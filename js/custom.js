jQuery(document).ready(function($) {

    // Select all scrolling image containers
    var $imageContainers = $('.scrolling-images-wrap');

    $imageContainers.each(function() {
        var $container = $(this);
        var isHorizontal = $container.hasClass('scroll-horizontal');
        var isVertical = $container.hasClass('scroll-vertical');

        // State variables
        let autoSpeed = 0.15; // Auto-scroll speed
        let isDragging = false;
        let isMomentumActive = false;
        let lastMousePos = 0;
        let lastScrollPos = 0;
        let velocity = 0;

        // Clone slides for infinite scrolling
        function cloneSlides() {
            const $slides = $container.children('.image-row');
            for (let i = 0; i < 2; i++) {
                $slides.clone().appendTo($container);
            }
        }

        // Automatic scrolling
        function scrollContainer() {
            if (isMomentumActive || isDragging) return;

            lastScrollPos += autoSpeed;

            if (isHorizontal) {
                const maxScroll = $container[0].scrollWidth / 3;
                $container.scrollLeft(lastScrollPos % maxScroll);
            } else if (isVertical) {
                const maxScroll = $container[0].scrollHeight / 3;
                $container.scrollTop(lastScrollPos % maxScroll);
            }

            requestAnimationFrame(scrollContainer);
        }

        // Start dragging event
        function startDrag(e) {
            isDragging = true;
            isMomentumActive = false;

            const event = e.type === 'mousedown' ? e : e.touches[0];
            lastMousePos = isHorizontal
                ? event.pageX - $container.offset().left
                : event.pageY - $container.offset().top;

            lastScrollPos = isHorizontal
                ? $container.scrollLeft()
                : $container.scrollTop();

            velocity = 0; // Reset velocity
            e.preventDefault();
        }

        // Dragging event
        function onDrag(e) {
            if (!isDragging) return;

            const event = e.type === 'mousemove' ? e : e.touches[0];
            const currentMousePos = isHorizontal
                ? event.pageX - $container.offset().left
                : event.pageY - $container.offset().top;

            const delta = currentMousePos - lastMousePos;

            lastMousePos = currentMousePos;

            if (isHorizontal) {
                lastScrollPos = $container.scrollLeft() - delta;
                $container.scrollLeft(lastScrollPos);
            } else {
                lastScrollPos = $container.scrollTop() - delta;
                $container.scrollTop(lastScrollPos);
            }
        }

        // End dragging event
        function endDrag() {
            if (!isDragging) return;
            isDragging = false;
        }

        // Attach events to enable drag and scroll
        function setupEvents() {
            $container.on('mousedown touchstart', startDrag);
            $(document).on('mousemove touchmove', onDrag);
            $(document).on('mouseup touchend touchcancel', endDrag);
        }

        // Initialize the scroller
        cloneSlides();
        setupEvents();
        scrollContainer();
    });
});
