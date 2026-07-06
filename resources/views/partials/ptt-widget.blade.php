@if(auth()->check())
<div id="ptt-widget" class="fixed bottom-6 right-6 z-[9999]">
    <button id="ptt-button" class="w-16 h-16 bg-blue-600 rounded-full shadow-lg flex items-center justify-center text-white text-2xl hover:bg-blue-700 active:bg-red-600 transition-colors select-none"
            onmousedown="PTTManager.startPTT()"
            onmouseup="PTTManager.stopPTT()"
            onmouseleave="PTTManager.stopPTT()"
            ontouchstart="PTTManager.startPTT()"
            ontouchend="PTTManager.stopPTT()">
        <i class="fa-solid fa-microphone"></i>
    </button>
</div>
<script>
    window.userId = {{ auth()->id() }};
</script>
@endif
