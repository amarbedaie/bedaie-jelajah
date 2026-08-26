import './bootstrap';

/**
 * Utiliti kecil yang dikongsi seluruh sistem.
 * Alpine dibekalkan oleh Livewire, jadi kita hanya mendaftar
 * data-component sebelum Alpine dimulakan.
 */
document.addEventListener('alpine:init', () => {
    /** Kiraan detik ke tarikh program. */
    window.Alpine.data('countdown', (target) => ({
        hari: 0, jam: 0, minit: 0, saat: 0, tamat: false,
        init() {
            this.tick();
            this.timer = setInterval(() => this.tick(), 1000);
        },
        destroy() { clearInterval(this.timer); },
        tick() {
            const diff = new Date(target).getTime() - Date.now();
            if (diff <= 0) { this.tamat = true; clearInterval(this.timer); return; }
            this.hari  = Math.floor(diff / 86400000);
            this.jam   = Math.floor((diff % 86400000) / 3600000);
            this.minit = Math.floor((diff % 3600000) / 60000);
            this.saat  = Math.floor((diff % 60000) / 1000);
        },
    }));

    /** Kaunter yang menaik apabila masuk pandangan. */
    window.Alpine.data('counter', (value, duration = 1400) => ({
        display: 0,
        init() {
            const observer = new IntersectionObserver(([entry]) => {
                if (!entry.isIntersecting) return;
                observer.disconnect();
                if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                    this.display = value;
                    return;
                }
                const start = performance.now();
                const step = (now) => {
                    const p = Math.min(1, (now - start) / duration);
                    this.display = Math.round(value * (1 - Math.pow(1 - p, 3)));
                    if (p < 1) requestAnimationFrame(step);
                };
                requestAnimationFrame(step);
            }, { threshold: 0.35 });
            observer.observe(this.$el);
        },
    }));

    /**
     * Pengimbas QR kehadiran. Perpustakaan kamera dimuat secara malas
     * supaya ia tidak membebankan halaman lain.
     */
    window.Alpine.data('qrScanner', (wire) => ({
        kamera: false,
        ralat: null,
        scanner: null,
        async mula() {
            if (this.kamera) return;
            this.ralat = null;
            try {
                const { Html5Qrcode } = await import('html5-qrcode');
                this.scanner = new Html5Qrcode('qr-reader', { verbose: false });
                await this.scanner.start(
                    { facingMode: 'environment' },
                    { fps: 10, qrbox: { width: 240, height: 240 } },
                    (teks) => wire.scan(teks),
                    () => {},
                );
                this.kamera = true;
            } catch (e) {
                this.ralat = 'Kamera tidak dapat dibuka. Gunakan kod manual atau carian nama di bawah.';
            }
        },
        async henti() {
            if (!this.scanner) return;
            try { await this.scanner.stop(); } catch (e) { /* sudah berhenti */ }
            this.kamera = false;
        },
        maklum(outcome) {
            if (!navigator.vibrate) return;
            if (outcome === 'checked_in') navigator.vibrate(80);
            if (outcome === 'fail') navigator.vibrate([60, 60, 60]);
        },
        destroy() { this.henti(); },
    }));
});

// ── PWA ───────────────────────────────────────────────────────────
// Rangka aplikasi di-cache untuk isyarat lemah di masjid; data langsung
// tidak pernah di-cache. Lihat public/sw.js.
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js').catch(() => {
            // Senyap: PWA ialah peningkatan, bukan keperluan.
        });
    });
}
