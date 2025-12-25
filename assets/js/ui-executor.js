/**
 * Divi UI Executor
 * Executes Homa's UI commands on the frontend
 *
 * @package HomayeTabesh
 * @since 1.0.0
 */

(function ($) {
    'use strict';

    /**
     * Homa UI Executor Class
     */
    class HomaUIExecutor {
        constructor() {
            this.activeHighlights = [];
            this.activeTooltips = [];
            this.modalContainer = null;
            this.init();
        }

        /**
         * Initialize the executor
         */
        init() {
            console.log('Homa UI Executor initialized');
            this.createModalContainer();
            this.setupStyles();
        }

        /**
         * Create modal container
         */
        createModalContainer() {
            if (!this.modalContainer) {
                this.modalContainer = $('<div>')
                    .attr('id', 'homa-modal-container')
                    .addClass('homa-modal-container')
                    .appendTo('body');
            }
        }

        /**
         * Setup custom styles
         */
        setupStyles() {
            if ($('#homa-ui-styles').length) {
                return;
            }

            const styles = `
                <style id="homa-ui-styles">
                    .homa-highlight {
                        position: relative;
                        animation: homa-pulse 2s infinite;
                        box-shadow: 0 0 20px rgba(74, 144, 226, 0.6) !important;
                        border: 2px solid #4a90e2 !important;
                        transition: all 0.3s ease;
                    }

                    @keyframes homa-pulse {
                        0%, 100% { box-shadow: 0 0 20px rgba(74, 144, 226, 0.6); }
                        50% { box-shadow: 0 0 30px rgba(74, 144, 226, 0.9); }
                    }

                    .homa-tooltip {
                        position: absolute;
                        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                        color: white;
                        padding: 12px 16px;
                        border-radius: 8px;
                        font-size: 14px;
                        line-height: 1.5;
                        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
                        z-index: 999999;
                        max-width: 300px;
                        animation: homa-tooltip-fade-in 0.3s ease;
                    }

                    .homa-tooltip::before {
                        content: '';
                        position: absolute;
                        bottom: -8px;
                        left: 20px;
                        width: 0;
                        height: 0;
                        border-left: 8px solid transparent;
                        border-right: 8px solid transparent;
                        border-top: 8px solid #764ba2;
                    }

                    .homa-tooltip-close {
                        position: absolute;
                        top: 5px;
                        right: 8px;
                        background: transparent;
                        border: none;
                        color: white;
                        font-size: 18px;
                        cursor: pointer;
                        opacity: 0.8;
                        transition: opacity 0.2s;
                    }

                    .homa-tooltip-close:hover {
                        opacity: 1;
                    }

                    @keyframes homa-tooltip-fade-in {
                        from { opacity: 0; transform: translateY(-10px); }
                        to { opacity: 1; transform: translateY(0); }
                    }

                    .homa-modal-container {
                        position: fixed;
                        top: 0;
                        left: 0;
                        width: 100%;
                        height: 100%;
                        background: rgba(0, 0, 0, 0.7);
                        z-index: 999998;
                        display: none;
                        align-items: center;
                        justify-content: center;
                        animation: homa-modal-fade-in 0.3s ease;
                    }

                    .homa-modal-container.active {
                        display: flex;
                    }

                    @keyframes homa-modal-fade-in {
                        from { opacity: 0; }
                        to { opacity: 1; }
                    }

                    .homa-modal {
                        background: white;
                        border-radius: 12px;
                        max-width: 600px;
                        width: 90%;
                        max-height: 80vh;
                        overflow-y: auto;
                        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.5);
                        animation: homa-modal-slide-up 0.3s ease;
                    }

                    @keyframes homa-modal-slide-up {
                        from { transform: translateY(50px); opacity: 0; }
                        to { transform: translateY(0); opacity: 1; }
                    }

                    .homa-modal-header {
                        padding: 20px;
                        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                        color: white;
                        border-radius: 12px 12px 0 0;
                        display: flex;
                        justify-content: space-between;
                        align-items: center;
                    }

                    .homa-modal-title {
                        margin: 0;
                        font-size: 20px;
                        font-weight: 600;
                    }

                    .homa-modal-close {
                        background: transparent;
                        border: none;
                        color: white;
                        font-size: 24px;
                        cursor: pointer;
                        opacity: 0.8;
                        transition: opacity 0.2s;
                    }

                    .homa-modal-close:hover {
                        opacity: 1;
                    }

                    .homa-modal-body {
                        padding: 24px;
                        line-height: 1.6;
                    }

                    .homa-discount-badge {
                        display: inline-block;
                        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
                        color: white;
                        padding: 8px 16px;
                        border-radius: 20px;
                        font-weight: 600;
                        animation: homa-bounce 1s infinite;
                    }

                    @keyframes homa-bounce {
                        0%, 100% { transform: translateY(0); }
                        50% { transform: translateY(-5px); }
                    }
                </style>
            `;
            $('head').append(styles);
        }

        /**
         * Execute action from Homa
         */
        executeAction(action) {
            if (!action || !action.type) {
                console.warn('Invalid action:', action);
                return;
            }

            console.log('Executing action:', action.type, action);

            switch (action.type) {
                case 'highlight_element':
                    this.highlightElement(action);
                    break;
                case 'show_tooltip':
                    this.showTooltip(action);
                    break;
                case 'scroll_to':
                    this.scrollTo(action);
                    break;
                case 'open_modal':
                    this.openModal(action);
                    break;
                case 'update_calculator':
                    this.updateCalculator(action);
                    break;
                case 'suggest_product':
                    this.suggestProduct(action);
                    break;
                case 'show_discount':
                    this.showDiscount(action);
                    break;
                case 'change_css':
                    this.changeCss(action);
                    break;
                case 'redirect':
                    this.redirect(action);
                    break;
                default:
                    console.warn('Unknown action type:', action.type);
            }
        }

        /**
         * Highlight an element
         */
        highlightElement(action) {
            const $element = $(action.target);
            if (!$element.length) {
                console.warn('Element not found:', action.target);
                return;
            }

            $element.addClass('homa-highlight');
            this.activeHighlights.push($element);

            // Auto-remove after 5 seconds
            setTimeout(() => {
                $element.removeClass('homa-highlight');
                const index = this.activeHighlights.indexOf($element);
                if (index > -1) {
                    this.activeHighlights.splice(index, 1);
                }
            }, 5000);
        }

        /**
         * Show tooltip
         */
        showTooltip(action) {
            const $element = $(action.target);
            if (!$element.length) {
                console.warn('Element not found:', action.target);
                return;
            }

            const message = action.message || action.data?.message || 'توجه کنید';
            const offset = $element.offset();
            const width = $element.outerWidth();

            const $tooltip = $('<div>')
                .addClass('homa-tooltip')
                .html(message + '<button class="homa-tooltip-close">&times;</button>')
                .css({
                    top: offset.top - 80,
                    left: offset.left
                })
                .appendTo('body');

            this.activeTooltips.push($tooltip);

            // Close button
            $tooltip.find('.homa-tooltip-close').on('click', () => {
                $tooltip.remove();
                const index = this.activeTooltips.indexOf($tooltip);
                if (index > -1) {
                    this.activeTooltips.splice(index, 1);
                }
            });

            // Auto-remove after 10 seconds
            setTimeout(() => {
                $tooltip.remove();
                const index = this.activeTooltips.indexOf($tooltip);
                if (index > -1) {
                    this.activeTooltips.splice(index, 1);
                }
            }, 10000);
        }

        /**
         * Scroll to element
         */
        scrollTo(action) {
            const $element = $(action.target);
            if (!$element.length) {
                console.warn('Element not found:', action.target);
                return;
            }

            $('html, body').animate({
                scrollTop: $element.offset().top - 100
            }, 800, () => {
                this.highlightElement(action);
            });
        }

        /**
         * Open modal
         */
        openModal(action) {
            const title = action.data?.title || 'پیام هما';
            const content = action.data?.content || '';

            const $modal = $('<div>').addClass('homa-modal').html(`
                <div class="homa-modal-header">
                    <h3 class="homa-modal-title">${title}</h3>
                    <button class="homa-modal-close">&times;</button>
                </div>
                <div class="homa-modal-body">${content}</div>
            `);

            this.modalContainer.html($modal).addClass('active');

            // Close handlers
            $modal.find('.homa-modal-close').on('click', () => {
                this.closeModal();
            });

            this.modalContainer.on('click', (e) => {
                if ($(e.target).hasClass('homa-modal-container')) {
                    this.closeModal();
                }
            });
        }

        /**
         * Close modal
         */
        closeModal() {
            this.modalContainer.removeClass('active');
        }

        /**
         * Update calculator
         */
        updateCalculator(action) {
            const field = action.field || action.target;
            const value = action.value || action.data?.value;

            const $field = $(`#${field}, [name="${field}"], .${field}`);
            if ($field.length) {
                $field.val(value).trigger('change');
                this.highlightElement({ target: `#${field}` });
            }
        }

        /**
         * Suggest product
         */
        suggestProduct(action) {
            const productId = action.data?.product_id;
            const reason = action.data?.reason || 'این محصول برای شما مناسب است';

            if (productId) {
                this.openModal({
                    data: {
                        title: 'پیشنهاد ویژه هما',
                        content: `
                            <p>${reason}</p>
                            <p><a href="?p=${productId}" class="button">مشاهده محصول</a></p>
                        `
                    }
                });
            }
        }

        /**
         * Show discount
         */
        showDiscount(action) {
            const code = action.data?.discount_code || action.discount_code;
            const message = action.data?.message || action.message;

            const discountHtml = `
                <p>${message}</p>
                <p>کد تخفیف: <span class="homa-discount-badge">${code}</span></p>
                <p><small>این کد را در صفحه سبد خرید وارد کنید</small></p>
            `;

            this.openModal({
                data: {
                    title: '🎉 تخفیف ویژه برای شما',
                    content: discountHtml
                }
            });
        }

        /**
         * Change CSS
         */
        changeCss(action) {
            const $element = $(action.target);
            if (!$element.length) {
                console.warn('Element not found:', action.target);
                return;
            }

            const property = action.data?.property || action.property;
            const value = action.data?.value || action.value;

            if (property && value) {
                $element.css(property, value);
            }
        }

        /**
         * Redirect to URL
         */
        redirect(action) {
            const url = action.data?.url || action.url;
            const delay = action.data?.delay || action.delay || 0;

            if (url) {
                setTimeout(() => {
                    window.location.href = url;
                }, delay);
            }
        }

        /**
         * Clear all active UI elements
         */
        clearAll() {
            this.activeHighlights.forEach($el => {
                $el.removeClass('homa-highlight');
            });
            this.activeHighlights = [];

            this.activeTooltips.forEach($tooltip => {
                $tooltip.remove();
            });
            this.activeTooltips = [];

            this.closeModal();
        }
    }

    // Initialize when document is ready
    $(document).ready(function () {
        if (typeof window.HomaUIExecutor === 'undefined') {
            window.HomaUIExecutor = new HomaUIExecutor();
            console.log('Homa UI Executor is ready');
        }
    });

})(jQuery);
