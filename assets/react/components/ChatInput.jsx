import React, { useState } from 'react';

/**
 * ChatInput Component
 * Input field for user to send messages
 */
const ChatInput = ({ onSendMessage }) => {
    const [inputValue, setInputValue] = useState('');
    const [isSending, setIsSending] = useState(false);

    const handleSubmit = async (e) => {
        e.preventDefault();
        
        if (!inputValue.trim() || isSending) {
            return;
        }

        setIsSending(true);
        await onSendMessage(inputValue.trim());
        setInputValue('');
        setIsSending(false);
    };

    const handleKeyPress = (e) => {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            handleSubmit(e);
        }
    };

    return (
        <form className="homa-chat-input" onSubmit={handleSubmit}>
            <textarea
                value={inputValue}
                onChange={(e) => setInputValue(e.target.value)}
                onKeyPress={handleKeyPress}
                placeholder="پیام خود را بنویسید..."
                rows="2"
                disabled={isSending}
                dir="rtl"
            />
            <button 
                type="submit" 
                disabled={!inputValue.trim() || isSending}
                className="homa-send-btn"
                aria-label="ارسال"
            >
                {isSending ? '...' : '➤'}
            </button>
        </form>
    );
};

export default ChatInput;
