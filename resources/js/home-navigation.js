const homeNavigation = document.querySelector('[data-home-navigation]');

if (homeNavigation) {
    const toggles = [...homeNavigation.querySelectorAll('[data-home-nav-toggle]')];
    const panels = [...homeNavigation.querySelectorAll('[data-home-nav-panel]')];
    const chapters = [...homeNavigation.querySelectorAll('[data-home-chapter]')];
    const currentNumber = homeNavigation.querySelector('[data-home-current-number]');
    const currentName = homeNavigation.querySelector('[data-home-current-name]');
    const previewImage = homeNavigation.querySelector('[data-home-preview-image]');
    const previewName = homeNavigation.querySelector('[data-home-preview-name]');
    const previewTheme = homeNavigation.querySelector('[data-home-preview-theme]');
    const desktopPreview = matchMedia('(min-width: 1001px)');
    let openPanel;
    let activeChapter;
    let scheduled;

    function preview(chapter) {
        if (!desktopPreview.matches) return;
        if (previewImage.getAttribute('src') !== chapter.dataset.homePreview) {
            previewImage.src = chapter.dataset.homePreview;
        }
        previewName.textContent = chapter.dataset.homeChapterName;
        previewTheme.textContent = chapter.dataset.homeChapterTheme;
    }

    function closePanel(returnFocus = false) {
        const previous = openPanel;
        openPanel = undefined;
        panels.forEach((panel) => { panel.hidden = true; });
        toggles.forEach((toggle) => { toggle.setAttribute('aria-expanded', 'false'); });
        if (returnFocus) toggles.find((toggle) => toggle.dataset.homeNavToggle === previous)?.focus();
        schedule();
    }

    function showPanel(name, focusFirst = false) {
        closePanel();
        openPanel = name;
        const panel = panels.find((item) => item.dataset.homeNavPanel === name);
        const toggle = toggles.find((item) => item.dataset.homeNavToggle === name);
        panel.hidden = false;
        toggle.setAttribute('aria-expanded', 'true');
        if (name === 'experiences' && activeChapter) preview(activeChapter);
        if (focusFirst) panel.querySelector('a, button')?.focus({ preventScroll: true });
    }

    function updateCurrentChapter() {
        scheduled = undefined;
        if (openPanel) return;
        const headerHeight = parseFloat(getComputedStyle(homeNavigation).getPropertyValue('--home-header-height'));
        const readingLine = headerHeight + (innerHeight - headerHeight) * .3;
        let current = chapters[0];
        chapters.forEach((chapter) => {
            const section = document.getElementById(chapter.dataset.homeChapter);
            if (section && section.getBoundingClientRect().top <= readingLine) current = chapter;
        });
        if (current === activeChapter) return;
        activeChapter = current;
        currentNumber.textContent = String(chapters.indexOf(current) + 1).padStart(2, '0');
        currentName.textContent = current.dataset.homeChapterName;
        toggles[0].setAttribute('aria-label', `Experiencias. Ahora en ${current.dataset.homeChapterName}`);
        chapters.forEach((chapter) => {
            chapter.toggleAttribute('data-current', chapter === current);
            const link = chapter.querySelector('[data-home-scene-link]');
            if (chapter === current) link.setAttribute('aria-current', 'location');
            else link.removeAttribute('aria-current');
        });
    }

    function schedule() {
        if (scheduled === undefined) scheduled = requestAnimationFrame(updateCurrentChapter);
    }

    toggles.forEach((toggle) => {
        toggle.hidden = false;
        toggle.addEventListener('click', () => {
            const name = toggle.dataset.homeNavToggle;
            if (openPanel === name) closePanel();
            else showPanel(name);
        });
    });

    chapters.forEach((chapter) => {
        chapter.addEventListener('mouseenter', () => preview(chapter));
        chapter.addEventListener('focusin', () => preview(chapter));
    });

    homeNavigation.addEventListener('click', (event) => {
        const link = event.target.closest('a[href^="#"]');
        if (!link || event.button !== 0 || event.ctrlKey || event.metaKey || event.shiftKey || event.altKey) return;
        const destination = document.getElementById(link.hash.slice(1));
        if (!destination) return;
        closePanel();
        if (!destination.hasAttribute('tabindex')) destination.setAttribute('tabindex', '-1');
        // Let the native link update the hash and history after the panel closes.
        requestAnimationFrame(() => {
            destination.scrollIntoView({ behavior: 'instant', block: 'start' });
            destination.focus({ preventScroll: true });
        });
    });

    document.addEventListener('click', (event) => {
        const trigger = event.target.closest('[data-home-nav-open]');
        if (!trigger) return;
        event.preventDefault();
        showPanel(trigger.dataset.homeNavOpen, true);
    });

    document.addEventListener('pointerdown', (event) => {
        if (openPanel && !homeNavigation.contains(event.target)) closePanel();
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && openPanel) {
            event.preventDefault();
            closePanel(true);
        }
    });

    homeNavigation.addEventListener('focusout', (event) => {
        if (openPanel && event.relatedTarget && !homeNavigation.contains(event.relatedTarget)) closePanel();
    });

    window.addEventListener('scroll', schedule, { passive: true });
    window.addEventListener('resize', schedule, { passive: true });
    window.addEventListener('pageshow', () => closePanel());
    desktopPreview.addEventListener('change', () => {
        if (openPanel === 'experiences' && activeChapter) preview(activeChapter);
    });
    panels.forEach((panel) => { panel.hidden = true; });
    homeNavigation.setAttribute('data-enhanced', '');
    updateCurrentChapter();
}
