(() => {
    'use strict';

    const toggle = document.getElementById('idlerpg-auto-refresh-toggle');
    const status = document.getElementById('idlerpg-auto-refresh-status');
    if (!toggle || !status) {
        return;
    }

    const intervalMs = 60_000;
    const retryWhileBusyMs = 5_000;
    const preferenceKey = 'envs-idlerpg-auto-refresh-v1';
    const detailsKey = `envs-idlerpg-open-details:${window.location.pathname}${window.location.search}`;

    let enabled = false;
    let deadline = 0;
    let timerId = null;

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

    const resetDeadline = (delay = intervalMs) => {
        deadline = Date.now() + delay;
    };

    const stopTicker = () => {
        if (timerId !== null) {
            window.clearInterval(timerId);
            timerId = null;
        }
    };

    const refreshPage = () => {
        preserveOpenDetails();
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
            setStatus(`${Math.ceil(remainingMs / 1000)}s`);
            return;
        }

        if (userIsEditing()) {
            resetDeadline(retryWhileBusyMs);
            setStatus('paused');
            return;
        }

        refreshPage();
    };

    const startTicker = () => {
        stopTicker();
        resetDeadline();
        tick();
        timerId = window.setInterval(tick, 1000);
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
