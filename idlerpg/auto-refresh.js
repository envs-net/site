(() => {
    'use strict';

    const toggle = document.getElementById('idlerpg-auto-refresh-toggle');
    const status = document.getElementById('idlerpg-auto-refresh-status');
    if (!toggle || !status) {
        return;
    }

    const fallbackIntervalMs = 60_000;
    const refreshOffsetMs = 5_000;
    const staleExportRetryMs = 5_000;
    const retryWhileBusyMs = 5_000;
    const preferenceKey = 'envs-idlerpg-auto-refresh-v1';
    const pageKey = `${window.location.pathname}${window.location.search}`;
    const detailsKey = `envs-idlerpg-open-details:${pageKey}`;
    const exportProbeKey = `envs-idlerpg-export-probe:${pageKey}`;

    const parsePositiveInteger = (value) => {
        const parsed = Number.parseInt(value || '', 10);
        return Number.isFinite(parsed) && parsed > 0 ? parsed : 0;
    };

    const exportedAtSeconds = parsePositiveInteger(toggle.dataset.exportedAt);
    const exportIntervalSeconds = parsePositiveInteger(toggle.dataset.exportInterval);
    const serverNowSeconds = parsePositiveInteger(toggle.dataset.serverNow);
    const clientStartedAtMs = Date.now();
    const exportIntervalMs = exportIntervalSeconds * 1000;
    const hasExportSchedule = exportedAtSeconds > 0 && exportIntervalMs > 0;

    let enabled = false;
    let deadline = 0;
    let timerId = null;
    let retryingStaleExport = false;

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

    const readExportProbe = () => {
        try {
            return parsePositiveInteger(window.sessionStorage.getItem(exportProbeKey));
        } catch (_) {
            return 0;
        }
    };

    const writeExportProbe = () => {
        if (exportedAtSeconds <= 0) {
            return;
        }
        try {
            window.sessionStorage.setItem(exportProbeKey, String(exportedAtSeconds));
        } catch (_) {
            // The regular timed reload still works without session storage.
        }
    };

    const clearExportProbe = () => {
        try {
            window.sessionStorage.removeItem(exportProbeKey);
        } catch (_) {
            // Ignore unavailable session storage.
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

    const nextRefreshDelayMs = () => {
        if (!hasExportSchedule) {
            return fallbackIntervalMs;
        }

        const nextRefreshAtMs = (exportedAtSeconds * 1000)
            + exportIntervalMs
            + refreshOffsetMs;
        return Math.max(0, nextRefreshAtMs - estimatedServerNowMs());
    };

    const detectStaleExportRetry = () => {
        const probedExportSeconds = readExportProbe();
        if (probedExportSeconds <= 0) {
            return false;
        }
        if (exportedAtSeconds > probedExportSeconds) {
            clearExportProbe();
            return false;
        }
        return true;
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

    const refreshPage = () => {
        preserveOpenDetails();
        writeExportProbe();
        setStatus('refreshing…');
        window.location.reload();
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

        const remainingMs = deadline - Date.now();
        if (remainingMs > 0) {
            const seconds = Math.ceil(remainingMs / 1000);
            setStatus(
                retryingStaleExport
                    ? `retry ${seconds}s`
                    : `refresh ${seconds}s`,
            );
            scheduleTick(Math.min(1000, remainingMs));
            return;
        }

        if (userIsEditing()) {
            deadline = Date.now() + retryWhileBusyMs;
            setStatus('paused');
            scheduleTick(retryWhileBusyMs);
            return;
        }

        refreshPage();
    };

    const startTicker = () => {
        stopTicker();
        retryingStaleExport = detectStaleExportRetry();
        const delay = retryingStaleExport
            ? staleExportRetryMs
            : nextRefreshDelayMs();
        deadline = Date.now() + delay;
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
            retryingStaleExport = false;
            clearExportProbe();
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
