(() => {
    'use strict';

    const toggle = document.getElementById('idlerpg-auto-refresh-toggle');
    const status = document.getElementById('idlerpg-auto-refresh-status');
    if (!toggle || !status) {
        return;
    }

    const fallbackProbeIntervalMs = 60_000;
    const refreshOffsetMs = 5_000;
    const staleProbeRetryMs = 5_000;
    const staleProbeRetryLimit = 3;
    const errorRetryMs = 15_000;
    const retryWhileBusyMs = 5_000;
    const preferenceKey = 'envs-idlerpg-auto-refresh-v1';

    const stableUrl = new URL(window.location.href);
    stableUrl.searchParams.delete('_idlerpg_refresh');
    stableUrl.searchParams.delete('_idlerpg_generation_probe');
    stableUrl.searchParams.delete('_idlerpg_probe');
    const pageKey = `${stableUrl.pathname}${stableUrl.search}`;
    const detailsKey = `envs-idlerpg-open-details:${pageKey}`;

    if (stableUrl.href !== window.location.href && window.history?.replaceState) {
        window.history.replaceState(null, '', stableUrl.toString());
    }

    const parseNonNegativeInteger = (value) => {
        const parsed = Number.parseInt(value || '', 10);
        return Number.isFinite(parsed) && parsed >= 0 ? parsed : 0;
    };

    const exportedAtSeconds = parseNonNegativeInteger(toggle.dataset.exportedAt);
    const exportIntervalSeconds = parseNonNegativeInteger(toggle.dataset.exportInterval);
    const serverNowSeconds = parseNonNegativeInteger(toggle.dataset.serverNow);
    const initialGenerationId = (toggle.dataset.generationId || '').trim();
    const exportIntervalMs = exportIntervalSeconds * 1000;
    const hasExportSchedule = exportedAtSeconds > 0 && exportIntervalMs > 0;
    const clientStartedAtMs = Date.now();

    let enabled = false;
    let timerId = null;
    let deadline = 0;
    let pendingReload = false;
    let probeInFlight = false;
    let staleProbeRetries = 0;
    let countdownLabel = '';

    const readPreference = () => {
        try {
            return window.localStorage.getItem(preferenceKey) === 'on';
        } catch (_) {
            return false;
        }
    };

    const writePreference = (value) => {
        try {
            window.localStorage.setItem(preferenceKey, value ? 'on' : 'off');
        } catch (_) {
            // The switch still works for this page when storage is unavailable.
        }
    };

    const detailsId = (details, index) => {
        const summary = details.querySelector(':scope > summary');
        return details.id
            || details.dataset.autoRefreshKey
            || `${index}:${summary ? summary.textContent.trim() : ''}`;
    };

    const preserveOpenDetails = () => {
        try {
            const open = Array.from(document.querySelectorAll('details'))
                .map((details, index) => ({
                    id: detailsId(details, index),
                    open: details.open,
                }));
            window.sessionStorage.setItem(detailsKey, JSON.stringify(open));
        } catch (_) {
            // A refresh remains safe when session storage is unavailable.
        }
    };

    const restoreOpenDetails = () => {
        try {
            const raw = window.sessionStorage.getItem(detailsKey);
            if (!raw) {
                return;
            }
            window.sessionStorage.removeItem(detailsKey);
            const saved = new Map(JSON.parse(raw).map((entry) => [
                entry.id,
                Boolean(entry.open),
            ]));
            document.querySelectorAll('details').forEach((details, index) => {
                const id = detailsId(details, index);
                if (saved.has(id)) {
                    details.open = saved.get(id);
                }
            });
        } catch (_) {
            // Ignore malformed or unavailable session state.
        }
    };

    const userIsEditing = () => {
        const active = document.activeElement;
        if (!active || active === document.body) {
            return false;
        }
        return active.matches(
            'input, select, textarea, button, [contenteditable="true"]',
        );
    };

    const estimatedServerNowMs = () => {
        if (serverNowSeconds <= 0) {
            return Date.now();
        }
        return (serverNowSeconds * 1000) + (Date.now() - clientStartedAtMs);
    };

    const nextScheduledDelayMs = () => {
        if (!hasExportSchedule) {
            return fallbackProbeIntervalMs;
        }

        const firstProbeAtMs = (exportedAtSeconds * 1000)
            + exportIntervalMs
            + refreshOffsetMs;
        const serverNowMs = estimatedServerNowMs();
        if (firstProbeAtMs > serverNowMs) {
            return firstProbeAtMs - serverNowMs;
        }

        // generated_at changes only when public data changes. If one or more
        // export cycles were semantically unchanged, advance to the next
        // interval instead of entering a rapid stale-export retry loop.
        const elapsedMs = serverNowMs - firstProbeAtMs;
        const completedIntervals = Math.floor(elapsedMs / exportIntervalMs) + 1;
        return (firstProbeAtMs + (completedIntervals * exportIntervalMs))
            - serverNowMs;
    };

    const formatCountdown = (milliseconds) => {
        const totalSeconds = Math.max(0, Math.ceil(milliseconds / 1000));
        if (totalSeconds < 60) {
            return `${totalSeconds}s`;
        }
        const minutes = Math.floor(totalSeconds / 60);
        const seconds = totalSeconds % 60;
        return seconds === 0 ? `${minutes}m` : `${minutes}m ${seconds}s`;
    };

    const setStatus = (text) => {
        status.textContent = text;
    };

    const stopTicker = () => {
        if (timerId !== null) {
            window.clearTimeout(timerId);
            timerId = null;
        }
    };

    const scheduleTick = (delay) => {
        stopTicker();
        timerId = window.setTimeout(() => {
            timerId = null;
            tick();
        }, Math.max(0, delay));
    };

    const scheduleDeadline = (delay, label = '') => {
        deadline = Date.now() + Math.max(0, delay);
        countdownLabel = label;
        const countdown = formatCountdown(delay);
        setStatus(label ? `${label} ${countdown}` : countdown);
        scheduleTick(Math.min(1000, Math.max(0, delay)));
    };

    const scheduleNextExportWindow = () => {
        staleProbeRetries = 0;
        scheduleDeadline(nextScheduledDelayMs());
    };

    const refreshPage = () => {
        preserveOpenDetails();
        setStatus('refreshing…');

        const refreshUrl = new URL(stableUrl.toString());
        refreshUrl.searchParams.set('_idlerpg_refresh', String(Date.now()));
        window.location.replace(refreshUrl.toString());
    };

    const probeUrl = () => {
        const url = new URL(stableUrl.toString());
        url.searchParams.set('_idlerpg_generation_probe', '1');
        url.searchParams.set('_idlerpg_probe', String(Date.now()));
        return url;
    };

    const exportChanged = (payload) => {
        const generationId = typeof payload?.generation_id === 'string'
            ? payload.generation_id.trim()
            : '';
        if (initialGenerationId !== '' && generationId !== '') {
            return generationId !== initialGenerationId;
        }

        const updatedAt = parseNonNegativeInteger(payload?.updated_at);
        return exportedAtSeconds > 0 && updatedAt > exportedAtSeconds;
    };

    const scheduleAfterUnchangedProbe = () => {
        if (!hasExportSchedule) {
            scheduleDeadline(fallbackProbeIntervalMs);
            return;
        }

        staleProbeRetries += 1;
        if (staleProbeRetries <= staleProbeRetryLimit) {
            scheduleDeadline(staleProbeRetryMs, 'retry');
            return;
        }

        // No public data changed around the expected export window. This is
        // normal with delta exports, so resume the regular schedule rather
        // than polling every few seconds indefinitely.
        scheduleNextExportWindow();
    };

    const runProbe = async () => {
        if (probeInFlight || !enabled || document.hidden) {
            return;
        }
        probeInFlight = true;
        setStatus('checking…');
        try {
            const response = await window.fetch(probeUrl(), {
                cache: 'no-store',
                credentials: 'same-origin',
                headers: {'Accept': 'application/json'},
            });
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }
            const payload = await response.json();
            if (exportChanged(payload)) {
                if (userIsEditing()) {
                    pendingReload = true;
                    setStatus('update ready');
                    scheduleTick(retryWhileBusyMs);
                    return;
                }
                refreshPage();
                return;
            }
            pendingReload = false;
            scheduleAfterUnchangedProbe();
        } catch (_) {
            scheduleDeadline(errorRetryMs, 'retry');
        } finally {
            probeInFlight = false;
        }
    };

    const tick = () => {
        if (!enabled) {
            return;
        }
        if (document.hidden) {
            stopTicker();
            setStatus('paused');
            return;
        }

        if (pendingReload) {
            if (userIsEditing()) {
                setStatus('update ready');
                scheduleTick(retryWhileBusyMs);
                return;
            }
            refreshPage();
            return;
        }

        const remainingMs = deadline - Date.now();
        if (remainingMs > 0) {
            const countdown = formatCountdown(remainingMs);
            setStatus(
                countdownLabel ? `${countdownLabel} ${countdown}` : countdown,
            );
            scheduleTick(Math.min(1000, remainingMs));
            return;
        }

        void runProbe();
    };

    const startTicker = () => {
        stopTicker();
        pendingReload = false;
        scheduleNextExportWindow();
    };

    const applyEnabled = (value, persist = true) => {
        enabled = Boolean(value);
        toggle.checked = enabled;
        if (persist) {
            writePreference(enabled);
        }

        if (enabled) {
            startTicker();
        } else {
            stopTicker();
            pendingReload = false;
            staleProbeRetries = 0;
            setStatus('off');
        }
    };

    restoreOpenDetails();
    applyEnabled(readPreference(), false);

    toggle.addEventListener('change', () => {
        applyEnabled(toggle.checked);
    });

    document.addEventListener('visibilitychange', () => {
        if (!enabled) {
            return;
        }
        if (document.hidden) {
            stopTicker();
            setStatus('paused');
        } else {
            startTicker();
        }
    });
})();
