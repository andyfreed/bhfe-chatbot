/**
 * WordPress Chatbot Widget
 * 
 * Embed this script in your WordPress site to add the chat widget.
 * 
 * Usage:
 * 1. Add this script to your WordPress theme's footer.php or use a plugin
 * 2. Configure the MIDDLEWARE_URL variable below
 * 
 * Example in footer.php:
 * <script>
 *   const CHATBOT_MIDDLEWARE_URL = 'https://your-middleware-url.com';
 * </script>
 * <script src="/path/to/chat-widget.js"></script>
 */

(function() {
  'use strict';

  // Configuration - Update this with your middleware server URL
  const MIDDLEWARE_URL = window.CHATBOT_MIDDLEWARE_URL || 'http://localhost:3000';
  const CHAT_ENDPOINT = `${MIDDLEWARE_URL}/chat`;

  // Chat state
  let threadId = null;
  let isOpen = false;
  let isLoading = false;

  // Create chat widget HTML
  function createChatWidget() {
    // Create container
    const container = document.createElement('div');
    container.id = 'chatbot-widget';
    container.innerHTML = `
      <div class="chatbot-toggle" id="chatbot-toggle" title="Open Chat">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
        </svg>
      </div>
      <div class="chatbot-window" id="chatbot-window">
        <div class="chatbot-header">
          <h3>AI Assistant</h3>
          <button class="chatbot-close" id="chatbot-close" title="Close Chat">×</button>
        </div>
        <div class="chatbot-messages" id="chatbot-messages"></div>
        <div class="chatbot-input-container">
          <input 
            type="text" 
            id="chatbot-input" 
            placeholder="Type your message..."
            disabled
          />
          <button id="chatbot-send" disabled>Send</button>
        </div>
      </div>
    `;

    // Add styles
    const style = document.createElement('style');
    style.textContent = `
      #chatbot-widget {
        position: fixed;
        bottom: 20px;
        right: 20px;
        z-index: 9999;
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
      }

      .chatbot-toggle {
        width: 60px;
        height: 60px;
        background: #0073aa;
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        transition: transform 0.2s;
      }

      .chatbot-toggle:hover {
        transform: scale(1.1);
      }

      .chatbot-window {
        position: absolute;
        bottom: 80px;
        right: 0;
        width: 400px;
        height: 600px;
        background: white;
        border-radius: 12px;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
        display: none;
        flex-direction: column;
        overflow: hidden;
      }

      .chatbot-window.open {
        display: flex;
      }

      .chatbot-header {
        background: #0073aa;
        color: white;
        padding: 16px;
        display: flex;
        justify-content: space-between;
        align-items: center;
      }

      .chatbot-header h3 {
        margin: 0;
        font-size: 18px;
        font-weight: 600;
      }

      .chatbot-close {
        background: none;
        border: none;
        color: white;
        font-size: 28px;
        cursor: pointer;
        padding: 0;
        width: 30px;
        height: 30px;
        line-height: 1;
      }

      .chatbot-close:hover {
        opacity: 0.8;
      }

      .chatbot-messages {
        flex: 1;
        overflow-y: auto;
        padding: 16px;
        background: #f5f5f5;
      }

      .chatbot-message {
        margin-bottom: 16px;
        display: flex;
        flex-direction: column;
      }

      .chatbot-message.user {
        align-items: flex-end;
      }

      .chatbot-message.assistant {
        align-items: flex-start;
      }

      .chatbot-message-content {
        max-width: 80%;
        padding: 12px 16px;
        border-radius: 18px;
        word-wrap: break-word;
      }

      .chatbot-message.user .chatbot-message-content {
        background: #0073aa;
        color: white;
      }

      .chatbot-message.assistant .chatbot-message-content {
        background: white;
        color: #333;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
      }

      .chatbot-message.loading .chatbot-message-content {
        background: #e0e0e0;
        color: #666;
        font-style: italic;
      }

      .chatbot-input-container {
        display: flex;
        padding: 16px;
        background: white;
        border-top: 1px solid #e0e0e0;
        gap: 8px;
      }

      #chatbot-input {
        flex: 1;
        padding: 12px;
        border: 1px solid #ddd;
        border-radius: 24px;
        font-size: 14px;
        outline: none;
      }

      #chatbot-input:focus {
        border-color: #0073aa;
      }

      #chatbot-input:disabled {
        background: #f5f5f5;
        cursor: not-allowed;
      }

      #chatbot-send {
        padding: 12px 24px;
        background: #0073aa;
        color: white;
        border: none;
        border-radius: 24px;
        cursor: pointer;
        font-size: 14px;
        font-weight: 600;
      }

      #chatbot-send:hover:not(:disabled) {
        background: #005a87;
      }

      #chatbot-send:disabled {
        background: #ccc;
        cursor: not-allowed;
      }

      @media (max-width: 480px) {
        .chatbot-window {
          width: 100vw;
          height: 100vh;
          bottom: 0;
          right: 0;
          border-radius: 0;
        }

        #chatbot-widget {
          bottom: 0;
          right: 0;
        }
      }
    `;

    document.head.appendChild(style);
    document.body.appendChild(container);

    // Set up event listeners
    setupEventListeners();
  }

  // Set up event listeners
  function setupEventListeners() {
    const toggle = document.getElementById('chatbot-toggle');
    const close = document.getElementById('chatbot-close');
    const input = document.getElementById('chatbot-input');
    const send = document.getElementById('chatbot-send');
    const window = document.getElementById('chatbot-window');

    toggle.addEventListener('click', () => {
      isOpen = !isOpen;
      window.classList.toggle('open', isOpen);
      if (isOpen) {
        input.focus();
      }
    });

    close.addEventListener('click', () => {
      isOpen = false;
      window.classList.remove('open');
    });

    send.addEventListener('click', sendMessage);
    input.addEventListener('keypress', (e) => {
      if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        sendMessage();
      }
    });
  }

  // Add message to chat
  function addMessage(content, role = 'assistant') {
    const messagesContainer = document.getElementById('chatbot-messages');
    const messageDiv = document.createElement('div');
    messageDiv.className = `chatbot-message ${role}`;
    
    const contentDiv = document.createElement('div');
    contentDiv.className = 'chatbot-message-content';
    contentDiv.textContent = content;
    
    messageDiv.appendChild(contentDiv);
    messagesContainer.appendChild(messageDiv);
    
    // Scroll to bottom
    messagesContainer.scrollTop = messagesContainer.scrollHeight;
    
    return messageDiv;
  }

  // Update message content (for streaming)
  function updateMessage(messageDiv, content) {
    const contentDiv = messageDiv.querySelector('.chatbot-message-content');
    contentDiv.textContent = content;
    
    // Scroll to bottom
    const messagesContainer = document.getElementById('chatbot-messages');
    messagesContainer.scrollTop = messagesContainer.scrollHeight;
  }

  // Send message to backend
  async function sendMessage() {
    const input = document.getElementById('chatbot-input');
    const send = document.getElementById('chatbot-send');
    const message = input.value.trim();

    if (!message || isLoading) return;

    // Clear input and disable
    input.value = '';
    input.disabled = true;
    send.disabled = true;
    isLoading = true;

    // Add user message
    addMessage(message, 'user');

    // Add loading message
    const loadingMessage = addMessage('Thinking...', 'assistant');
    loadingMessage.classList.add('loading');

    try {
      // Send to backend with streaming
      const response = await fetch(CHAT_ENDPOINT, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'text/event-stream'
        },
        body: JSON.stringify({
          message: message,
          threadId: threadId
        })
      });

      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }

      // Remove loading message
      loadingMessage.remove();

      // Create assistant message for streaming
      const assistantMessage = addMessage('', 'assistant');

      // Read stream
      const reader = response.body.getReader();
      const decoder = new TextDecoder();
      let buffer = '';

      while (true) {
        const { done, value } = await reader.read();
        if (done) break;

        buffer += decoder.decode(value, { stream: true });
        const lines = buffer.split('\n');
        buffer = lines.pop() || '';

        for (const line of lines) {
          if (line.startsWith('data: ')) {
            try {
              const data = JSON.parse(line.slice(6));
              
              if (data.type === 'threadId') {
                threadId = data.threadId;
              } else if (data.type === 'content') {
                const currentText = assistantMessage.querySelector('.chatbot-message-content').textContent;
                updateMessage(assistantMessage, currentText + data.content);
              } else if (data.type === 'done') {
                // Streaming complete
              }
            } catch (e) {
              console.error('Error parsing SSE data:', e);
            }
          }
        }
      }
    } catch (error) {
      console.error('Chat error:', error);
      loadingMessage.remove();
      addMessage('Sorry, I encountered an error. Please try again.', 'assistant');
    } finally {
      // Re-enable input
      input.disabled = false;
      send.disabled = false;
      isLoading = false;
      input.focus();
    }
  }

  // Initialize when DOM is ready
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', createChatWidget);
  } else {
    createChatWidget();
  }
})();

