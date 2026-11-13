const roadroom = document.querySelector('[data-roadroom]');

if (roadroom) {
    const stage = roadroom.querySelector('.roadroom-stage');
    const details = roadroom.querySelector('.roadroom-details');
    const steps = roadroom.querySelectorAll('[data-roadroom-step]');
    const motion = matchMedia('(prefers-reduced-motion: no-preference) and (min-height: 520px)');
    let frame;
    let visible = false;

    function segment(progress, start, end) {
        const t = Math.min(1, Math.max(0, (progress - start) / (end - start)));
        return t * t * (3 - 2 * t);
    }

    function draw() {
        frame = undefined;
        const distance = parseFloat(getComputedStyle(details).marginTop);
        const offset = parseFloat(getComputedStyle(stage).top) || 0;
        const progress = Math.min(1, Math.max(0, (offset - roadroom.getBoundingClientRect().top) / distance));
        const firstApproach = segment(progress, .04, .14) - segment(progress, .57, .64);
        const firstVisit = segment(progress, .23, .35) - segment(progress, .40, .50);
        const secondApproach = segment(progress, .69, .77);
        const secondVisit = segment(progress, .85, .96);
        const zoom = 1 + .58 * (firstApproach + secondApproach) + 3.15 * (firstVisit + secondVisit);
        const x = .214 * firstApproach - .222 * secondApproach;
        const y = -.041 * (firstVisit + secondVisit);
        const elevator = segment(progress, .14, .23) - segment(progress, .50, .57);
        const dj = segment(progress, .77, .85);

        roadroom.style.setProperty('--roadroom-progress', progress.toFixed(4));
        stage.style.setProperty('--roadroom-camera-x', `${(-100 * x).toFixed(3)}%`);
        stage.style.setProperty('--roadroom-camera-y', `${(-100 * y).toFixed(3)}%`);
        stage.style.setProperty('--roadroom-zoom', zoom.toFixed(4));
        stage.style.setProperty('--roadroom-elevator', elevator.toFixed(4));
        stage.style.setProperty('--roadroom-dj', dj.toFixed(4));
        if (dj > 0) {
            const beat = (Math.min(progress, .96) - .77) * 93;
            stage.style.setProperty('--roadroom-level-a', `${Math.round(7 + 4 * Math.sin(beat)) / 12 * 100}%`);
            stage.style.setProperty('--roadroom-level-b', `${Math.round(6 + 4 * Math.sin(beat + 1.6)) / 12 * 100}%`);
        }
        let chapter = 0;
        if (progress >= .14 && progress < .57) chapter = 1;
        if (progress >= .57 && progress < .77) chapter = 2;
        if (progress >= .77) chapter = 3;
        steps.forEach((step, index) => step.toggleAttribute('data-current', index === chapter));
    }

    function schedule() {
        if (visible && motion.matches && frame === undefined) frame = requestAnimationFrame(draw);
    }

    function syncMotion() {
        roadroom.toggleAttribute('data-motion', motion.matches);
        if (frame !== undefined) cancelAnimationFrame(frame);
        frame = undefined;
        schedule();
    }

    new IntersectionObserver(([entry]) => {
        visible = entry.isIntersecting;
        schedule();
    }).observe(roadroom);
    window.addEventListener('scroll', schedule, { passive: true });
    new ResizeObserver(schedule).observe(stage);
    motion.addEventListener('change', syncMotion);
    syncMotion();
}
