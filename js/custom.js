jQuery(document).ready(function ($) {

    /* ====================
    INITIALIZATION
    ======================= */

    console.log("✅ Optimized Custom JS Loaded!");

    var $imageContainers = $('.scrolling-images-wrap');

    /* ====================
    INITIALIZE SLIDES FOR INFINITE SCROLLING
    ======================= */

    function initializeSlides($container) {
        const $slides = $container.children('.image-row');

        if ($slides.length === 0) {
            console.error("❌ No images found in container: " + $container.data('scroller-id'));
            return;
        }

        // Ensure original images are not lost
        $container.empty();
        $slides.each(function () {
            $container.append($(this).clone());
        });

        // Clone slides for seamless infinite scrolling
        for (let i = 0; i < 2; i++) {
            $slides.each(function () {
                $container.append($(this).clone());
            });
        }
    }

    /* ====================
    AUTO-SCROLL FUNCTION (Fixed & Optimized)
    ======================= */

    function scrollContainer($container) {
        if (!$container.length || $container.hasClass('dragging')) return; // Disable auto-scroll when dragging

        let hasHorizontalClass = $container.hasClass('scroll-horizontal');
        let hasVerticalClass = $container.hasClass('scroll-vertical');

        let imageScrollPosition = hasHorizontalClass ? $container.scrollLeft() : $container.scrollTop();
        let baseSpeed = 0.15;
        let hoverSpeed = baseSpeed * 2;
        let currentSpeed = baseSpeed;

        function animateScroll() {
            if ($container.hasClass('dragging') || !$container.data('scrollRunning')) return; // Stop if dragging or not running

            if (hasHorizontalClass) {
                imageScrollPosition += currentSpeed;
                $container.scrollLeft(imageScrollPosition);

                const scrollWidth = $container[0].scrollWidth / 3;
                if (imageScrollPosition >= scrollWidth) {
                    imageScrollPosition -= scrollWidth;
                    $container.scrollLeft(imageScrollPosition);
                }
            }

            if (hasVerticalClass) {
                imageScrollPosition += currentSpeed;
                $container.scrollTop(imageScrollPosition);

                const scrollHeight = $container[0].scrollHeight / 3;
                if (imageScrollPosition >= scrollHeight) {
                    imageScrollPosition -= scrollHeight;
                    $container.scrollTop(imageScrollPosition);
                }
            }

            requestAnimationFrame(animateScroll);
        }

        // 🔥 Optimized hover event handling (removes unnecessary triggers)
        $container.on('mouseenter', function () {
            if (!$container.hasClass('dragging')) {
                currentSpeed = hoverSpeed;
            }
        });

        $container.on('mouseleave', function () {
            if (!$container.hasClass('dragging')) {
                currentSpeed = baseSpeed;
            }
        });

        // Start auto-scrolling only if in view
        $container.data('scrollRunning', true);
        requestAnimationFrame(animateScroll);
    }

    /* ====================
    HANDLE SCROLLING & DRAGGING
    ======================= */

    function setupScroller($container) {
        var hasHorizontalClass = $container.hasClass('scroll-horizontal');
        var hasVerticalClass = $container.hasClass('scroll-vertical');

        let isDragging = false;
        let startX, startY, lastScrollLeft, lastScrollTop;

        /* ====================
        DRAG FUNCTIONALITY (Fixed & Optimized)
        ======================= */

        function startDrag(e) {
            isDragging = true;
            const event = e.type.includes("mouse") ? e : e.touches[0];

            // Store initial drag start positions
            startX = event.pageX - $container.offset().left;
            startY = event.pageY - $container.offset().top;
            lastScrollLeft = $container.scrollLeft();
            lastScrollTop = $container.scrollTop();

            $container.addClass('dragging');
            e.preventDefault();
        }

        function onDrag(e) {
            if (!isDragging) return;
            const event = e.type.includes("mouse") ? e : e.touches[0];

            // Calculate movement from start position
            const deltaX = event.pageX - $container.offset().left - startX;
            const deltaY = event.pageY - $container.offset().top - startY;

            if (hasHorizontalClass) {
                $container.scrollLeft(lastScrollLeft - deltaX);
            }
            if (hasVerticalClass) {
                $container.scrollTop(lastScrollTop - deltaY);
            }
        }

        function endDrag() {
            isDragging = false;
            $container.removeClass('dragging');

            // 🔥 FIX: Set `imageScrollPosition` to match where drag ended
            if (hasHorizontalClass) {
                imageScrollPosition = $container.scrollLeft();
            }
            if (hasVerticalClass) {
                imageScrollPosition = $container.scrollTop();
            }

            // Resume auto-scrolling
            requestAnimationFrame(() => scrollContainer($container));
        }

        $container.on('mousedown touchstart', startDrag);
        $(document).on('mousemove touchmove', onDrag);
        $(document).on('mouseup touchend touchcancel', endDrag);
    }

    /* ====================
    PERFORMANCE OPTIMIZATION: STOP AUTO-SCROLL WHEN NOT IN VIEW
    ======================= */

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            const $container = $(entry.target);
            if (entry.isIntersecting) {
                $container.data('scrollRunning', true);
                requestAnimationFrame(() => scrollContainer($container));
            } else {
                $container.data('scrollRunning', false);
            }
        });
    });

    /* ====================
    LOOP THROUGH SCROLLERS & INITIALIZE
    ======================= */

    $imageContainers.each(function () {
        var $container = $(this);
        initializeSlides($container);
        setupScroller($container);
        observer.observe($container[0]); // Optimize performance
    });

    /* ====================
    CLEANUP EVENT LISTENERS WHEN NOT NEEDED
    ======================= */

    $(window).on('beforeunload', function () {
        $imageContainers.each(function () {
            var $container = $(this);
            $container.off('mouseenter mouseleave');
            $(document).off('mousemove touchmove mouseup touchend touchcancel');
        });
    });

});
