import {
    AUTO_REFRESH_STORAGE_KEY,
    AUTO_REFRESH_INTERVALS_SECONDS,
} from './constants';

export default class AutoRefresh {
    constructor(view) {
        this.view = view;
    }

    initFromDom() {
        const $select = this.view.$main.find('.report-board--auto-refresh-interval').first();
        if (!$select.length) {
            return;
        }

        const storedValue = window.sessionStorage.getItem(AUTO_REFRESH_STORAGE_KEY);
        const storedSeconds = storedValue === null ? 0 : Number(storedValue);
        const seconds = AUTO_REFRESH_INTERVALS_SECONDS.includes(storedSeconds)
            ? storedSeconds
            : 0;

        this.setInterval(seconds, false);
    }

    syncSelect() {
        const $select = this.view.$main.find('.report-board--auto-refresh-interval').first();
        if (!$select.length) {
            return;
        }

        const seconds = this.view.autoRefreshIntervalMs / 1000;
        $select.val(String(seconds));
    }

    setInterval(seconds, persist = true) {
        const normalized = AUTO_REFRESH_INTERVALS_SECONDS.includes(seconds) ? seconds : 0;

        this.clearTimer();
        this.view.autoRefreshIntervalMs = normalized * 1000;
        this.syncSelect();

        if (persist) {
            window.sessionStorage.setItem(
                AUTO_REFRESH_STORAGE_KEY,
                String(normalized)
            );
        }

        if (this.view.autoRefreshIntervalMs <= 0) {
            return;
        }

        this.view.autoRefreshTimer = window.setInterval(() => {
            if (document.hidden) {
                return;
            }

            const $button = this.view.$main.find('.report-board--refresh');
            if ($button.prop('disabled')) {
                return;
            }

            this.view.reportRefresh.refresh({ silent: true });
        }, this.view.autoRefreshIntervalMs);
    }

    clearTimer() {
        if (this.view.autoRefreshTimer) {
            window.clearInterval(this.view.autoRefreshTimer);
            this.view.autoRefreshTimer = null;
        }
    }
}
