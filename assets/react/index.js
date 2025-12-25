import React from 'react';
import ReactDOM from 'react-dom/client';
import HomaSidebar from './components/HomaSidebar';
import './styles/parallel-ui.css';

/**
 * Initialize Homa Parallel UI
 * This function is called by WordPress when the page loads
 */
window.initHomaParallelUI = function() {
    // Check if container exists
    const container = document.getElementById('homa-sidebar-view');
    if (!container) {
        console.error('Homa sidebar container not found');
        return;
    }

    // Create React root and render sidebar
    const root = ReactDOM.createRoot(container);
    root.render(<HomaSidebar />);

    // Initialize the orchestrator
    if (window.HomaOrchestrator) {
        window.HomaOrchestrator.init();
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
