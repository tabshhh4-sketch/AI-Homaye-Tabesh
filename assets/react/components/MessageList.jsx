import React, { useEffect, useState } from 'react';

/**
 * MessageList Component
 * Displays chat messages with streaming text effect
 */
const MessageList = ({ messages }) => {
    return (
        <div className="homa-message-list">
            {messages.map((message) => (
                <Message key={message.id} message={message} />
            ))}
        </div>
    );
};

/**
 * Individual Message Component with streaming effect
 */
const Message = ({ message }) => {
    const [displayedContent, setDisplayedContent] = useState('');
    const [isStreaming, setIsStreaming] = useState(false);

    useEffect(() => {
        // Only stream assistant messages on first render
        if (message.type === 'assistant' && !displayedContent) {
            setIsStreaming(true);
            streamText(message.content);
        } else if (message.type === 'user') {
            setDisplayedContent(message.content);
        }
    }, [message.content]);

    const streamText = async (text) => {
        let index = 0;
        const delay = 20; // milliseconds per character

        const stream = () => {
            if (index < text.length) {
                setDisplayedContent((prev) => prev + text[index]);
                index++;
                setTimeout(stream, delay);
            } else {
                setIsStreaming(false);
            }
        };

        stream();
    };

    return (
        <div className={`homa-message homa-message-${message.type}`}>
            <div className="homa-message-content">
                {displayedContent || message.content}
                {isStreaming && <span className="homa-cursor">▊</span>}
            </div>
            {message.timestamp && (
                <div className="homa-message-time">
                    {new Date(message.timestamp).toLocaleTimeString('fa-IR', {
                        hour: '2-digit',
                        minute: '2-digit'
                    })}
                </div>
            )}
        </div>
    );
};

export default MessageList;
