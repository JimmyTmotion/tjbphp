(function () {
    var header = document.getElementById('mainHeader');
    var body = document.body;
    var revealSelector = [
        '.site-content > section',
        '.site-content > section .section-title',
        '.site-content > section .about-image-part',
        '.site-content > section .about-content-part',
        '.site-content > section .single-resume',
        '.site-content > section .resume-item',
        '.site-content > section .testimonial-item',
        '.site-content > section .slider-arrows',
        '.site-content > section video',
        '.site-content > section .company-list',
        '.site-content > section .tjb-footer-row',
        '.site-content > section .skills-category-card',
        '.site-content > section .skills-category-list li'
    ].join(',');
    var isNavigating = false;

    function updateStickyHeader() {
        if (!header) return;
        if (window.scrollY > 85) {
            header.classList.add('fixed-header');
        } else {
            header.classList.remove('fixed-header');
        }
    }

    function initTestimonialSlider() {
        var slider = document.querySelector('[data-slider]');
        var prevButton = document.querySelector('[data-testimonial-prev]');
        var nextButton = document.querySelector('[data-testimonial-next]');

        if (!slider || !prevButton || !nextButton) {
            return;
        }

        var slides = Array.prototype.slice.call(slider.querySelectorAll('[data-slide]'));
        if (!slides.length) {
            return;
        }

        var currentIndex = 0;

        function render() {
            slides.forEach(function (slide, index) {
                if (index === currentIndex) {
                    slide.classList.add('is-active');
                } else {
                    slide.classList.remove('is-active');
                }
            });
        }

        prevButton.addEventListener('click', function () {
            currentIndex = (currentIndex - 1 + slides.length) % slides.length;
            render();
        });

        nextButton.addEventListener('click', function () {
            currentIndex = (currentIndex + 1) % slides.length;
            render();
        });

        render();
    }

    function setReveal(el, type, delay) {
        if (!el || el.getAttribute('data-reveal')) {
            return;
        }
        el.setAttribute('data-reveal', type || 'up');
        if (typeof delay === 'number' && delay > 0) {
            el.style.setProperty('--reveal-delay', delay + 'ms');
        }
    }

    function setStaggeredReveal(selector, type, step, maxDelay) {
        var nodes = Array.prototype.slice.call(document.querySelectorAll(selector));
        nodes.forEach(function (el, index) {
            var delay = Math.min(index * step, maxDelay);
            setReveal(el, type, delay);
        });
    }

    function initRevealAnimations() {
        var reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

        setStaggeredReveal('.site-content > section', 'up', 90, 280);
        setStaggeredReveal('.site-content > section .section-title', 'up', 70, 220);
        setStaggeredReveal('.site-content > section .single-resume', 'up', 90, 220);
        setStaggeredReveal('.site-content > section .resume-item', 'up', 70, 280);
        setStaggeredReveal('.site-content > section .skills-category-card', 'up', 90, 260);

        Array.prototype.slice.call(document.querySelectorAll('.site-content > section .about-image-part'))
            .forEach(function (el) { setReveal(el, 'left', 80); });
        Array.prototype.slice.call(document.querySelectorAll('.site-content > section .about-content-part'))
            .forEach(function (el) { setReveal(el, 'right', 120); });
        Array.prototype.slice.call(document.querySelectorAll('.site-content > section .testimonial-item, .site-content > section .slider-arrows, .site-content > section video, .site-content > section .company-list, .site-content > section .tjb-footer-row'))
            .forEach(function (el) { setReveal(el, 'up', 140); });

        Array.prototype.slice.call(document.querySelectorAll('.skills-category-card'))
            .forEach(function (card, cardIndex) {
                var items = Array.prototype.slice.call(card.querySelectorAll('.skills-category-list li'));
                items.forEach(function (item, itemIndex) {
                    var delay = Math.min((cardIndex * 120) + (itemIndex * 70), 520);
                    setReveal(item, 'up', delay);
                });
            });

        var targets = Array.prototype.slice.call(document.querySelectorAll(revealSelector));
        if (!targets.length) {
            return;
        }

        if (reduceMotion || !('IntersectionObserver' in window)) {
            targets.forEach(function (el) {
                el.classList.add('is-revealed');
            });
            return;
        }

        var observer = new IntersectionObserver(function (entries, io) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-revealed');
                    io.unobserve(entry.target);
                }
            });
        }, {
            threshold: 0.16,
            rootMargin: '0px 0px -8% 0px'
        });

        targets.forEach(function (el) {
            observer.observe(el);
        });
    }

    function markPageReady() {
        if (!body) {
            return;
        }
        body.classList.remove('page-is-loading');
        body.classList.remove('page-is-leaving');
        body.classList.add('page-is-ready');
    }

    function shouldTransitionLink(link, event) {
        if (!link || !body || isNavigating) {
            return false;
        }
        if (event.defaultPrevented || event.button !== 0) {
            return false;
        }
        if (event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
            return false;
        }
        if (link.target && link.target.toLowerCase() !== '_self') {
            return false;
        }
        if (link.hasAttribute('download')) {
            return false;
        }

        var href = link.getAttribute('href');
        if (!href || href.indexOf('#') === 0 || href.indexOf('mailto:') === 0 || href.indexOf('tel:') === 0 || href.indexOf('javascript:') === 0) {
            return false;
        }

        var url;
        try {
            url = new URL(link.href, window.location.href);
        } catch (error) {
            return false;
        }

        if (url.origin !== window.location.origin) {
            return false;
        }

        if (url.protocol !== 'http:' && url.protocol !== 'https:') {
            return false;
        }

        if (url.pathname === window.location.pathname && url.search === window.location.search && url.hash) {
            return false;
        }

        if (url.href === window.location.href) {
            return false;
        }

        return true;
    }

    function initPageTransitions() {
        if (!body) {
            return;
        }

        // Apply entrance animation once styles are painted and visible long enough to perceive motion.
        window.requestAnimationFrame(function () {
            window.requestAnimationFrame(function () {
                window.setTimeout(function () {
                    if (!isNavigating) {
                        markPageReady();
                    }
                }, 140);
            });
        });

        window.addEventListener('load', function () {
            if (!isNavigating) {
                markPageReady();
            }
        });

        document.addEventListener('click', function (event) {
            var target = event.target;
            if (!target || typeof target.closest !== 'function') {
                return;
            }
            var link = target.closest('a');
            if (!shouldTransitionLink(link, event)) {
                return;
            }

            event.preventDefault();
            isNavigating = true;
            body.classList.remove('page-is-ready');
            body.classList.add('page-is-leaving');

            window.setTimeout(function () {
                window.location.href = link.href;
            }, 340);
        });

        window.addEventListener('pageshow', function () {
            isNavigating = false;
            markPageReady();
        });
    }

    window.addEventListener('scroll', updateStickyHeader);
    updateStickyHeader();
    initTestimonialSlider();
    initRevealAnimations();
    initPageTransitions();
})();
