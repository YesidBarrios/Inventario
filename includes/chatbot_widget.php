<!-- Chatbot Widget -->
<link rel="stylesheet" href="chatbot/chatbot.css?v=<?php echo time(); ?>">

<div id="chatbot-container" class="fixed bottom-6 right-6 z-[99]" data-state="closed">
    <div id="chatbot-bubble" class="relative group cursor-pointer transform transition-all duration-300 hover:scale-105">
        <div class="w-16 h-16 bg-gradient-to-br from-indigo-500 via-purple-600 to-pink-500 rounded-2xl flex items-center justify-center shadow-2xl hover:shadow-purple-500/25 transition-all duration-300"><svg class="w-8 h-8 text-white transition-transform duration-300 group-hover:rotate-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg></div>
        <div class="absolute -top-2 -right-2 w-6 h-6 bg-gradient-to-r from-red-500 to-pink-600 rounded-full border-2 border-white flex items-center justify-center animate-bounce"><span class="text-xs text-white font-bold">!</span></div>
        <div class="absolute right-full mr-4 top-1/2 -translate-y-1/2 bg-gradient-to-r from-slate-800 to-slate-900 text-white px-4 py-2 rounded-xl text-sm font-medium opacity-0 group-hover:opacity-100 transition-all duration-300 whitespace-nowrap shadow-xl backdrop-blur-sm">¿Necesitas ayuda? ✨<div class="absolute left-full top-1/2 -translate-y-1/2 border-4 border-transparent border-l-slate-800"></div></div>
    </div>
    <div id="chatbot-window" class="hidden">
        <div class="bg-gradient-to-r from-indigo-500 via-purple-600 to-pink-500 px-6 py-5 flex justify-between items-center relative overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-r from-indigo-400/20 via-purple-500/20 to-pink-400/20"></div>
            <div class="flex items-center space-x-3 relative z-10">
                <div class="w-12 h-12 bg-white/20 rounded-2xl flex items-center justify-center backdrop-blur-sm ring-2 ring-white/30"><svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg></div>
                <div><h3 class="text-white font-bold text-lg">Asistente IA</h3><div class="flex items-center space-x-2"><div class="w-2 h-2 bg-green-400 rounded-full animate-pulse"></div><p class="text-white/90 text-sm">En línea • Respuesta instantánea</p></div></div>
            </div>
            <button id="chatbot-close" class="text-white/90 hover:text-white transition-colors p-2 rounded-xl hover:bg-white/10 backdrop-blur-sm relative z-10"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
        </div>
        <div id="chatbot-messages" class="custom-scrollbar"></div>
        <div class="chat-footer">
            <form id="chatbot-form" class="flex items-center"><input type="text" id="chatbot-input" placeholder="Pregúntame sobre tu inventario..." autocomplete="off" class="flex-1"><button type="submit" class="flex-shrink-0"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg></button></form>
            <div class="quick-suggestions"><button class="quick-suggestion" data-message="productos con stock bajo">📊 Stock crítico</button><button class="quick-suggestion" data-message="qué me recomiendas">💡 Recomendaciones</button></div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
<script src="chatbot/chatbot.js"></script>
