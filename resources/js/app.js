import './bootstrap';
import './zodiac-journey';
import './experience-sequence';
import './legends-journey';
import './roadroom-journey';
import './home-navigation';

document.documentElement.classList.add('js');

// The mobile menu expands in the document flow, without a modal or scroll lock.
const menuToggle = document.querySelector('[data-menu-toggle]');
const navigation = document.getElementById('main-navigation');

if (menuToggle && navigation) {
    menuToggle.hidden = false;

    const closeMenu = () => {
        menuToggle.setAttribute('aria-expanded', 'false');
        navigation.removeAttribute('data-open');
    };

    menuToggle.addEventListener('click', () => {
        const opening = menuToggle.getAttribute('aria-expanded') !== 'true';
        menuToggle.setAttribute('aria-expanded', String(opening));
        navigation.toggleAttribute('data-open', opening);
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && menuToggle.getAttribute('aria-expanded') === 'true') {
            closeMenu();
            menuToggle.focus();
        }
    });

    window.matchMedia('(max-width: 1120px)').addEventListener('change', closeMenu);
    menuToggle.disabled = false;
}

document.querySelectorAll('[data-password-toggle]').forEach((toggle) => {
    const input = document.getElementById(toggle.getAttribute('aria-controls'));
    if (!input) return;

    toggle.hidden = false;
    toggle.addEventListener('click', () => {
        const showing = input.type === 'password';
        input.type = showing ? 'text' : 'password';
        toggle.setAttribute('aria-pressed', String(showing));
        toggle.setAttribute('aria-label', showing ? 'Ocultar contraseña' : 'Mostrar contraseña');
    });
    toggle.disabled = false;
});

// Keep normal server submissions and FormRequest validation; prevent repeat clicks.
document.querySelectorAll('form[data-submit]').forEach((form) => {
    form.addEventListener('submit', (event) => {
        if (form.dataset.submitting === 'true') {
            event.preventDefault();
            return;
        }

        form.dataset.submitting = 'true';
        form.setAttribute('aria-busy', 'true');
        form.querySelector('button[type="submit"]').disabled = true;
    });
});

window.addEventListener('pageshow', () => {
    document.querySelectorAll('form[data-submit]').forEach((form) => {
        delete form.dataset.submitting;
        form.removeAttribute('aria-busy');
        form.querySelector('button[type="submit"]').disabled = false;
    });

    document.querySelectorAll('[data-password-toggle]').forEach((toggle) => {
        const input = document.getElementById(toggle.getAttribute('aria-controls'));
        if (input) input.type = 'password';
        toggle.setAttribute('aria-pressed', 'false');
        toggle.setAttribute('aria-label', 'Mostrar contraseña');
    });

    document.querySelector('.field-control[aria-invalid="true"]')?.focus();
});

const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
const revealElements = document.querySelectorAll('[data-reveal]');

if (!reducedMotion.matches && 'IntersectionObserver' in window) {
    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    observer.unobserve(entry.target);
                }
            });
        },
        { threshold: 0.12 },
    );

    revealElements.forEach((element) => {
        element.classList.add('reveal-ready');
        observer.observe(element);
    });

    reducedMotion.addEventListener('change', (event) => {
        if (event.matches) {
            observer.disconnect();
            revealElements.forEach((element) => element.classList.remove('reveal-ready'));
        }
    });
}

// Homepage only: scroll follows the cobra through the pyramid using offline image frames.
const relicJourney = document.querySelector('[data-relic-journey]');

if (relicJourney) {
    const stage = relicJourney.querySelector('.relic-stage');
    const canvas = relicJourney.querySelector('.relic-canvas');
    const context = canvas.getContext('2d', { alpha: true });
    const mobileSequence = window.matchMedia('(max-width: 760px)');
    const forcedColors = window.matchMedia('(forced-colors: active)');
    const frameRoot = relicJourney.dataset.frames;
    let manifest;
    let sequence;
    let controller;
    let generation = 0;
    let animationFrame;
    let targetFrame = 0;
    let scrollDirection = 1;
    let paintedFrame = -1;
    let startupTimer;
    let nearby = !('IntersectionObserver' in window);
    const frames = new Map();
    const encodedFrames = new Map();
    const pending = new Set();
    const failed = new Set();

    const releaseFrames = () => {
        clearTimeout(startupTimer);
        controller?.abort();
        frames.forEach((frame) => frame.close());
        frames.clear();
        encodedFrames.clear();
        pending.clear();
        failed.clear();
        paintedFrame = -1;
        relicJourney.removeAttribute('data-relic-ready');
    };

    const showPoster = () => {
        generation += 1;
        sequence = undefined;
        releaseFrames();
        relicJourney.classList.add('relic-static');
        relicJourney.style.removeProperty('--relic-progress');
        relicJourney.style.removeProperty('--relic-inside');
        relicJourney.style.removeProperty('--relic-label-opacity');
    };

    const requestDraw = () => {
        if (animationFrame === undefined) animationFrame = window.requestAnimationFrame(draw);
    };

    const loadFrame = async (index, decode = true) => {
        const currentGeneration = generation;
        pending.add(index);

        try {
            let blob = encodedFrames.get(index);
            if (!blob) {
                const response = await fetch(
                    `${frameRoot}/${sequence.prefix}${String(index).padStart(3, '0')}.webp?v=${sequence.revision}`,
                    { signal: controller.signal, cache: 'force-cache' },
                );
                if (!response.ok) throw new Error('Frame unavailable');
                blob = await response.blob();
                if (currentGeneration !== generation) return;
                encodedFrames.set(index, blob);
                const encodedLimit = mobileSequence.matches ? 20 : 32;
                while (encodedFrames.size > encodedLimit) {
                    const furthest = [...encodedFrames.keys()].sort(
                        (a, b) => Math.abs(b - targetFrame) - Math.abs(a - targetFrame),
                    )[0];
                    encodedFrames.delete(furthest);
                }
            }
            if (!decode) return;
            const bitmap = await createImageBitmap(blob);
            if (currentGeneration !== generation) {
                bitmap.close();
                return;
            }

            frames.set(index, bitmap);
            // Bound decoded memory; the browser may keep compressed responses in its HTTP cache.
            const limit = mobileSequence.matches ? 8 : 12;
            while (frames.size > limit) {
                const furthest = [...frames.keys()].sort(
                    (a, b) => Math.abs(b - targetFrame) - Math.abs(a - targetFrame),
                )[0];
                frames.get(furthest).close();
                frames.delete(furthest);
            }
        } catch (error) {
            if (currentGeneration !== generation) return;
            failed.add(index);
            if (!frames.size && index === targetFrame) showPoster();
        } finally {
            if (currentGeneration === generation) {
                pending.delete(index);
                requestDraw();
            }
        }
    };

    const warmNearbyFrames = () => {
        const radius = mobileSequence.matches ? 3 : 5;
        const nearby = [targetFrame];
        for (let distance = 1; distance <= radius; distance += 1) {
            nearby.push(targetFrame + distance, targetFrame - distance);
        }

        for (const index of nearby) {
            if (pending.size >= 3) break;
            if (
                index >= 0 &&
                index < sequence.count &&
                !frames.has(index) &&
                !pending.has(index) &&
                !failed.has(index)
            ) {
                loadFrame(index);
            }
        }

        // Fetch a compressed strip ahead; only the small nearby window is decoded.
        const ahead = mobileSequence.matches ? 12 : 24;
        const direction =
            targetFrame === 0 ? 1 : targetFrame === sequence.count - 1 ? -1 : scrollDirection;
        for (let distance = radius + 1; distance <= ahead; distance += 1) {
            if (pending.size >= 3) break;
            const index = targetFrame + distance * direction;
            if (
                index >= 0 &&
                index < sequence.count &&
                !encodedFrames.has(index) &&
                !pending.has(index) &&
                !failed.has(index)
            ) {
                loadFrame(index, false);
            }
        }
    };

    function draw() {
        animationFrame = undefined;
        if (!sequence) return;

        const bounds = relicJourney.getBoundingClientRect();
        const viewport = window.innerHeight;
        const stageTop = parseFloat(getComputedStyle(stage).top) || 0;
        const duration = Math.max(1, bounds.height - stage.getBoundingClientRect().height);
        // Reach the chamber before the closing text enters; hold the interior while reading it.
        const progress = Math.min(1, Math.max(0, (stageTop - bounds.top) / duration / 0.78));
        const nextFrame = Math.round(progress * (sequence.count - 1));
        if (nextFrame !== targetFrame) scrollDirection = Math.sign(nextFrame - targetFrame);
        targetFrame = nextFrame;
        relicJourney.style.setProperty('--relic-progress', progress.toFixed(4));
        relicJourney.toggleAttribute('data-intro-ended', progress >= 0.38);
        relicJourney.style.setProperty(
            '--relic-inside',
            Math.min(1, Math.max(0, (progress - 0.25) / 0.4)).toFixed(4),
        );
        relicJourney.style.setProperty(
            '--relic-label-opacity',
            (1 - Math.min(1, Math.max(0, (progress - 0.65) / 0.15))).toFixed(4),
        );

        if (frames.size) {
            const nearest = [...frames.keys()].sort(
                (a, b) => Math.abs(a - targetFrame) - Math.abs(b - targetFrame),
            )[0];
            if (nearest !== paintedFrame) {
                context.clearRect(0, 0, canvas.width, canvas.height);
                context.drawImage(frames.get(nearest), 0, 0, canvas.width, canvas.height);
                paintedFrame = nearest;
                canvas.dataset.frame = String(nearest);
                relicJourney.setAttribute('data-relic-ready', '');
                clearTimeout(startupTimer);
            }
        }

        if (nearby && bounds.bottom > -viewport && bounds.top < viewport * 2) warmNearbyFrames();
    }

    const configureSequence = async () => {
        if (
            reducedMotion.matches ||
            forcedColors.matches ||
            navigator.connection?.saveData ||
            !context ||
            !('createImageBitmap' in window)
        ) {
            showPoster();
            return;
        }

        releaseFrames();
        const currentGeneration = ++generation;
        sequence = undefined;
        relicJourney.classList.remove('relic-static');
        if (!nearby || document.hidden) return;
        controller = new AbortController();
        startupTimer = setTimeout(() => {
            if (currentGeneration === generation && !frames.size) showPoster();
        }, 12000);

        try {
            if (!manifest) {
                const response = await fetch(`${frameRoot}/sequence.json`, { signal: controller.signal });
                if (!response.ok) throw new Error('Sequence unavailable');
                const variants = await response.json();
                if (currentGeneration !== generation) return;
                manifest = variants;
            }
            if (currentGeneration !== generation) return;
            sequence = mobileSequence.matches ? manifest.mobile : manifest.desktop;
            canvas.width = sequence.width;
            canvas.height = sequence.height;
            requestDraw();
        } catch (error) {
            if (currentGeneration === generation) showPoster();
        }
    };

    const suspendSequence = () => {
        generation += 1;
        sequence = undefined;
        releaseFrames();
    };
    if ('IntersectionObserver' in window) {
        new IntersectionObserver(([entry]) => {
            nearby = entry.isIntersecting;
            if (nearby) configureSequence();
            else suspendSequence();
        }, { rootMargin: '100% 0px' }).observe(relicJourney);
    }
    window.addEventListener('scroll', requestDraw, { passive: true });
    window.addEventListener('resize', requestDraw, { passive: true });
    window.addEventListener('pagehide', suspendSequence);
    window.addEventListener('pageshow', configureSequence);
    document.addEventListener('visibilitychange', () => {
        if (document.hidden) suspendSequence();
        else configureSequence();
    });
    mobileSequence.addEventListener('change', configureSequence);
    reducedMotion.addEventListener('change', configureSequence);
    forcedColors.addEventListener('change', configureSequence);
    navigator.connection?.addEventListener('change', configureSequence);
    configureSequence();
}

// Homepage only: ease the document's vertical wheel movement, leaving the image sequence unchanged.
if (relicJourney) {
    const finePointer = window.matchMedia('(pointer: fine)');
    const wheelSmoothingMs = 160;
    let wheelFrame;
    let wheelTarget = 0;
    let wheelTime;
    let expectedScroll;

    const stopWheel = () => {
        if (wheelFrame !== undefined) window.cancelAnimationFrame(wheelFrame);
        wheelFrame = undefined;
        wheelTime = undefined;
        expectedScroll = undefined;
    };

    const scrollLimit = () =>
        Math.max(0, document.scrollingElement.scrollHeight - window.innerHeight);

    function moveWheel(time) {
        wheelFrame = undefined;
        const current = window.scrollY;
        if (expectedScroll !== undefined && Math.abs(current - expectedScroll) > 2) {
            stopWheel();
            return;
        }

        wheelTarget = Math.min(scrollLimit(), Math.max(0, wheelTarget));
        const elapsed = wheelTime === undefined ? 16 : Math.min(48, Math.max(0, time - wheelTime));
        wheelTime = time;
        const distance = wheelTarget - current;
        const next =
            Math.abs(distance) < 0.5
                ? wheelTarget
                : current + distance * (1 - Math.exp(-elapsed / wheelSmoothingMs));
        window.scrollTo({ top: next, behavior: 'instant' });
        expectedScroll = window.scrollY;

        if (Math.abs(wheelTarget - expectedScroll) >= 0.5) {
            wheelFrame = window.requestAnimationFrame(moveWheel);
        } else {
            window.scrollTo({ top: wheelTarget, behavior: 'instant' });
            stopWheel();
        }
    }

    window.addEventListener(
        'wheel',
        (event) => {
            const nativeInput = event.composedPath().some((element) => {
                if (element.nodeType !== 1) return false;
                if (
                    element.matches(
                        'input, textarea, select, [contenteditable], dialog, [role="dialog"]',
                    )
                ) {
                    return true;
                }
                if (element === document.body || element === document.documentElement) return false;
                return (
                    element.scrollHeight > element.clientHeight + 1 &&
                    /(auto|scroll)/.test(window.getComputedStyle(element).overflowY)
                );
            });
            if (
                reducedMotion.matches ||
                !finePointer.matches ||
                event.defaultPrevented ||
                !event.cancelable ||
                event.ctrlKey ||
                event.metaKey ||
                event.shiftKey ||
                Math.abs(event.deltaX) >= Math.abs(event.deltaY) ||
                nativeInput
            ) {
                stopWheel();
                return;
            }

            const unit =
                event.deltaMode === 1 ? 16 : event.deltaMode === 2 ? window.innerHeight : 1;
            const delta = event.deltaY * unit;
            const current = window.scrollY;
            const reversing =
                wheelFrame !== undefined && Math.sign(delta) !== Math.sign(wheelTarget - current);
            const origin = wheelFrame === undefined || reversing ? current : wheelTarget;
            const target = Math.min(scrollLimit(), Math.max(0, origin + delta));
            if (Math.abs(target - current) < 0.5) {
                stopWheel();
                return;
            }

            event.preventDefault();
            wheelTarget = target;
            expectedScroll = current;
            if (wheelFrame === undefined) wheelFrame = window.requestAnimationFrame(moveWheel);
        },
        { passive: false },
    );

    // Hand control back immediately for keyboard, touch, scrollbar dragging or navigation.
    window.addEventListener('keydown', stopWheel);
    window.addEventListener('pointerdown', stopWheel, { passive: true });
    window.addEventListener('touchstart', stopWheel, { passive: true });
    window.addEventListener('resize', stopWheel, { passive: true });
    window.addEventListener('pagehide', stopWheel);
    window.addEventListener('pageshow', stopWheel);
    window.addEventListener('blur', stopWheel);
    window.addEventListener('hashchange', stopWheel);
    window.addEventListener(
        'scroll',
        () => {
            if (expectedScroll !== undefined && Math.abs(window.scrollY - expectedScroll) > 2) {
                stopWheel();
            }
        },
        { passive: true },
    );
    document.addEventListener('visibilitychange', stopWheel);
    reducedMotion.addEventListener('change', stopWheel);
    finePointer.addEventListener('change', stopWheel);
}
