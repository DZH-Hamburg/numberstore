import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.data('screenshotCapture', (config) => ({
    modalOpen: false,
    busy: false,
    status: '',
    previousAt: config.previousAt ?? null,
    postUrl: config.postUrl,
    metaUrl: config.metaUrl,
    showUrl: config.showUrl,
    imgSrc: config.imgSrc ?? '',
    strings: config.strings,
    downloadUrl() {
        return `${this.showUrl}?download=1`;
    },
    async capture() {
        this.busy = true;
        this.status = this.strings.running;
        const before = this.previousAt;
        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        const res = await fetch(this.postUrl, {
            method: 'POST',
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                ...(token ? { 'X-CSRF-TOKEN': token } : {}),
            },
            credentials: 'same-origin',
        });
        if (! res.ok) {
            this.status = this.strings.error;
            this.busy = false;

            return;
        }
        const deadline = Date.now() + 120000;
        while (Date.now() < deadline) {
            await new Promise((r) => setTimeout(r, 1500));
            const metaRes = await fetch(this.metaUrl, { headers: { Accept: 'application/json' } });
            if (! metaRes.ok) {
                continue;
            }
            const m = await metaRes.json();
            const at = m.last_screenshot_at;
            if (at && at !== before) {
                this.previousAt = at;
                this.imgSrc = `${this.showUrl}?t=${encodeURIComponent(at)}`;
                this.status = this.strings.done;
                this.busy = false;

                return;
            }
        }
        this.status = this.strings.timeout;
        this.busy = false;
    },
}));

Alpine.start();
