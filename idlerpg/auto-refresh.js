(() => {
    'use strict';

    const toggle = document.getElementById('idlerpg-auto-refresh-toggle');
    const status = document.getElementById('idlerpg-auto-refresh-status');
    if (!toggle || !status) {
        return;
    }

    const refreshOffsetMs = 5_000;
    const retryWhileBusyMs = 5_000;
    const preferenceKey = 'envs-idlerpg-auto-refresh-v1';

    const stableUrl = new URL(window.location.href);
    stableUrl.searchParams.delete('_idlerpg_refresh');
    const pageKey = `${stableUrl.pathname}${stableUrl.search}`;
    const detailsKey = `envs-idlerpg-open-details:${pageKey}`;

    if (stableUrl.href !== window.location.href && window.history?.replaceState) {
        window.history.replaceState(null, '', stableUrl.toString());
    }

    const parsePositiveInteger = (value) => {
        const parsed = Number.parseInt(value || '', 10);
        return Number.isFinite(parsed) && parsed > 0 ? parsed : 0;
    };

    const configuredExportIntervalSeconds = parsePositiveInteger(
        toggle.dataset.exportInterval,
    );
    const exportIntervalMs = Math.max(
        10_000,
        (configuredExportIntervalSeconds || 60) * 1000,
    );

    let enabled = false;
    let reloadDeadline = 0;
    let retryDeadline = 0;
    let timerId = null;
    let waitingForEditor = false;

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

    const scheduleReload = () => {
        reloadDeadline = Date.now() + exportIntervalMs + refreshOffsetMs;
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
        setStatus('refreshing…');

        const refreshUrl = new URL(stableUrl.toString());
        refreshUrl.searchParams.set('_idlerpg_refresh', String(Date.now()));
        window.location.replace(refreshUrl.toString());
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

        const now = Date.now();

        if (waitingForEditor) {
            const retryRemainingMs = retryDeadline - now;
            if (retryRemainingMs > 0) {
                setStatus('paused');
                scheduleTick(Math.min(1000, retryRemainingMs));
                return;
            }
        } else if (now < reloadDeadline) {
            const reloadRemainingMs = reloadDeadline - now;
            setStatus(`reload ${Math.ceil(reloadRemainingMs / 1000)}s`);
            scheduleTick(Math.min(1000, reloadRemainingMs));
            return;
        }

        if (userIsEditing()) {
            waitingForEditor = true;
            retryDeadline = Date.now() + retryWhileBusyMs;
            setStatus('paused');
            scheduleTick(retryWhileBusyMs);
            return;
        }

        refreshPage();
    };

    const startTicker = () => {
        stopTicker();
        waitingForEditor = false;
        scheduleReload();
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
            waitingForEditor = false;
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
