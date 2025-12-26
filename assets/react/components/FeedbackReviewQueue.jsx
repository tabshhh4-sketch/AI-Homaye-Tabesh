/**
 * Feedback Review Queue Component
 * 
 * Admin interface for reviewing user feedback
 * PR17: User Feedback System
 */

import React, { useState, useEffect } from 'react';

const FeedbackReviewQueue = () => {
    const [feedbackItems, setFeedbackItems] = useState([]);
    const [statistics, setStatistics] = useState(null);
    const [filters, setFilters] = useState({
        status: 'pending',
        rating: '',
        date_from: '',
        date_to: '',
    });
    const [currentPage, setCurrentPage] = useState(1);
    const [totalPages, setTotalPages] = useState(1);
    const [isLoading, setIsLoading] = useState(false);
    const [selectedFeedback, setSelectedFeedback] = useState(null);

    useEffect(() => {
        loadFeedbackQueue();
        loadStatistics();
    }, [filters, currentPage]);

    const loadFeedbackQueue = async () => {
        setIsLoading(true);
        try {
            const params = new URLSearchParams({
                ...filters,
                page: currentPage,
                per_page: 20,
            });

            const response = await fetch(`/wp-json/homaye-tabesh/v1/feedback/queue?${params}`, {
                headers: {
                    'X-WP-Nonce': window.homaReactData?.nonce || '',
                },
            });

            const result = await response.json();

            if (result.items) {
                setFeedbackItems(result.items);
                setTotalPages(result.total_pages);
            }
        } catch (error) {
            console.error('Error loading feedback queue:', error);
        } finally {
            setIsLoading(false);
        }
    };

    const loadStatistics = async () => {
        try {
            const response = await fetch('/wp-json/homaye-tabesh/v1/feedback/statistics', {
                headers: {
                    'X-WP-Nonce': window.homaReactData?.nonce || '',
                },
            });

            const result = await response.json();

            if (result.success) {
                setStatistics(result.data);
            }
        } catch (error) {
            console.error('Error loading statistics:', error);
        }
    };

    const updateFeedbackStatus = async (feedbackId, status, adminNotes = '') => {
        try {
            const response = await fetch(`/wp-json/homaye-tabesh/v1/feedback/${feedbackId}/status`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': window.homaReactData?.nonce || '',
                },
                body: JSON.stringify({
                    status,
                    admin_notes: adminNotes,
                }),
            });

            const result = await response.json();

            if (result.success) {
                loadFeedbackQueue();
                loadStatistics();
                setSelectedFeedback(null);
                alert('وضعیت بازخورد به‌روزرسانی شد');
            }
        } catch (error) {
            console.error('Error updating feedback status:', error);
            alert('خطا در به‌روزرسانی وضعیت');
        }
    };

    const renderStatistics = () => {
        if (!statistics) return null;

        return (
            <div style={styles.statsContainer}>
                <div style={styles.statCard}>
                    <div style={styles.statValue}>{statistics.total}</div>
                    <div style={styles.statLabel}>کل بازخوردها</div>
                </div>
                <div style={styles.statCard}>
                    <div style={styles.statValue}>{statistics.likes}</div>
                    <div style={styles.statLabel}>لایک</div>
                </div>
                <div style={styles.statCard}>
                    <div style={styles.statValue}>{statistics.dislikes}</div>
                    <div style={styles.statLabel}>دیسلایک</div>
                </div>
                <div style={styles.statCard}>
                    <div style={styles.statValue}>{statistics.satisfaction_rate}%</div>
                    <div style={styles.statLabel}>رضایت کاربران</div>
                </div>
            </div>
        );
    };

    const renderFilters = () => {
        return (
            <div style={styles.filtersContainer}>
                <select
                    value={filters.status}
                    onChange={(e) => setFilters({ ...filters, status: e.target.value })}
                    style={styles.select}
                >
                    <option value="">همه وضعیت‌ها</option>
                    <option value="pending">در انتظار بررسی</option>
                    <option value="reviewed">بررسی شده</option>
                    <option value="resolved">حل شده</option>
                    <option value="dismissed">رد شده</option>
                </select>

                <select
                    value={filters.rating}
                    onChange={(e) => setFilters({ ...filters, rating: e.target.value })}
                    style={styles.select}
                >
                    <option value="">همه نوع‌ها</option>
                    <option value="like">لایک</option>
                    <option value="dislike">دیسلایک</option>
                </select>

                <button
                    onClick={loadFeedbackQueue}
                    style={styles.refreshButton}
                >
                    🔄 بروزرسانی
                </button>
            </div>
        );
    };

    const renderFeedbackItem = (item) => {
        const ratingIcon = item.rating === 'like' ? '👍' : '👎';
        const statusColors = {
            pending: '#ff9800',
            reviewed: '#2196f3',
            resolved: '#4caf50',
            dismissed: '#9e9e9e',
        };

        return (
            <div
                key={item.id}
                style={styles.feedbackItem}
                onClick={() => setSelectedFeedback(item)}
            >
                <div style={styles.feedbackHeader}>
                    <span style={styles.rating}>{ratingIcon}</span>
                    <span style={{
                        ...styles.status,
                        backgroundColor: statusColors[item.status] || '#ccc',
                    }}>
                        {item.status}
                    </span>
                    <span style={styles.date}>{new Date(item.created_at).toLocaleDateString('fa-IR')}</span>
                </div>
                
                <div style={styles.feedbackContent}>
                    <div style={styles.label}>درخواست کاربر:</div>
                    <div style={styles.text}>{item.user_prompt || 'ندارد'}</div>
                </div>

                <div style={styles.feedbackContent}>
                    <div style={styles.label}>پاسخ هما:</div>
                    <div style={styles.text}>{item.response_text}</div>
                </div>

                {item.error_details && (
                    <div style={styles.feedbackContent}>
                        <div style={styles.errorLabel}>توضیحات خطا:</div>
                        <div style={styles.errorText}>{item.error_details}</div>
                    </div>
                )}
            </div>
        );
    };

    const renderDetailModal = () => {
        if (!selectedFeedback) return null;

        return (
            <div style={styles.modalOverlay} onClick={() => setSelectedFeedback(null)}>
                <div style={styles.modal} onClick={(e) => e.stopPropagation()}>
                    <div style={styles.modalHeader}>
                        <h2>جزئیات بازخورد #{selectedFeedback.id}</h2>
                        <button
                            onClick={() => setSelectedFeedback(null)}
                            style={styles.closeButton}
                        >
                            ✕
                        </button>
                    </div>

                    <div style={styles.modalBody}>
                        <div style={styles.detailSection}>
                            <strong>نوع:</strong> {selectedFeedback.rating === 'like' ? '👍 لایک' : '👎 دیسلایک'}
                        </div>

                        <div style={styles.detailSection}>
                            <strong>درخواست کاربر:</strong>
                            <div>{selectedFeedback.user_prompt || 'ندارد'}</div>
                        </div>

                        <div style={styles.detailSection}>
                            <strong>پاسخ هما:</strong>
                            <div>{selectedFeedback.response_text}</div>
                        </div>

                        {selectedFeedback.error_details && (
                            <div style={styles.detailSection}>
                                <strong>توضیحات خطا:</strong>
                                <div style={styles.errorText}>{selectedFeedback.error_details}</div>
                            </div>
                        )}

                        {selectedFeedback.facts_used && Object.keys(selectedFeedback.facts_used).length > 0 && (
                            <div style={styles.detailSection}>
                                <strong>فکت‌های استفاده شده:</strong>
                                <pre style={styles.jsonBlock}>
                                    {JSON.stringify(selectedFeedback.facts_used, null, 2)}
                                </pre>
                            </div>
                        )}

                        {selectedFeedback.admin_notes && (
                            <div style={styles.detailSection}>
                                <strong>یادداشت مدیر:</strong>
                                <div>{selectedFeedback.admin_notes}</div>
                            </div>
                        )}
                    </div>

                    <div style={styles.modalFooter}>
                        <button
                            onClick={() => updateFeedbackStatus(selectedFeedback.id, 'resolved')}
                            style={{...styles.actionButton, backgroundColor: '#4caf50'}}
                        >
                            ✓ حل شده
                        </button>
                        <button
                            onClick={() => updateFeedbackStatus(selectedFeedback.id, 'reviewed')}
                            style={{...styles.actionButton, backgroundColor: '#2196f3'}}
                        >
                            👁 بررسی شده
                        </button>
                        <button
                            onClick={() => updateFeedbackStatus(selectedFeedback.id, 'dismissed')}
                            style={{...styles.actionButton, backgroundColor: '#9e9e9e'}}
                        >
                            ✕ رد شده
                        </button>
                    </div>
                </div>
            </div>
        );
    };

    return (
        <div style={styles.container}>
            <h1 style={styles.title}>صف بررسی بازخوردها</h1>
            
            {renderStatistics()}
            {renderFilters()}

            {isLoading ? (
                <div style={styles.loading}>در حال بارگذاری...</div>
            ) : (
                <>
                    <div style={styles.feedbackList}>
                        {feedbackItems.length === 0 ? (
                            <div style={styles.empty}>هیچ بازخوردی یافت نشد</div>
                        ) : (
                            feedbackItems.map(renderFeedbackItem)
                        )}
                    </div>

                    {totalPages > 1 && (
                        <div style={styles.pagination}>
                            <button
                                onClick={() => setCurrentPage(Math.max(1, currentPage - 1))}
                                disabled={currentPage === 1}
                                style={styles.paginationButton}
                            >
                                قبلی
                            </button>
                            <span style={styles.pageInfo}>
                                صفحه {currentPage} از {totalPages}
                            </span>
                            <button
                                onClick={() => setCurrentPage(Math.min(totalPages, currentPage + 1))}
                                disabled={currentPage === totalPages}
                                style={styles.paginationButton}
                            >
                                بعدی
                            </button>
                        </div>
                    )}
                </>
            )}

            {renderDetailModal()}
        </div>
    );
};

const styles = {
    container: {
        padding: '20px',
        maxWidth: '1200px',
        margin: '0 auto',
        direction: 'rtl',
        fontFamily: 'inherit',
    },
    title: {
        fontSize: '24px',
        fontWeight: 'bold',
        marginBottom: '20px',
        color: '#333',
    },
    statsContainer: {
        display: 'grid',
        gridTemplateColumns: 'repeat(auto-fit, minmax(200px, 1fr))',
        gap: '16px',
        marginBottom: '24px',
    },
    statCard: {
        background: 'white',
        padding: '20px',
        borderRadius: '8px',
        boxShadow: '0 2px 4px rgba(0,0,0,0.1)',
        textAlign: 'center',
    },
    statValue: {
        fontSize: '32px',
        fontWeight: 'bold',
        color: '#2196f3',
        marginBottom: '8px',
    },
    statLabel: {
        fontSize: '14px',
        color: '#666',
    },
    filtersContainer: {
        display: 'flex',
        gap: '12px',
        marginBottom: '20px',
        flexWrap: 'wrap',
    },
    select: {
        padding: '8px 12px',
        border: '1px solid #ddd',
        borderRadius: '4px',
        fontSize: '14px',
    },
    refreshButton: {
        padding: '8px 16px',
        background: '#2196f3',
        color: 'white',
        border: 'none',
        borderRadius: '4px',
        cursor: 'pointer',
        fontSize: '14px',
    },
    feedbackList: {
        display: 'flex',
        flexDirection: 'column',
        gap: '12px',
    },
    feedbackItem: {
        background: 'white',
        padding: '16px',
        borderRadius: '8px',
        boxShadow: '0 2px 4px rgba(0,0,0,0.1)',
        cursor: 'pointer',
        transition: 'box-shadow 0.2s',
        ':hover': {
            boxShadow: '0 4px 8px rgba(0,0,0,0.15)',
        },
    },
    feedbackHeader: {
        display: 'flex',
        alignItems: 'center',
        gap: '12px',
        marginBottom: '12px',
    },
    rating: {
        fontSize: '20px',
    },
    status: {
        padding: '4px 12px',
        borderRadius: '12px',
        color: 'white',
        fontSize: '12px',
        fontWeight: 'bold',
    },
    date: {
        fontSize: '12px',
        color: '#999',
        marginRight: 'auto',
    },
    feedbackContent: {
        marginBottom: '8px',
    },
    label: {
        fontSize: '12px',
        color: '#999',
        marginBottom: '4px',
    },
    text: {
        fontSize: '14px',
        color: '#333',
    },
    errorLabel: {
        fontSize: '12px',
        color: '#f44336',
        marginBottom: '4px',
        fontWeight: 'bold',
    },
    errorText: {
        fontSize: '14px',
        color: '#f44336',
        background: '#ffebee',
        padding: '8px',
        borderRadius: '4px',
    },
    loading: {
        textAlign: 'center',
        padding: '40px',
        color: '#999',
    },
    empty: {
        textAlign: 'center',
        padding: '40px',
        color: '#999',
        background: 'white',
        borderRadius: '8px',
    },
    pagination: {
        display: 'flex',
        justifyContent: 'center',
        alignItems: 'center',
        gap: '16px',
        marginTop: '24px',
    },
    paginationButton: {
        padding: '8px 16px',
        background: 'white',
        border: '1px solid #ddd',
        borderRadius: '4px',
        cursor: 'pointer',
    },
    pageInfo: {
        fontSize: '14px',
        color: '#666',
    },
    modalOverlay: {
        position: 'fixed',
        top: 0,
        left: 0,
        right: 0,
        bottom: 0,
        background: 'rgba(0,0,0,0.5)',
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'center',
        zIndex: 10000,
    },
    modal: {
        background: 'white',
        borderRadius: '8px',
        maxWidth: '800px',
        width: '90%',
        maxHeight: '90vh',
        overflow: 'auto',
    },
    modalHeader: {
        display: 'flex',
        justifyContent: 'space-between',
        alignItems: 'center',
        padding: '20px',
        borderBottom: '1px solid #e0e0e0',
    },
    closeButton: {
        background: 'none',
        border: 'none',
        fontSize: '24px',
        cursor: 'pointer',
        color: '#999',
    },
    modalBody: {
        padding: '20px',
    },
    detailSection: {
        marginBottom: '16px',
    },
    jsonBlock: {
        background: '#f5f5f5',
        padding: '12px',
        borderRadius: '4px',
        fontSize: '12px',
        overflow: 'auto',
        direction: 'ltr',
    },
    modalFooter: {
        display: 'flex',
        gap: '12px',
        padding: '20px',
        borderTop: '1px solid #e0e0e0',
        justifyContent: 'flex-end',
    },
    actionButton: {
        padding: '10px 20px',
        color: 'white',
        border: 'none',
        borderRadius: '4px',
        cursor: 'pointer',
        fontSize: '14px',
        fontWeight: 'bold',
    },
};

export default FeedbackReviewQueue;
