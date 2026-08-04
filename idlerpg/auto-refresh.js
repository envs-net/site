(() => {
    'use strict';

    const toggle = document.getElementById('idlerpg-auto-refresh-toggle');
    const status = document.getElementById('idlerpg-auto-refresh-status');
    if (!toggle || !status) {
        return;
    }

    const fallbackIntervalMs = 60_000;
    const refreshDelayMs = 3_000;
    const exportProbeIntervalMs = 1_000;
    const probeErrorRetryMs = 5_000;
    const retryWhileBusyMs = 5_000;
    const preferenceKey = 'envs-idlerpg-auto-refresh-v1';
    const detailsKey = `envs-idlerpg-open-details:${window.location.pathname}${window.location.search}`;

    const exportedAtSeconds = Number.parseInt(toggle.dataset.exportedAt || '', 10);
    const exportIntervalSeconds = Number.parseInt(toggle.dataset.exportInterval || '', 10);
    const initialExportSeconds = Number.isFinite(exportedAtSeconds) && exportedAtSeconds > 0
        ? exportedAtSeconds
        : 0;
    const exportIntervalMs = Number.isFinite(exportIntervalSeconds) && exportIntervalSeconds > 0
        ? exportIntervalSeconds * 1000
        : 0;
    const hasExportSchedule = initialExportSeconds > 0 && exportIntervalMs > 0;

    let enabled = false;
    let baselineExportSeconds = initialExportSeconds;
    let deadline = 0;
    let phase = 'waiting';
    let timerId = null;
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
            // The switch still works for this page even when storage is blocked.
        }
    };

    const detailsId = (details, index) => {
        const summary = details.querySelector(':scope > summary');
        return details.id || details.dataset.autoRefreshKey || `${index}:${summary ? summary.textContent.trim() : ''}`;
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
            const saved = new Map(JSON.parse(raw).map((entry) => [entry.id, Boolean(entry.open)]));
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
        return active.matches('input, select, textarea, button, [contenteditable="true"]');
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

    const nextExpectedExportDeadline = (now = Date.now()) => {
        if (!hasExportSchedule) {
            return now + fallbackIntervalMs;
        }

        const expected = (baselineExportSeconds * 1000) + exportIntervalMs;
        return expected > now ? expected : now;
    };

    const probeUrl = () => {
        const url = new URL(window.location.href);
        url.searchParams.set('_idlerpg_export_probe', String(Date.now()));
        return url.toString();
    };

    const readExportTimestamp = (html) => {
        const parsed = new DOMParser().parseFromString(html, 'text/html');
        const probeToggle = parsed.getElementById('idlerpg-auto-refresh-toggle');
        if (!probeToggle) {
            return 0;
        }
        const value = Number.parseInt(probeToggle.dataset.exportedAt || '', 10);
        return Number.isFinite(value) && value > 0 ? value : 0;
    };

    const remainingRefreshDelay = (freshExportSeconds, response) => {
        let referenceNow = Date.now();
        const serverDate = response.headers ? response.headers.get('Date') : null;
        if (serverDate) {
            const parsedServerDate = Date.parse(serverDate);
            if (Number.isFinite(parsedServerDate)) {
                referenceNow = parsedServerDate;
            }
        }

        const exportAgeMs = Math.max(0, referenceNow - (freshExportSeconds * 1000));
        return Math.max(0, refreshDelayMs - exportAgeMs);
    };

    const refreshPage = () => {
        preserveOpenDetails();
        setStatus('refreshing…');
        window.location.reload();
    };

    const probeForNewExport = async () => {
        if (!enabled || probeInFlight || document.hidden) {
            return;
        }

        probeInFlight = true;
        setStatus('checking…');

        try {
            const response = await window.fetch(probeUrl(), {
                cache: 'no-store',
                credentials: 'same-origin',
                headers: {
                    'X-Requested-With': 'idlerpg-auto-refresh',
                },
            });
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }

            const freshExportSeconds = readExportTimestamp(await response.text());
            if (!enabled || document.hidden) {
                return;
            }
            if (freshExportSeconds > baselineExportSeconds) {
                baselineExportSeconds = freshExportSeconds;
                phase = 'refresh-delay';
                deadline = Date.now() + remainingRefreshDelay(freshExportSeconds, response);
                tick();
                return;
            }

            phase = 'probing';
            deadline = Date.now() + exportProbeIntervalMs;
            scheduleTick(exportProbeIntervalMs);
        } catch (_) {
            phase = 'probe-error';
            deadline = Date.now() + probeErrorRetryMs;
            setStatus(`retry ${Math.ceil(probeErrorRetryMs / 1000)}s`);
            scheduleTick(probeErrorRetryMs);
        } finally {
            probeInFlight = false;
        }
    };

    const tick = () => {
        if (!enabled) {
            return;
        }
        if (document.hidden) {
            setStatus('paused');
            return;
        }

        const remainingMs = deadline - Date.now();
        if (remainingMs > 0) {
            const seconds = Math.ceil(remainingMs / 1000);
            if (phase === 'waiting') {
                setStatus(`export ${seconds}s`);
            } else if (phase === 'refresh-delay') {
                setStatus(`refresh ${seconds}s`);
            } else if (phase === 'probe-error') {
                setStatus(`retry ${seconds}s`);
            } else {
                setStatus('checking…');
            }
            scheduleTick(Math.min(1000, remainingMs));
            return;
        }

        if (phase === 'refresh-delay') {
            if (userIsEditing()) {
                deadline = Date.now() + retryWhileBusyMs;
                setStatus('paused');
                scheduleTick(retryWhileBusyMs);
                return;
            }
            refreshPage();
            return;
        }

        probeForNewExport();
    };

    const startTicker = () => {
        stopTicker();
        phase = 'waiting';
        deadline = nextExpectedExportDeadline();
        tick();
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
            probeInFlight = false;
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
            phase = 'waiting';
            deadline = nextExpectedExportDeadline();
            tick();
        }
    });
})();
