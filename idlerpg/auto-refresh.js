(() => {
    'use strict';

    const toggle = document.getElementById('idlerpg-auto-refresh-toggle');
    const status = document.getElementById('idlerpg-auto-refresh-status');
    if (!toggle || !status) {
        return;
    }

    const fallbackProbeIntervalMs = 60_000;
    const minProbeIntervalMs = 10_000;
    const maxProbeIntervalMs = 60_000;
    const retryAfterErrorMs = 10_000;
    const retryWhileBusyMs = 5_000;
    const preferenceKey = 'envs-idlerpg-auto-refresh-v1';

    const stableUrl = new URL(window.location.href);
    stableUrl.searchParams.delete('_idlerpg_refresh');
    stableUrl.searchParams.delete('_idlerpg_generation_probe');
    const pageKey = `${stableUrl.pathname}${stableUrl.search}`;
    const detailsKey = `envs-idlerpg-open-details:${pageKey}`;

    if (stableUrl.href !== window.location.href && window.history?.replaceState) {
        window.history.replaceState(null, '', stableUrl.toString());
    }

    const parseNonNegativeInteger = (value) => {
        const parsed = Number.parseInt(value || '', 10);
        return Number.isFinite(parsed) && parsed >= 0 ? parsed : 0;
    };

    const configuredExportIntervalSeconds = parseNonNegativeInteger(
        toggle.dataset.exportInterval,
    );
    const configuredExportIntervalMs = configuredExportIntervalSeconds * 1000;
    const probeIntervalMs = configuredExportIntervalMs > 0
        ? Math.max(
            minProbeIntervalMs,
            Math.min(configuredExportIntervalMs, maxProbeIntervalMs),
        )
        : fallbackProbeIntervalMs;

    const initialGenerationId = (toggle.dataset.generationId || '').trim();
    const initialExportedAt = parseNonNegativeInteger(toggle.dataset.exportedAt);

    let enabled = false;
    let timerId = null;
    let probeDeadline = 0;
    let pendingReload = false;
    let probeInFlight = false;

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

    const scheduleProbe = (delay = probeIntervalMs) => {
        probeDeadline = Date.now() + delay;
        scheduleTick(Math.min(1000, delay));
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
        return initialExportedAt > 0 && updatedAt > initialExportedAt;
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
            scheduleProbe();
        } catch (_) {
            setStatus('retrying…');
            scheduleProbe(retryAfterErrorMs);
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

        const remainingMs = probeDeadline - Date.now();
        if (remainingMs > 0) {
            setStatus(`check ${Math.ceil(remainingMs / 1000)}s`);
            scheduleTick(Math.min(1000, remainingMs));
            return;
        }

        void runProbe();
    };

    const startTicker = () => {
        stopTicker();
        pendingReload = false;
        scheduleProbe();
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
