// Shared image-sequence controller for new experience chapters.
// The two existing chapters retain their accepted controllers.
for (const journey of document.querySelectorAll("[data-experience-sequence]")) {
    const canvas = journey.querySelector("[data-sequence-canvas]");
    const stage = journey.querySelector(".virus-stage");
    const arrival = journey.querySelector(".virus-arrival");
    const context = canvas?.getContext("2d", { alpha: false });
    const motion = window.matchMedia("(prefers-reduced-motion: reduce)");
    const narrow = window.matchMedia("(max-width: 760px)");
    const contrast = window.matchMedia("(forced-colors: active)");
    const root = journey.dataset.frames;
    const frames = new Map();
    const encoded = new Map();
    const pending = new Set();
    const failed = new Set();
    const steps = [...journey.querySelectorAll("[data-sequence-step]")];
    const defaultPhaseStarts = [0, 0.32, 0.81];
    const phaseStarts = steps.map((label, index) =>
        Number(label.dataset.sequenceStart ?? defaultPhaseStarts[index] ?? 1),
    );
    let controller;
    let sequence;
    let generation = 0;
    let scheduled;
    let startupTimer;
    let nearby = false;
    let target = 0;
    let direction = 1;
    let painted = -1;
    let manifest;

    const release = () => {
        generation += 1;
        controller?.abort();
        clearTimeout(startupTimer);
        if (scheduled !== undefined) cancelAnimationFrame(scheduled);
        scheduled = undefined;
        sequence = undefined;
        frames.forEach((frame) => frame.close());
        frames.clear();
        encoded.clear();
        pending.clear();
        failed.clear();
        painted = -1;
        journey.removeAttribute("data-sequence-ready");
    };

    const capable = () =>
        context &&
        "createImageBitmap" in window &&
        "IntersectionObserver" in window &&
        !motion.matches &&
        !contrast.matches &&
        !navigator.connection?.saveData;

    const fallback = () => {
        release();
        journey.dataset.sequenceMode = "static";
        journey.style.removeProperty("--sequence-progress");
    };

    const requestDraw = () => {
        if (sequence && nearby && scheduled === undefined)
            scheduled = requestAnimationFrame(draw);
    };

    const load = async (index, decode = true) => {
        const current = generation;
        const variant = sequence;
        pending.add(index);
        try {
            let blob = encoded.get(index);
            if (!blob) {
                const response = await fetch(
                    `${root}/${variant.prefix}${String(index).padStart(3, "0")}.webp?v=${variant.revision}`,
                    {
                        signal: controller.signal,
                        cache: "force-cache",
                    },
                );
                if (!response.ok)
                    throw new Error("Experience frame unavailable");
                blob = await response.blob();
                if (current !== generation) return;
                encoded.set(index, blob);
                while (encoded.size > (narrow.matches ? 16 : 24)) {
                    const furthest = [...encoded.keys()].sort(
                        (a, b) => Math.abs(b - target) - Math.abs(a - target),
                    )[0];
                    encoded.delete(furthest);
                }
            }
            if (!decode) return;
            const bitmap = await createImageBitmap(blob);
            if (current !== generation) {
                bitmap.close();
                return;
            }
            frames.set(index, bitmap);
            while (frames.size > (narrow.matches ? 6 : 10)) {
                const furthest = [...frames.keys()].sort(
                    (a, b) => Math.abs(b - target) - Math.abs(a - target),
                )[0];
                frames.get(furthest).close();
                frames.delete(furthest);
            }
        } catch (error) {
            if (current !== generation) return;
            failed.add(index);
            if (!frames.size && index === target) fallback();
        } finally {
            if (current === generation) {
                pending.delete(index);
                requestDraw();
            }
        }
    };

    const warm = () => {
        const radius = narrow.matches ? 2 : 4;
        const candidates = [target];
        for (let distance = 1; distance <= radius; distance += 1)
            candidates.push(
                target + distance * direction,
                target - distance * direction,
            );
        for (const index of candidates) {
            if (pending.size >= 3) break;
            if (
                index >= 0 &&
                index < sequence.count &&
                !frames.has(index) &&
                !pending.has(index) &&
                !failed.has(index)
            )
                load(index);
        }
        for (
            let distance = radius + 1;
            distance <= (narrow.matches ? 8 : 16);
            distance += 1
        ) {
            if (pending.size >= 3) break;
            const index = target + distance * direction;
            if (
                index >= 0 &&
                index < sequence.count &&
                !encoded.has(index) &&
                !pending.has(index) &&
                !failed.has(index)
            )
                load(index, false);
        }
    };

    function draw() {
        scheduled = undefined;
        if (!sequence || !nearby) return;
        const bounds = journey.getBoundingClientRect();
        const stageHeight = stage.getBoundingClientRect().height;
        const stageTop = parseFloat(getComputedStyle(stage).top) || 0;
        // Finish before the briefing reaches the header, even when mobile text grows.
        const distance = Math.max(1, Math.min(
            (bounds.height - stageHeight) * 0.82,
            arrival.offsetTop - stageHeight * 0.22,
        ));
        const progress = Math.min(
            1,
            Math.max(
                0,
                (stageTop - bounds.top) / distance,
            ),
        );
        const next = Math.round(progress * (sequence.count - 1));
        if (next !== target) direction = Math.sign(next - target);
        target = next;
        journey.style.setProperty("--sequence-progress", progress.toFixed(4));
        const phaseIndex = phaseStarts.findLastIndex((start) => progress >= start);
        steps.forEach((label, index) =>
            label.toggleAttribute("data-current", index === phaseIndex),
        );
        if (frames.size) {
            const nearest = [...frames.keys()].sort(
                (a, b) => Math.abs(a - target) - Math.abs(b - target),
            )[0];
            if (nearest !== painted) {
                // Exactly one opaque frame. No blending, artificial blur or sequence easing.
                context.drawImage(
                    frames.get(nearest),
                    0,
                    0,
                    canvas.width,
                    canvas.height,
                );
                canvas.dataset.frame = String(nearest);
                painted = nearest;
                journey.setAttribute("data-sequence-ready", "");
                clearTimeout(startupTimer);
            }
        }
        if (bounds.bottom > -innerHeight && bounds.top < innerHeight * 2)
            warm();
    }

    const activate = async () => {
        if (
            !nearby ||
            !capable() ||
            sequence ||
            (controller && !controller.signal.aborted)
        )
            return;
        const current = ++generation;
        controller = new AbortController();
        startupTimer = setTimeout(() => {
            if (current === generation && !frames.size) fallback();
        }, 12000);
        try {
            if (!manifest) {
                const response = await fetch(
                    `${root}/sequence.json?v=${journey.dataset.sequenceVersion}`,
                    {
                        signal: controller.signal,
                        cache: "force-cache",
                    },
                );
                if (!response.ok)
                    throw new Error("Experience sequence unavailable");
                const result = await response.json();
                if (current !== generation) return;
                manifest = result;
            }
            const variant = narrow.matches ? manifest.mobile : manifest.desktop;
            if (
                !variant ||
                !Number.isInteger(variant.count) ||
                variant.count < 2 ||
                !Number.isInteger(variant.width) ||
                !Number.isInteger(variant.height)
            )
                throw new Error("Invalid Experience sequence");
            if (current !== generation) return;
            sequence = variant;
            canvas.width = variant.width;
            canvas.height = variant.height;
            requestDraw();
        } catch (error) {
            if (current === generation) fallback();
        }
    };

    const configure = () => {
        release();
        if (!capable()) {
            fallback();
            return;
        }
        journey.dataset.sequenceMode = "sequence";
        activate();
    };

    if ("IntersectionObserver" in window) {
        const observer = new IntersectionObserver(
            ([entry]) => {
                nearby = entry.isIntersecting;
                if (nearby && journey.dataset.sequenceMode === "sequence")
                    activate().then(requestDraw).catch(fallback);
                else if (!nearby) release();
            },
            { rootMargin: "100% 0px" },
        );
        observer.observe(journey);
    }

    window.addEventListener("scroll", requestDraw, { passive: true });
    window.addEventListener("resize", requestDraw, { passive: true });
    window.addEventListener("pagehide", release);
    window.addEventListener("pageshow", configure);
    motion.addEventListener("change", configure);
    narrow.addEventListener("change", configure);
    contrast.addEventListener("change", configure);
    navigator.connection?.addEventListener("change", configure);
    configure();
}
