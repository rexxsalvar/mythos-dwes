const journey = document.querySelector('[data-zodiac-journey]');

if (journey) {
    const canvas = journey.querySelector('.zodiac-canvas');
    const stage = journey.querySelector('.zodiac-stage');
    const arrival = journey.querySelector('.zodiac-arrival');
    const context = canvas.getContext('2d', { alpha: false });
    const narrow = matchMedia('(max-width: 760px)');
    const motion = matchMedia('(prefers-reduced-motion: reduce)');
    const contrast = matchMedia('(forced-colors: active)');
    const frames = new Map();
    const encoded = new Map();
    const pending = new Set();
    const failures = new Map();
    const failed = new Set();
    const clamp = (value) => Math.min(1, Math.max(0, value));
    let manifest;
    let sequence;
    let controller;
    let generation = 0;
    let scheduled;
    let startupTimer;
    let nearby = false;
    let painted = -1;
    let target = 0;
    let direction = 1;
    let wanted = new Set();
    let encodedWanted = new Set();

    const capable = () => context && 'createImageBitmap' in window && 'IntersectionObserver' in window
        && !motion.matches && !contrast.matches && !navigator.connection?.saveData;
    const mainLast = () => sequence.count - 1;
    const finalFrame = () => mainLast() + sequence.door.count - 1;
    function release() {
        generation += 1;
        controller?.abort();
        clearTimeout(startupTimer);
        if (scheduled !== undefined) cancelAnimationFrame(scheduled);
        scheduled = undefined;
        sequence = undefined;
        frames.forEach((bitmap) => bitmap.close());
        frames.clear();
        encoded.clear();
        pending.clear();
        failures.clear();
        failed.clear();
        wanted.clear();
        encodedWanted.clear();
        painted = -1;
        journey.removeAttribute('data-zodiac-ready');
    }

    function fallback() {
        release();
        journey.dataset.zodiacMode = 'static';
        journey.style.removeProperty('--zodiac-progress');
    }

    function requestDraw() {
        if (sequence && nearby && scheduled === undefined) scheduled = requestAnimationFrame(draw);
    }

    function asset(index) {
        const part = index > mainLast() ? sequence.door : sequence;
        const number = index > mainLast() ? index - mainLast() : index;
        const digits = index > mainLast() ? 2 : 3;
        return journey.dataset.frames + '/' + part.prefix + String(number).padStart(digits, '0') + '.webp?v=' + part.revision;
    }

    function evict(cache, limit, keep, close) {
        while (cache.size > limit) {
            const outside = [...cache.keys()]
                .sort((a, b) => Number(keep.has(a)) - Number(keep.has(b)) || Math.abs(b - target) - Math.abs(a - target))[0];
            if (close) cache.get(outside).close();
            cache.delete(outside);
        }
    }

    async function load(index, decode) {
        const current = generation;
        const deadline = new AbortController();
        const abort = () => deadline.abort();
        const signal = controller.signal;
        signal.addEventListener('abort', abort, { once: true });
        if (signal.aborted) deadline.abort();
        const timeout = setTimeout(abort, 4000);
        pending.add(index);
        try {
            let blob = encoded.get(index);
            if (!blob) {
                const response = await fetch(asset(index), { signal: deadline.signal, cache: 'force-cache' });
                if (!response.ok) throw new Error('Zodiac frame unavailable');
                blob = await response.blob();
                if (current !== generation) return;
                encoded.set(index, blob);
                evict(encoded, narrow.matches ? 16 : 24, encodedWanted, false);
            }
            if (!decode) return;
            const bitmap = await createImageBitmap(blob);
            if (current !== generation) { bitmap.close(); return; }
            frames.set(index, bitmap);
            evict(frames, narrow.matches ? 6 : 10, wanted, true);
        } catch {
            if (current !== generation) return;
            encoded.delete(index);
            const attempts = (failures.get(index) || 0) + 1;
            failures.set(index, attempts);
            if (attempts >= 2) failed.add(index);
        } finally {
            clearTimeout(timeout);
            signal.removeEventListener('abort', abort);
            if (current === generation) {
                pending.delete(index);
                requestDraw();
            }
        }
    }

    function warm(candidates, decode = true) {
        for (const index of candidates) {
            if (pending.size >= 3) return;
            if (index < 0 || index > finalFrame() || failed.has(index) || pending.has(index)) continue;
            if (decode ? !frames.has(index) : !encoded.has(index)) load(index, decode);
        }
    }

    function validTarget(desired) {
        if (!failed.has(desired)) return desired;
        return Array.from({ length: finalFrame() + 1 }, (_, index) => index)
            .filter((index) => !failed.has(index))
            .sort((a, b) => Math.abs(a - desired) - Math.abs(b - desired) || a - b)[0];
    }

    function draw() {
        scheduled = undefined;
        if (!sequence || !nearby) return;
        const bounds = journey.getBoundingClientRect();
        const stageHeight = stage.getBoundingClientRect().height;
        const stageTop = parseFloat(getComputedStyle(stage).top) || 0;
        const doorEnd = Math.max(2, arrival.offsetTop - 16);
        const doorSpan = Math.min(doorEnd - 1, Math.max(160, stageHeight * .34));
        const mainEnd = Math.max(1, Math.min((bounds.height - stageHeight) * .82, doorEnd - doorSpan));
        const position = stageTop - bounds.top;
        const progress = clamp(position / mainEnd);
        const doorProgress = clamp((position - mainEnd) / (doorEnd - mainEnd));
        const scrollFrame = Math.round(progress * mainLast()) + Math.round(doorProgress * (sequence.door.count - 1));
        journey.style.setProperty('--zodiac-progress', progress.toFixed(4));
        canvas.dataset.scrollEnd = mainEnd.toFixed(3);

        const desired = validTarget(scrollFrame);
        if (desired === undefined) { fallback(); return; }
        if (desired !== target) direction = Math.sign(desired - target);
        target = desired;

        const decodeLimit = narrow.matches ? 6 : 10;
        const near = [target];
        for (let distance = 1; distance < decodeLimit; distance += 1) {
            near.push(target + distance * direction, target - distance * direction);
        }
        const decodeWindow = near.filter((index) => index >= 0 && index <= finalFrame() && !failed.has(index)).slice(0, decodeLimit);
        const ahead = Array.from({ length: narrow.matches ? 9 : 17 }, (_, index) => target + index * direction);
        wanted = new Set(decodeWindow);
        encodedWanted = new Set([...decodeWindow, ...ahead]);
        warm(decodeWindow);
        warm(ahead, false);

        let next = target;
        if (!frames.has(next)) {
            const lower = Math.min(painted, target);
            const upper = Math.max(painted, target);
            // A late response can advance toward the current scroll position, but never past it.
            next = [...frames.keys()].filter((index) => index >= lower && index <= upper)
                .sort((a, b) => Math.abs(a - target) - Math.abs(b - target))[0];
        }
        if (next === undefined || next === painted) return;
        context.drawImage(frames.get(next), 0, 0, canvas.width, canvas.height);
        canvas.dataset.frame = String(next);
        canvas.dataset.doorFrame = String(Math.max(0, next - mainLast()));
        painted = next;
        journey.setAttribute('data-zodiac-ready', '');
        clearTimeout(startupTimer);
    }

    async function activate() {
        if (!nearby || !capable() || sequence || (controller && !controller.signal.aborted)) return;
        const current = ++generation;
        controller = new AbortController();
        startupTimer = setTimeout(() => { if (current === generation && !frames.size) fallback(); }, 12000);
        try {
            if (!manifest) {
                const response = await fetch(journey.dataset.frames + '/' + journey.dataset.sequenceManifest + '?v=' + journey.dataset.sequenceVersion, {
                    signal: controller.signal,
                    cache: 'force-cache',
                });
                if (!response.ok) throw new Error('Zodiac manifest unavailable');
                const result = await response.json();
                if (current !== generation) return;
                manifest = result;
            }
            const variant = narrow.matches ? manifest.mobile : manifest.desktop;
            if (!variant || !Number.isInteger(variant.count) || variant.count < 2
                || !variant.door || !Number.isInteger(variant.door.count) || variant.door.count < 2
                || !Number.isInteger(variant.door.width) || !Number.isInteger(variant.door.height)
            ) throw new Error('Invalid Zodiac manifest');
            if (current !== generation) return;
            sequence = variant;
            canvas.width = variant.door.width;
            canvas.height = variant.door.height;
            requestDraw();
        } catch {
            if (current === generation) fallback();
        }
    }

    function configure() {
        release();
        if (!capable()) { fallback(); return; }
        journey.dataset.zodiacMode = 'sequence';
        activate();
    }

    if ('IntersectionObserver' in window) {
        new IntersectionObserver(([entry]) => {
            nearby = entry.isIntersecting;
            if (nearby && journey.dataset.zodiacMode === 'sequence') activate().then(requestDraw);
            else if (!nearby) release();
        }, { rootMargin: '100% 0px' }).observe(journey);
    }
    window.addEventListener('scroll', requestDraw, { passive: true });
    window.addEventListener('resize', requestDraw, { passive: true });
    window.addEventListener('pagehide', release);
    window.addEventListener('pageshow', configure);
    narrow.addEventListener('change', configure);
    motion.addEventListener('change', configure);
    contrast.addEventListener('change', configure);
    navigator.connection?.addEventListener('change', configure);
    configure();
}
