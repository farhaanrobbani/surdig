

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.data('countUp', (target) => ({
    display: 0,
    init() {
        const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        if (reduce || !Number.isFinite(Number(target))) {
            this.display = Number(target) || 0;
            return;
        }
        const start = performance.now();
        const duration = 800;
        const step = (now) => {
            const progress = Math.min((now - start) / duration, 1);
            const eased = 1 - Math.pow(1 - progress, 3);
            this.display = Math.round(Number(target) * eased);
            if (progress < 1) requestAnimationFrame(step);
        };
        requestAnimationFrame(step);
    },
}));

Alpine.start();
