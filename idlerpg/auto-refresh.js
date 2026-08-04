(() => {
    'use strict';

    const toggle = document.getElementById('idlerpg-auto-refresh-toggle');
    const status = document.getElementById('idlerpg-auto-refresh-status');
    if (!toggle || !status) {
        return;
    }

    const fallbackIntervalMs = 60_000;
    const refreshOffsetMs = 3_000;
    const retryWhileBusyMs = 5_000;
    const retryWhileExportStaleMs = 5_000;
    const preferenceKey = 'envs-idlerpg-auto-refresh-v1';
    const pageKey = `${window.location.pathname}${window.location.search}`;
    const detailsKey = `envs-idlerpg-open-details:${pageKey}`;
    const exportProbeKey = `envs-idlerpg-export-probe:${pageKey}`;

    const exportedAtSeconds = Number.parseInt(toggle.dataset.exportedAt || '', 10);
    const exportIntervalSeconds = Number.parseInt(toggle.dataset.exportInterval || '', 10);
    const exportedAtMs = Number.isFinite(exportedAtSeconds) && exportedAtSeconds > 0
        ? exportedAtSeconds * 1000
        : 0;
    const exportIntervalMs = Number.isFinite(exportIntervalSeconds) && exportIntervalSeconds > 0
        ? exportIntervalSeconds * 1000
        : 0;
    const hasExportSchedule = exportedAtMs > 0 && exportIntervalMs > 0;

    let enabled = false;
    let deadline = 0;
    let timerId = null;
    let waitingForNewExport = false;

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

    const readExportProbe = () => {
        try {
            const value = Number.parseInt(window.sessionStorage.getItem(exportProbeKey) || '', 10);
            return Number.isFinite(value) && value > 0 ? value : 0;
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
            // Without session storage, the regular export-aligned refresh still works.
        }
    };

    const clearExportProbe = () => {
        try {
            window.sessionStorage.removeItem(exportProbeKey);
        } catch (_) {
            // Ignore unavailable session storage.
        }
    };

    const detectStaleExport = () => {
        const previousExportSeconds = readExportProbe();
        if (previousExportSeconds <= 0) {
            return false;
        }
        if (exportedAtSeconds > previousExportSeconds) {
            clearExportProbe();
            return false;
        }
        return true;
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

    const nextExportAlignedDeadline = (now = Date.now()) => {
        if (!hasExportSchedule) {
            return now + fallbackIntervalMs;
        }

        const firstDeadline = exportedAtMs + exportIntervalMs + refreshOffsetMs;
        if (firstDeadline > now) {
            return firstDeadline;
        }

        const elapsed = now - firstDeadline;
        const completedIntervals = Math.floor(elapsed / exportIntervalMs) + 1;
        return firstDeadline + (completedIntervals * exportIntervalMs);
    };

    const resetDeadline = (delay = null) => {
        deadline = delay === null
            ? nextExportAlignedDeadline()
            : Date.now() + delay;
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
            setStatus('paused');
            return;
        }

        const remainingMs = deadline - Date.now();
        if (remainingMs > 0) {
            const seconds = Math.ceil(remainingMs / 1000);
            setStatus(waitingForNewExport ? `retry ${seconds}s` : `${seconds}s`);
            scheduleTick(Math.min(1000, remainingMs));
            return;
        }

        if (userIsEditing()) {
            resetDeadline(retryWhileBusyMs);
            setStatus('paused');
            scheduleTick(retryWhileBusyMs);
            return;
        }

        refreshPage();
    };

    const startTicker = () => {
        stopTicker();
        waitingForNewExport = detectStaleExport();
        resetDeadline(waitingForNewExport ? retryWhileExportStaleMs : null);
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
            waitingForNewExport = false;
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
            setStatus('paused');
        } else {
            tick();
        }
    });
})();
