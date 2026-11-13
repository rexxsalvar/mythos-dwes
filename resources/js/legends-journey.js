const legends = document.querySelector('[data-legends]');

if (legends) {
    const stage = legends.querySelector('.legends-stage');
    const details = legends.querySelector('.legends-details');
    const elastic = legends.querySelector('#legends-elastic');
    const motion = matchMedia('(prefers-reduced-motion: no-preference) and (min-height: 461px)');
    const elasticPoses = [
        [0,    [83, -1, 83, 39, 83, 111, 83, 151]],
        [.35,  [83, -1, 108, 31, 119, 98, 83, 151]],
        [.57,  [84, -1, 116, 31, 124, 115, 102, 147]],
        [.73,  [100, 2, 118, 25, 120, 109, 104, 146]],
        [.86,  [103, 7, 108, 40, 101, 104, 103, 143]],
        [1,    [103, 12, 101, 48, 105, 104, 103, 138]],
    ];
    let lastRelease;
    let frame;

    function drawElastic(release) {
        if (release === lastRelease) return;
        lastRelease = release;
        const next = elasticPoses.findIndex(([time], index) => index > 0 && release <= time);
        const [start, from] = elasticPoses[next - 1];
        const [end, to] = elasticPoses[next];
        const blend = (release - start) / (end - start);
        const points = from.map((value, index) => (value + (to[index] - value) * blend).toFixed(2));
        elastic.setAttribute('d', `M${points.slice(0, 2).join(' ')} C${points.slice(2).join(' ')}`);
    }

    function draw() {
        frame = undefined;
        const distance = parseFloat(getComputedStyle(details).marginTop);
        const offset = parseFloat(getComputedStyle(stage).top) || 0;
        const progress = Math.min(1, Math.max(0, (offset - legends.getBoundingClientRect().top) / distance));
        legends.style.setProperty('--legends-progress', progress.toFixed(4));
        drawElastic(Math.min(1, progress / .19));
    }

    function schedule() {
        if (motion.matches && frame === undefined) frame = requestAnimationFrame(draw);
    }

    function syncMotion() {
        legends.toggleAttribute('data-motion', motion.matches);
        if (frame !== undefined) cancelAnimationFrame(frame);
        frame = undefined;
        if (motion.matches) {
            draw();
        } else {
            legends.style.removeProperty('--legends-progress');
            drawElastic(0);
        }
    }

    const visibility = new IntersectionObserver(([entry]) => {
        window[entry.isIntersecting ? 'addEventListener' : 'removeEventListener']('scroll', schedule);
        if (entry.isIntersecting) schedule();
    });
    visibility.observe(legends);
    new ResizeObserver(schedule).observe(stage);
    motion.addEventListener('change', syncMotion);
    syncMotion();
}
