/**
 * Feedback Buttons Component
 * 
 * Displays like/dislike buttons for user feedback on Homa responses
 * PR17: User Feedback System
 */

import React, { useState } from 'react';

const FeedbackButtons = ({ 
    conversationId, 
    responseText, 
    userPrompt,
    factsUsed = {},
    contextData = {},
    onFeedbackSubmitted = null 
}) => {
    const [feedbackGiven, setFeedbackGiven] = useState(null);
    const [showErrorForm, setShowErrorForm] = useState(false);
    const [errorDetails, setErrorDetails] = useState('');
    const [isSubmitting, setIsSubmitting] = useState(false);

    const submitFeedback = async (rating, additionalDetails = '') => {
        setIsSubmitting(true);

        try {
            const response = await fetch('/wp-json/homaye-tabesh/v1/feedback', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': window.homaReactData?.nonce || '',
                },
                body: JSON.stringify({
                    rating,
                    response_text: responseText,
                    user_prompt: userPrompt,
                    conversation_id: conversationId,
                    error_details: additionalDetails,
                    facts_used: factsUsed,
                    context_data: contextData,
                }),
            });

            const result = await response.json();

            if (result.success) {
                setFeedbackGiven(rating);
                setShowErrorForm(false);
                
                if (onFeedbackSubmitted) {
                    onFeedbackSubmitted(rating, result);
                }
            } else {
                alert(result.message || 'خطا در ثبت بازخورد');
            }
        } catch (error) {
            console.error('Error submitting feedback:', error);
            alert('خطا در ارتباط با سرور');
        } finally {
            setIsSubmitting(false);
        }
    };

    const handleLike = () => {
        submitFeedback('like');
    };

    const handleDislike = () => {
        setShowErrorForm(true);
    };

    const handleErrorSubmit = (e) => {
        e.preventDefault();
        if (errorDetails.trim()) {
            submitFeedback('dislike', errorDetails);
        }
    };

    if (feedbackGiven) {
        return (
            <div className="homa-feedback-thanks" style={styles.thanks}>
                {feedbackGiven === 'like' ? (
                    <>
                        <span style={styles.icon}>✅</span>
                        <span style={styles.text}>از بازخورد شما متشکریم</span>
                    </>
                ) : (
                    <>
                        <span style={styles.icon}>🙏</span>
                        <span style={styles.text}>بازخورد شما ثبت شد. تیم ما به زودی بررسی خواهد کرد</span>
                    </>
                )}
            </div>
        );
    }

    if (showErrorForm) {
        return (
            <div className="homa-feedback-error-form" style={styles.errorForm}>
                <p style={styles.errorLabel}>کدام بخش پاسخ اشتباه بود؟</p>
                <textarea
                    value={errorDetails}
                    onChange={(e) => setErrorDetails(e.target.value)}
                    placeholder="لطفاً توضیح دهید که چه مشکلی وجود داشت..."
                    style={styles.textarea}
                    rows={4}
                />
                <div style={styles.buttonGroup}>
                    <button
                        onClick={handleErrorSubmit}
                        disabled={isSubmitting || !errorDetails.trim()}
                        style={{
                            ...styles.submitButton,
                            ...(isSubmitting || !errorDetails.trim() ? styles.disabledButton : {}),
                        }}
                    >
                        {isSubmitting ? 'در حال ارسال...' : 'ارسال'}
                    </button>
                    <button
                        onClick={() => {
                            setShowErrorForm(false);
                            setErrorDetails('');
                        }}
                        style={styles.cancelButton}
                        disabled={isSubmitting}
                    >
                        انصراف
                    </button>
                </div>
            </div>
        );
    }

    return (
        <div className="homa-feedback-buttons" style={styles.container}>
            <span style={styles.label}>آیا این پاسخ مفید بود؟</span>
            <div style={styles.buttons}>
                <button
                    onClick={handleLike}
                    style={styles.button}
                    title="مفید بود"
                    aria-label="Like"
                    disabled={isSubmitting}
                >
                    👍
                </button>
                <button
                    onClick={handleDislike}
                    style={styles.button}
                    title="مفید نبود"
                    aria-label="Dislike"
                    disabled={isSubmitting}
                >
                    👎
                </button>
            </div>
        </div>
    );
};

const styles = {
    container: {
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'space-between',
        padding: '8px 12px',
        marginTop: '8px',
        borderTop: '1px solid #e0e0e0',
        fontSize: '13px',
        direction: 'rtl',
    },
    label: {
        color: '#666',
        marginLeft: '8px',
    },
    buttons: {
        display: 'flex',
        gap: '8px',
    },
    button: {
        background: 'none',
        border: '1px solid #ddd',
        borderRadius: '4px',
        padding: '6px 12px',
        cursor: 'pointer',
        fontSize: '16px',
        transition: 'all 0.2s',
        ':hover': {
            background: '#f5f5f5',
            borderColor: '#999',
        },
    },
    thanks: {
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'center',
        padding: '12px',
        marginTop: '8px',
        borderTop: '1px solid #e0e0e0',
        direction: 'rtl',
        color: '#4caf50',
    },
    icon: {
        fontSize: '18px',
        marginLeft: '8px',
    },
    text: {
        fontSize: '13px',
    },
    errorForm: {
        padding: '12px',
        marginTop: '8px',
        borderTop: '1px solid #e0e0e0',
        direction: 'rtl',
    },
    errorLabel: {
        fontSize: '14px',
        fontWeight: 'bold',
        marginBottom: '8px',
        color: '#333',
    },
    textarea: {
        width: '100%',
        padding: '8px',
        border: '1px solid #ddd',
        borderRadius: '4px',
        fontSize: '13px',
        fontFamily: 'inherit',
        resize: 'vertical',
        direction: 'rtl',
    },
    buttonGroup: {
        display: 'flex',
        gap: '8px',
        marginTop: '12px',
        justifyContent: 'flex-end',
    },
    submitButton: {
        padding: '8px 20px',
        background: '#2196f3',
        color: 'white',
        border: 'none',
        borderRadius: '4px',
        cursor: 'pointer',
        fontSize: '13px',
        fontWeight: 'bold',
        transition: 'background 0.2s',
    },
    disabledButton: {
        background: '#ccc',
        cursor: 'not-allowed',
    },
    cancelButton: {
        padding: '8px 20px',
        background: 'white',
        color: '#666',
        border: '1px solid #ddd',
        borderRadius: '4px',
        cursor: 'pointer',
        fontSize: '13px',
    },
};

export default FeedbackButtons;
