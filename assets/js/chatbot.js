/**
 * BHFE Chatbot JavaScript
 */

(function($) {
    'use strict';
    
    const Chatbot = {
        init: function() {
            this.bindEvents();
            this.applyTheme();
            this.applyPosition();
            this.showWelcome();
        },
        
        bindEvents: function() {
            const self = this;
            
            // Toggle chatbot
            $('#bhfe-chatbot-toggle').on('click', function() {
                self.toggleChatbot();
            });
            
            // Close chatbot
            $('#bhfe-chatbot-close').on('click', function() {
                self.closeChatbot();
            });
            
            // Send message
            $('#bhfe-chatbot-send').on('click', function() {
                self.sendMessage();
            });
            
            // Enter key to send
            $('#bhfe-chatbot-input').on('keydown', function(e) {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    self.sendMessage();
                }
            });
            
            // Auto-resize textarea
            $('#bhfe-chatbot-input').on('input', function() {
                this.style.height = 'auto';
                this.style.height = Math.min(this.scrollHeight, 100) + 'px';
            });
        },
        
        toggleChatbot: function() {
            $('#bhfe-chatbot-container').toggleClass('active');
            $('#bhfe-chatbot-input').focus();
        },
        
        closeChatbot: function() {
            $('#bhfe-chatbot-container').removeClass('active');
        },
        
        showWelcome: function() {
            const welcomeMessage = 'Hello! I\'m here to help you find information about our CPE/CE courses for CFPs, CPAs, IRS enrolled agents, CDFAs, IARs, and other professionals. What would you like to know?';
            
            setTimeout(function() {
                Chatbot.addMessage('bot', welcomeMessage);
            }, 300);
        },
        
        sendMessage: function() {
            const input = $('#bhfe-chatbot-input');
            const message = input.val().trim();
            
            if (!message) {
                return;
            }
            
            // Add user message to chat
            this.addMessage('user', message);
            
            // Clear input
            input.val('');
            input.css('height', 'auto');
            
            // Disable send button and show typing indicator
            $('#bhfe-chatbot-send').prop('disabled', true);
            this.showTyping();
            
            // Send AJAX request
            $.ajax({
                url: bhfeChatbot.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'bhfe_chatbot_send_message',
                    nonce: bhfeChatbot.nonce,
                    message: message
                },
                success: function(response) {
                    Chatbot.hideTyping();
                    
                    if (response.success && response.data) {
                        Chatbot.addMessage('bot', response.data.message);
                    } else {
                        Chatbot.addMessage('bot', 'I apologize, but I encountered an error. Please try again.');
                    }
                    
                    Chatbot.scrollToBottom();
                },
                error: function() {
                    Chatbot.hideTyping();
                    Chatbot.addMessage('bot', 'I apologize, but I couldn\'t connect to the server. Please try again later.');
                    Chatbot.scrollToBottom();
                },
                complete: function() {
                    $('#bhfe-chatbot-send').prop('disabled', false);
                    input.focus();
                }
            });
        },
        
        addMessage: function(type, message) {
            const messagesContainer = $('#bhfe-chatbot-messages');
            const messageHtml = this.formatMessage(message);
            
            const messageElement = $('<div>', {
                class: 'bhfe-chatbot-message ' + type
            }).html(`<div class="bhfe-chatbot-message-content">${messageHtml}</div>`);
            
            messagesContainer.append(messageElement);
            this.scrollToBottom();
        },
        
        formatMessage: function(message) {
            // Convert line breaks to <br>
            message = message.replace(/\n/g, '<br>');
            
            // Convert markdown-style formatting to HTML
            message = message.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
            message = message.replace(/\*(.*?)\*/g, '<em>$1</em>');
            
            // Convert URLs to links
            const urlRegex = /(https?:\/\/[^\s]+)/g;
            message = message.replace(urlRegex, '<a href="$1" target="_blank" rel="noopener noreferrer">$1</a>');
            
            return '<p>' + message + '</p>';
        },
        
        showTyping: function() {
            const messagesContainer = $('#bhfe-chatbot-messages');
            const typingElement = $('<div>', {
                class: 'bhfe-chatbot-message bot'
            }).html('<div class="bhfe-chatbot-typing"><div class="bhfe-chatbot-typing-dot"></div><div class="bhfe-chatbot-typing-dot"></div><div class="bhfe-chatbot-typing-dot"></div></div>');
            
            messagesContainer.append(typingElement);
            this.scrollToBottom();
        },
        
        hideTyping: function() {
            $('.bhfe-chatbot-typing').parent().remove();
        },
        
        scrollToBottom: function() {
            const messagesContainer = $('#bhfe-chatbot-messages');
            messagesContainer.scrollTop(messagesContainer[0].scrollHeight);
        },
        
        applyTheme: function() {
            const themeColor = bhfeChatbot.themeColor || '#2563eb';
            
            // Apply to toggle button
            $('#bhfe-chatbot-toggle').css('background', themeColor);
            
            // Apply to header
            $('.bhfe-chatbot-header').css('background', themeColor);
            
            // Apply to send button
            $('#bhfe-chatbot-send').css('background', themeColor);
            
            // Apply to user messages
            const customCSS = `
                .bhfe-chatbot-message.user .bhfe-chatbot-message-content,
                #bhfe-chatbot-send:hover:not(:disabled) {
                    background: ${themeColor} !important;
                }
                .bhfe-chatbot-input:focus {
                    border-color: ${themeColor} !important;
                }
            `;
            
            if (!$('#bhfe-chatbot-custom-theme').length) {
                $('head').append('<style id="bhfe-chatbot-custom-theme">' + customCSS + '</style>');
            }
        },
        
        applyPosition: function() {
            const position = bhfeChatbot.position || 'bottom-right';
            const container = $('#bhfe-chatbot-container');
            
            container.removeClass('bottom-right bottom-left top-right top-left');
            container.addClass(position);
        }
    };
    
    // Initialize when document is ready
    $(document).ready(function() {
        Chatbot.init();
    });
    
})(jQuery);

