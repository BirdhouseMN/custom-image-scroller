/* ====================
IMAGE SCROLLERS APPLICATION
======================= */

var $imageContainers = $('.scrolling-images-wrap');

$imageContainers.each(function () {
    var $container = $(this);
    var hasHorizontalClass = $container.hasClass('scroll-horizontal');
    var hasVerticalClass = $container.hasClass('scroll-vertical');
    let baseSpeed = 0.15;
    let hoverSpeed = baseSpeed * 2;
    let currentSpeed = baseSpeed;
    let imageScrollPosition = 0;

    // Initialize slides for infinite scrolling
    function initializeSlides() {
        const $slides = $container.children('.image-row');

        // Check if valid images exist
        if ($slides.length === 0) {
            console.error("No images found in the container for scroller ID: " + $container.data('scroller-id'));
            $container.html('<p>No images to display. Please add images in the admin panel.</p>');
            return;
        }

        // Clear old content and re-add valid images
        $container.empty();
        $slides.each(function () {
            const $slide = $(this).clone();
            $container.append($slide); // Append original slides
        });

        // Clone slides for infinite scrolling
        for (let i = 0; i < 2; i++) {
            $slides.each(function () {
                const $clone = $(this).clone();
                $container.append($clone);
            });
        }
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
    $(document).on('mousemove touchmove', onDrag);
    $(document).on('mouseup touchend touchcancel', endDrag);

    // Auto-scroll functionality
    function scrollContainer() {
        if (isDragging) return; // Disable auto-scroll while dragging

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
});
