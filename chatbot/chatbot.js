
// chatbot/chatbot.js - VERSIÓN CORREGIDA Y COMPLETA
document.addEventListener('DOMContentLoaded', function () {
  // Referencias del DOM
  const container = document.getElementById('chatbot-container');
  const chatbotBubble = document.getElementById('chatbot-bubble');
  const chatbotWindow = document.getElementById('chatbot-window');
  const chatbotClose = document.getElementById('chatbot-close');
  const chatbotForm = document.getElementById('chatbot-form');
  const chatbotInput = document.getElementById('chatbot-input');
  const chatbotMessages = document.getElementById('chatbot-messages');
  const quickSuggestions = document.querySelectorAll('.quick-suggestion');

  // Estado
  let hasShownWelcome = false;
  let isChatOpen = false;
  let isWaitingForResponse = false;

  // ========================================
  // INICIALIZACIÓN
  // ========================================
  if (container) container.setAttribute('data-state', 'closed');
  if (chatbotWindow) chatbotWindow.classList.add('hidden');

  // ========================================
  // UTILIDADES UI
  // ========================================
  function scrollToBottom() {
    if (chatbotMessages) {
      chatbotMessages.scrollTo({ top: chatbotMessages.scrollHeight, behavior: 'smooth' });
    }
  }

  function createUserMessage(message) {
    const userMessageDiv = document.createElement('div');
    userMessageDiv.className = 'user-message flex items-end justify-end space-x-3 animate-fade-in';
    userMessageDiv.innerHTML = `
      <div class="message-content bg-gradient-to-r from-cyan-500 to-blue-600 rounded-2xl rounded-tr-md px-4 py-3 max-w-xs shadow-xl">
        <p class="text-white text-sm leading-relaxed">${message}</p>
      </div>
      <div class="flex-shrink-0 w-10 h-10 bg-gradient-to-br from-slate-600 to-slate-700 rounded-2xl flex items-center justify-center shadow-lg">
        <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
          <path d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"/>
        </svg>
      </div>
    `;
    return userMessageDiv;
  }

  function createBotMessage(htmlContent) {
    const botMessageDiv = document.createElement('div');
    botMessageDiv.className = 'bot-message flex items-start space-x-3 animate-fade-in';
    // Asegúrate de que el contenido HTML se renderice correctamente
    botMessageDiv.innerHTML = `
      <div class="flex-shrink-0 w-10 h-10 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-2xl flex items-center justify-center shadow-lg">
        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
        </svg>
      </div>
      <div class="message-content bg-white/20 backdrop-blur-sm border border-white/30 rounded-2xl rounded-tl-md px-4 py-3 max-w-md shadow-xl text-white text-sm leading-relaxed prose prose-invert prose-sm">
        ${htmlContent}
      </div>
    `;
    return botMessageDiv;
  }

  function showTypingIndicator() {
    const typingDiv = document.createElement('div');
    typingDiv.className = 'bot-message flex items-start space-x-3 animate-fade-in typing-indicator';
    typingDiv.innerHTML = `
      <div class="flex-shrink-0 w-10 h-10 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-2xl flex items-center justify-center shadow-lg">
        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
      </div>
      <div class="message-content bg-white/20 backdrop-blur-sm border border-white/30 rounded-2xl rounded-tl-md px-4 py-3 shadow-xl">
        <div class="typing-indicator-dots">
          <div class="typing-dot"></div> <div class="typing-dot"></div> <div class="typing-dot"></div>
        </div>
      </div>
    `;
    chatbotMessages.appendChild(typingDiv);
    scrollToBottom();
  }

  function removeTypingIndicator() {
    const indicator = chatbotMessages.querySelector('.typing-indicator');
    if (indicator) indicator.remove();
  }

  // ========================================
  // ENVÍO DE MENSAJE (LÓGICA DE API REAL)
  // ========================================
  async function sendMessage(messageText) {
    const message = messageText || (chatbotInput ? chatbotInput.value.trim() : '');
    if (!message || isWaitingForResponse) return;

    isWaitingForResponse = true;
    chatbotInput.disabled = true;

    const userMessage = createUserMessage(message);
    chatbotMessages.appendChild(userMessage);
    if (chatbotInput) chatbotInput.value = '';
    setTimeout(scrollToBottom, 50);

    showTypingIndicator();

    try {
      const response = await fetch('chatbot/chatbot_api_simple.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
        body: JSON.stringify({ message: message })
      });

      removeTypingIndicator();

      if (!response.ok) {
        const errorData = await response.json().catch(() => ({ message: 'Error desconocido del servidor.' }));
        throw new Error(errorData.message || `Error del servidor: ${response.status}`);
      }

      const data = await response.json();
      const botReply = data.reply || "No he podido procesar tu solicitud.";
      
      const renderedHtml = typeof marked !== 'undefined' ? marked.parse(botReply) : botReply;

      const botMessage = createBotMessage(renderedHtml);
      chatbotMessages.appendChild(botMessage);
      
    } catch (error) {
      console.error("Error en el chatbot:", error);
      removeTypingIndicator();
      const errorMessage = createBotMessage(`Lo siento, hubo un error de conexión. Inténtalo de nuevo. <br><small class="opacity-70">(${error.message})</small>`);
      chatbotMessages.appendChild(errorMessage);
    } finally {
      isWaitingForResponse = false;
      chatbotInput.disabled = false;
      if (isChatOpen) chatbotInput.focus();
      scrollToBottom();
    }
  }

  // ========================================
  // APERTURA Y CIERRE
  // ========================================
  function openChat() {
    isChatOpen = true;
    if (container) container.setAttribute('data-state', 'open');
    if (chatbotWindow) {
      chatbotWindow.classList.remove('hidden');
      requestAnimationFrame(() => chatbotWindow.classList.add('open'));
    }
    if (chatbotBubble) {
      Object.assign(chatbotBubble.style, { display: 'none', opacity: '0', visibility: 'hidden', pointerEvents: 'none', transform: 'scale(0)' });
    }
    if (!hasShownWelcome && chatbotMessages && chatbotMessages.children.length === 0) {
      hasShownWelcome = true;
      setTimeout(() => {
        const welcomeHtml = typeof marked !== 'undefined' ? marked.parse('¡Hola! Soy tu **asistente de inventario**. ¿En qué puedo ayudarte hoy?') : '¡Hola! Soy tu asistente de inventario. ¿En qué puedo ayudarte hoy?';
        const welcome = createBotMessage(welcomeHtml);
        chatbotMessages.appendChild(welcome);
        scrollToBottom();
      }, 300);
    }
    if (chatbotInput) setTimeout(() => chatbotInput.focus(), 300);
  }

  function closeChat() {
    isChatOpen = false;
    if (container) container.setAttribute('data-state', 'closed');
    if (chatbotWindow) {
      chatbotWindow.classList.remove('open');
      setTimeout(() => chatbotWindow.classList.add('hidden'), 300);
    }
    if (chatbotBubble) {
      Object.assign(chatbotBubble.style, { display: 'block', opacity: '1', visibility: 'visible', pointerEvents: 'auto', transform: 'scale(1)' });
    }
  }

  function toggleChatbot() {
    isChatOpen ? closeChat() : openChat();
  }

  // ========================================
  // EVENTOS
  // ========================================
  if (chatbotBubble) chatbotBubble.addEventListener('click', e => { e.stopPropagation(); toggleChatbot(); });
  if (chatbotClose) chatbotClose.addEventListener('click', e => { e.stopPropagation(); toggleChatbot(); });
  if (chatbotWindow) chatbotWindow.addEventListener('click', e => e.stopPropagation());
  if (chatbotForm) chatbotForm.addEventListener('submit', e => { e.preventDefault(); sendMessage(); });
  if (chatbotInput) chatbotInput.addEventListener('keypress', e => { if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); sendMessage(); } });
  quickSuggestions.forEach(button => {
    button.addEventListener('click', e => {
      e.stopPropagation();
      sendMessage(button.getAttribute('data-message'));
    });
  });

  console.log('✅ Chatbot inteligente inicializado correctamente');
});
