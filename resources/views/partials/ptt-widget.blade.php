@if(auth()->check())
<div id="ptt-widget" class="fixed bottom-6 right-6 z-[9999] flex flex-col items-center">
    <!-- Drag Handle -->
    <div id="ptt-drag-handle" style="touch-action: none;" class="w-10 h-5 mb-2 bg-gray-600 rounded-full flex items-center justify-center cursor-move opacity-50 hover:opacity-100 shadow-md">
        <div class="w-5 h-1 border-y border-white opacity-80 pointer-events-none"></div>
    </div>
    <!-- Mic Button -->
    <button id="ptt-button" class="w-16 h-16 bg-blue-600 rounded-full shadow-lg flex items-center justify-center text-white text-2xl hover:bg-blue-700 active:bg-red-600 transition-colors select-none"
            onmousedown="PTTManager.startPTT()"
            onmouseup="PTTManager.stopPTT()"
            onmouseleave="PTTManager.stopPTT()"
            ontouchstart="PTTManager.startPTT()"
            ontouchend="PTTManager.stopPTT()">
        <i class="fa-solid fa-microphone pointer-events-none"></i>
    </button>
</div>
<script>
    window.userId = {{ auth()->id() }};
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = '{{ csrf_token() }}';

    // Drag Logic
    (function initDrag() {
        const widget = document.getElementById('ptt-widget');
        const handle = document.getElementById('ptt-drag-handle');
        if (!widget || !handle) return;

        let isDragging = false;
        let startX, startY, initialX, initialY;

        const dragStart = (e) => {
            isDragging = true;
            const clientX = e.touches ? e.touches[0].clientX : e.clientX;
            const clientY = e.touches ? e.touches[0].clientY : e.clientY;
            
            const rect = widget.getBoundingClientRect();
            initialX = rect.left;
            initialY = rect.top;
            startX = clientX;
            startY = clientY;
            
            widget.style.bottom = 'auto';
            widget.style.right = 'auto';
            widget.style.left = initialX + 'px';
            widget.style.top = initialY + 'px';
            
            if (e.type === 'touchstart') e.preventDefault();
        };

        const drag = (e) => {
            if (!isDragging) return;
            if (e.type === 'touchmove') e.preventDefault();
            
            const clientX = e.touches ? e.touches[0].clientX : e.clientX;
            const clientY = e.touches ? e.touches[0].clientY : e.clientY;
            
            widget.style.left = (initialX + (clientX - startX)) + 'px';
            widget.style.top = (initialY + (clientY - startY)) + 'px';
        };

        const dragEnd = () => isDragging = false;

        handle.addEventListener('mousedown', dragStart);
        handle.addEventListener('touchstart', dragStart, {passive: false});
        document.addEventListener('mousemove', drag);
        document.addEventListener('touchmove', drag, {passive: false});
        document.addEventListener('mouseup', dragEnd);
        document.addEventListener('touchend', dragEnd);
    })();
</script>
@endif
