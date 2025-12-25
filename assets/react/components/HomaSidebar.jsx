import React, { useState, useEffect, useRef } from 'react';
import { useHomaStore } from '../store/homaStore';
import MessageList from './MessageList';
import ChatInput from './ChatInput';
import SmartChips from './SmartChips';

/**
 * HomaSidebar Component
 * Main React component for the Homa chatbot sidebar
 */
const HomaSidebar = () => {
    const [isOpen, setIsOpen] = useState(false);
    const { messages, addMessage, userPersona, setUserPersona } = useHomaStore();
    const messagesEndRef = useRef(null);

    useEffect(() => {
        // Listen for sidebar toggle events
        const handleToggle = (event) => {
            setIsOpen(event.detail.isOpen);
        };

        document.addEventListener('homa:toggle-sidebar', handleToggle);

        // Load chat history from localStorage
        loadChatHistory();

        // Listen for site changes
        const handleSiteChange = (event) => {
            const { fieldId, value } = event.detail;
            console.log(`کاربر فیلد ${fieldId} را به ${value} تغییر داد`);
            // هما میتواند اینجا واکنش نشان دهد
        };
        window.addEventListener('homa_site_updated', handleSiteChange);

        return () => {
            document.removeEventListener('homa:toggle-sidebar', handleToggle);
            window.removeEventListener('homa_site_updated', handleSiteChange);
        };
    }, []);

    useEffect(() => {
        // Auto-scroll to bottom when new messages arrive
        scrollToBottom();
    }, [messages]);

    const scrollToBottom = () => {
        messagesEndRef.current?.scrollIntoView({ behavior: 'smooth' });
    };

    const loadChatHistory = () => {
        try {
            const history = localStorage.getItem('homa_chat_history');
            if (history) {
                const parsed = JSON.parse(history);
                // Load messages into store
                if (parsed.messages && Array.isArray(parsed.messages)) {
                    parsed.messages.forEach(msg => addMessage(msg));
                }
                if (parsed.persona) {
                    setUserPersona(parsed.persona);
                }
            }
        } catch (error) {
            console.error('Failed to load chat history:', error);
        }
    };

    const saveChatHistory = () => {
        try {
            const history = {
                messages: messages,
                persona: userPersona,
                timestamp: Date.now()
            };
            localStorage.setItem('homa_chat_history', JSON.stringify(history));
        } catch (error) {
            console.error('Failed to save chat history:', error);
        }
    };

    useEffect(() => {
        // Save chat history whenever messages or persona change
        if (messages.length > 0) {
            saveChatHistory();
        }
    }, [messages, userPersona]);

    const handleSendMessage = async (message) => {
        // Add user message
        const userMessage = {
            id: Date.now(),
            type: 'user',
            content: message,
            timestamp: new Date()
        };
        addMessage(userMessage);

        // Call AI endpoint
        try {
            // Check if nonce is available
            if (!window.homayeParallelUIConfig?.nonce) {
                throw new Error('امنیت: نشست شما منقضی شده است. لطفاً صفحه را رفرش کنید.');
            }

            const response = await fetch('/wp-json/homaye/v1/ai/chat', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': window.homayeParallelUIConfig.nonce
                },
                body: JSON.stringify({
                    message: message,
                    persona: userPersona,
                    context: {
                        page: window.location.pathname,
                        formData: getFormData()
                    }
                })
            });

            const data = await response.json();
            
            if (data.success) {
                // Add AI response with streaming effect
                const aiMessage = {
                    id: Date.now() + 1,
                    type: 'assistant',
                    content: data.response,
                    timestamp: new Date(),
                    actions: data.actions || []
                };
                addMessage(aiMessage);

                // Execute any UI actions
                if (data.actions && Array.isArray(data.actions)) {
                    executeActions(data.actions);
                }
            }
        } catch (error) {
            console.error('Failed to send message:', error);
            const errorMessage = {
                id: Date.now() + 1,
                type: 'assistant',
                content: 'متأسفم، خطایی رخ داد. لطفاً دوباره امتحان کنید.',
                timestamp: new Date()
            };
            addMessage(errorMessage);
        }
    };

    const getFormData = () => {
        // Collect current form data from the site
        const formData = {};
        const inputs = document.querySelectorAll('#homa-site-view input, #homa-site-view select, #homa-site-view textarea');
        inputs.forEach(input => {
            if (input.name) {
                formData[input.name] = input.value;
            }
        });
        return formData;
    };

    const executeActions = (actions) => {
        actions.forEach(action => {
            if (action.type === 'highlight' && action.selector) {
                executeOnSite(action.selector);
            } else if (action.type === 'scroll' && action.selector) {
                const element = document.querySelector(`#homa-site-view ${action.selector}`);
                if (element) {
                    element.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            } else if (action.type === 'fill' && action.field && action.value) {
                if (window.Homa && window.Homa.FormHydration) {
                    window.Homa.FormHydration.syncField(action.field, action.value);
                }
            }
        });
    };

    const executeOnSite = (selector) => {
        const el = document.querySelector(`#homa-site-view ${selector}`);
        if (el) {
            el.classList.add('homa-pulse');
            el.scrollIntoView({ behavior: 'smooth', block: 'center' });
            setTimeout(() => {
                el.classList.remove('homa-pulse');
            }, 3000);
        }
    };

    const handleChipClick = (chipAction) => {
        if (typeof chipAction === 'string') {
            handleSendMessage(chipAction);
        } else if (chipAction.message) {
            handleSendMessage(chipAction.message);
        }
    };

    return (
        <div className={`homa-sidebar-container ${isOpen ? 'open' : ''}`}>
            <div className="homa-sidebar-header">
                <div className="homa-avatar">
                    <span className="homa-avatar-icon">🤖</span>
                </div>
                <div className="homa-header-text">
                    <h3>همای تابش</h3>
                    <p>دستیار هوشمند چاپ</p>
                </div>
                <button 
                    className="homa-close-btn"
                    onClick={() => {
                        document.body.classList.remove('homa-open');
                        setIsOpen(false);
                    }}
                    aria-label="بستن"
                >
                    ✕
                </button>
            </div>

            <div className="homa-sidebar-content">
                <MessageList messages={messages} />
                <div ref={messagesEndRef} />
            </div>

            <SmartChips 
                persona={userPersona}
                onChipClick={handleChipClick}
            />

            <ChatInput onSendMessage={handleSendMessage} />
        </div>
    );
};

export default HomaSidebar;
