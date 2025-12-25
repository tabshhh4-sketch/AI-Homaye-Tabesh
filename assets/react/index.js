import React from 'react';
import ReactDOM from 'react-dom/client';
import HomaSidebar from './components/HomaSidebar';
import './styles/parallel-ui.css';

/**
 * Initialize Homa Parallel UI
 * This function is called by WordPress when the page loads
 */
window.initHomaParallelUI = function() {
    // Validate React is available
    if (typeof window.React === 'undefined' || typeof window.ReactDOM === 'undefined') {
        console.error('[Homa] React or ReactDOM not loaded. Please check CDN availability.');
        // Show user-friendly error
        const container = document.getElementById('homa-sidebar-view');
        if (container) {
            container.innerHTML = `
                <div style="padding: 20px; text-align: center; color: #721c24; background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 4px; margin: 20px;">
                    <h3>خطا در بارگذاری هما</h3>
                    <p>لطفاً اتصال اینترنت خود را بررسی کنید و صفحه را رفرش کنید.</p>
                </div>
            `;
        }
        return;
    }

    // Check if container exists
    const container = document.getElementById('homa-sidebar-view');
    if (!container) {
        console.error('[Homa] Sidebar container not found');
        return;
    }

    try {
        // Create React root and render sidebar
        const root = window.ReactDOM.createRoot(container);
        root.render(window.React.createElement(HomaSidebar));

        // Initialize the orchestrator
        if (window.HomaOrchestrator) {
            window.HomaOrchestrator.init();
        } else {
            console.warn('[Homa] Orchestrator not found');
        }
    } catch (error) {
        console.error('[Homa] Initialization error:', error);
        container.innerHTML = `
            <div style="padding: 20px; text-align: center; color: #721c24; background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 4px; margin: 20px;">
                <h3>خطا در بارگذاری هما</h3>
                <p>لطفاً صفحه را رفرش کنید. اگر مشکل ادامه داشت، با پشتیبانی تماس بگیرید.</p>
            </div>
        `;
    }
};

// Auto-initialize when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        if (window.initHomaParallelUI) {
            window.initHomaParallelUI();
        }
    });
} else {
    if (window.initHomaParallelUI) {
        window.initHomaParallelUI();
    }
}
