jQuery(document).ready(function ($) {
    /* ====================
    IMAGE SCROLLERS APPLICATION
    ======================= */

    var $imageContainers = $('.scrolling-images-wrap');
    let initializedContainers = new Set(); // Track initialized containers

    $imageContainers.each(function () {
        var $container = $(this);
        var containerId = $container.data('scroller-id');

        // Avoid reinitializing the same container
        if (initializedContainers.has(containerId)) return;
        initializedContainers.add(containerId);

        var hasHorizontalClass = $container.hasClass('scroll-horizontal');
        var hasVerticalClass = $container.hasClass('scroll-vertical');
        let baseSpeed = 0.15;
        let hoverSpeed = baseSpeed * 2;
        let currentSpeed = baseSpeed;
        let imageScrollPosition = 0;
        let isAnimating = true;

        // Initialize slides for infinite scrolling
        function initializeSlides() {
            const $slides = $container.children('.image-row');

            // Check if valid images exist
            if ($slides.length === 0) {
                console.error("No images found in the container for scroller ID: " + containerId);
                $container.html('<p>No images to display. Please add images in the admin panel.</p>');
                return;
            }

            // Clear old content and re-add valid images
            $container.empty();
            const fragment = document.createDocumentFragment();
            $slides.each(function () {
                const $slide = $(this).clone();
                fragment.appendChild($slide[0]); // Append original slides to fragment
            });

            // Clone slides for infinite scrolling dynamically based on container size
            const slidesNeeded = hasHorizontalClass
                ? Math.ceil($container.width() / $slides.first().width())
                : Math.ceil($container.height() / $slides.first().height());

            for (let i = 0; i < slidesNeeded; i++) {
                $slides.each(function () {
                    const $clone = $(this).clone();
                    fragment.appendChild($clone[0]);
                });
            }
            $container.append(fragment);
        }

        initializeSlides();

        // Hover effects to adjust scrolling speed
        $container.hover(
            function () {
                currentSpeed = hoverSpeed;
            },
            function () {
                currentSpeed = baseSpeed;
            }
        );

        let isDragging = false;
        let startX, startY, scrollLeft, scrollTop;

        // Throttle for drag events
        function throttle(fn, limit) {
            let lastFunc, lastRan;
            return function () {
                const context = this;
                const args = arguments;
                if (!lastRan) {
                    fn.apply(context, args);
                    lastRan = Date.now();
                } else {
                    clearTimeout(lastFunc);
                    lastFunc = setTimeout(function () {
                        if (Date.now() - lastRan >= limit) {
                            fn.apply(context, args);
                            lastRan = Date.now();
                        }
                    }, limit - (Date.now() - lastRan));
                }
            };
        }

        // Handle both mouse and touch events for drag functionality
        function startDrag(e) {
            isDragging = true;
            const event = e.type === 'mousedown' ? e : e.touches[0];
            startX = event.pageX - $container.offset().left;
            startY = event.pageY - $container.offset().top;
            scrollLeft = $container.scrollLeft();
            scrollTop = $container.scrollTop();
            $container.addClass('dragging');
            e.preventDefault();
        }

        function onDrag(e) {
            if (!isDragging) return;
            const event = e.type === 'mousemove' ? e : e.touches[0];
            const x = event.pageX - $container.offset().left;
            const y = event.pageY - $container.offset().top;
            const walkX = startX - x;
            const walkY = startY - y;

            if (hasHorizontalClass) $container.scrollLeft(scrollLeft + walkX);
            if (hasVerticalClass) $container.scrollTop(scrollTop + walkY);
        }

        function endDrag() {
            if (isDragging) {
                isDragging = false;
                $container.removeClass('dragging');
                if (hasHorizontalClass) {
                    imageScrollPosition = $container.scrollLeft();
                } else if (hasVerticalClass) {
                    imageScrollPosition = $container.scrollTop();
                }
                scrollContainer(); // Resume auto-scroll
            }
        }

        $container.on('mousedown touchstart', startDrag);
        $(document).on('mousemove touchmove', throttle(onDrag, 50));
        $(document).on('mouseup touchend touchcancel', endDrag);

        // Auto-scroll functionality
        function scrollContainer() {
            if (!isAnimating || isDragging) return; // Disable auto-scroll while dragging or if stopped

            if (hasHorizontalClass) {
                imageScrollPosition += currentSpeed;
                $container.scrollLeft(imageScrollPosition);

                const scrollWidth = $container[0].scrollWidth / 3;
                if (imageScrollPosition >= scrollWidth) {
                    imageScrollPosition -= scrollWidth;
                    $container.scrollLeft(imageScrollPosition);
                }
            } else if (hasVerticalClass) {
                imageScrollPosition += currentSpeed;
                $container.scrollTop(imageScrollPosition);

                const scrollHeight = $container[0].scrollHeight / 3;
                if (imageScrollPosition >= scrollHeight) {
                    imageScrollPosition -= scrollHeight;
                    $container.scrollTop(imageScrollPosition);
                }
            }
            requestAnimationFrame(scrollContainer);
        }

        scrollContainer();

        // Stop animation when the window is unloaded or the container is removed
        $(window).on('unload', function () {
            isAnimating = false;
        });
    });
});
