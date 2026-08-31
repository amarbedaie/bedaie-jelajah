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

    /**
     * Papan kanban yang ditatal mendatar (cth. papan sasaran jelajah).
     * Skrol bar disembunyikan untuk kekemasan, jadi pengguna tetikus biasa
     * (tanpa touchpad) tiada cara nampak untuk tatal ke kanan — komponen ini
     * menambah butang anak panah dan menukar skrol menegak tetikus kepada
     * tatalan mendatar apabila menuding di atas papan.
     */
    window.Alpine.data('kanbanScroll', () => ({
        atStart: true,
        atEnd: false,
        init() {
            const el = this.$refs.scroller;
            this.update();
            el.addEventListener('scroll', () => this.update(), { passive: true });
            el.addEventListener('wheel', (e) => {
                if (Math.abs(e.deltaY) > Math.abs(e.deltaX) && el.scrollWidth > el.clientWidth) {
                    el.scrollLeft += e.deltaY;
                    e.preventDefault();
                }
            }, { passive: false });
            new ResizeObserver(() => this.update()).observe(el);
        },
        update() {
            const el = this.$refs.scroller;
            this.atStart = el.scrollLeft <= 4;
            this.atEnd = el.scrollLeft >= el.scrollWidth - el.clientWidth - 4;
        },
        scroll(dir) {
            const el = this.$refs.scroller;
            el.scrollBy({ left: dir * el.clientWidth * 0.8, behavior: 'smooth' });
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

/*
 * Maklum balas tindakan.
 *
 * session()->flash() tidak pernah dipaparkan dari dalam komponen
 * Livewire: partial flash berada di luar akar komponen, jadi permintaan
 * XHR memakan mesej itu sebelum ia sempat dirender. Setiap "berjaya
 * disimpan" dalam ruang admin hilang begitu sahaja — punca utama staf
 * merasakan sistem ini tidak memberi respons.
 *
 * Komponen kini menghantar peristiwa 'notify'; pendengar ini membina
 * nodnya sendiri, jadi ia tidak bergantung pada kitaran reaktif mana-mana
 * rangka kerja.
 */
const TOAST_TONES = {
    success: ['bg-clay-50', 'border-clay-300', 'text-ink', 'bg-clay-600'],
    warning: ['bg-mist', 'border-control-line/35', 'text-ink', 'bg-ink-muted'],
    info:    ['bg-mist', 'border-control-line/35', 'text-ink', 'bg-ink-muted'],
    error:   ['bg-alert-soft', 'border-alert-line', 'text-alert', 'bg-alert'],
};

function tunjukToast(detail) {
    const host = document.getElementById('jelajah-toast');
    if (!host || !detail?.message) return;

    const [bg, border, text, dot] = TOAST_TONES[detail.variant] ?? TOAST_TONES.success;

    const el = document.createElement('div');
    el.className = `pointer-events-auto flex w-full max-w-md items-start gap-3 rounded-card border
                    px-4 py-3 text-sm shadow-lift transition-opacity duration-200 sm:w-auto
                    sm:min-w-[20rem] ${bg} ${border} ${text}`;
    el.style.opacity = '0';

    const mark = document.createElement('span');
    mark.className = `mt-1 h-2 w-2 shrink-0 rounded-full ${dot}`;

    const body = document.createElement('span');
    body.className = 'flex-1 text-pretty';
    body.textContent = detail.message;

    const close = document.createElement('button');
    close.type = 'button';
    close.className = 'shrink-0 opacity-50 transition hover:opacity-100';
    close.setAttribute('aria-label', 'Tutup mesej');
    close.innerHTML = '<svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" '
        + 'stroke-width="1.8"><path d="M6 6l12 12M18 6L6 18" stroke-linecap="round"/></svg>';

    const buang = () => {
        el.style.opacity = '0';
        setTimeout(() => el.remove(), 200);
    };

    close.addEventListener('click', buang);
    el.append(mark, body, close);
    host.append(el);

    requestAnimationFrame(() => { el.style.opacity = '1'; });
    setTimeout(buang, 6000);
}

window.addEventListener('notify', (e) => tunjukToast(e.detail));
