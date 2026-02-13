/**
 * Module: Fab/Messenger/EnqueueMessages
 */
import Modal from '@typo3/backend/modal.js';
import Notification from '@typo3/backend/notification.js';

const MessengerEnqueueMessages = {
    modal: null,
    observerInitialized: false,
    clickListenerInitialized: false,

    /**
     * Get edit recipient URL
     *
     * @param {string} url
     * @param {Array} data
     * @param {string} searchTerm
     * @return {string}
     * @private
     */
    getEditRecipientUrl: function (url, data = [], searchTerm = '') {
        if (!url) {
            console.error('URL is undefined or null');
            return '';
        }

        let absoluteUrl;
        if (url.startsWith('/')) {
            absoluteUrl = window.location.origin + url;
        } else {
            absoluteUrl = url;
        }

        // Get selected items
        const columnsToSend = [...document.querySelectorAll('.select:checked')].map((element) => element.value);

        const urlObj = new URL(absoluteUrl);
        const params = urlObj.searchParams;

        params.set('tx_messenger_user_messengerm5[matches][uid]', columnsToSend.join(','));
        params.set('data', data);
        params.set('search', searchTerm);

        return urlObj.toString();
    },

    initialize: function () {
        if (!this.observerInitialized) {
            this.setupModalObserver();
            this.observerInitialized = true;
        }
        if (!this.clickListenerInitialized) {
            this.initializeEnqueueMessages();
            this.clickListenerInitialized = true;
        }
    },

    /**
     * Initialize checkbox handlers in the modal
     */
    initializeModalCheckboxes: function () {
        // Find checkboxes in all possible contexts
        const docs = [document];
        if (window.parent && window.parent.document !== document) {
            docs.push(window.parent.document);
        }

        docs.forEach(doc => {
            // Handle "Replace message body" checkbox
            const hasBodyTextCheckbox = doc.getElementById('has-body-text');
            if (hasBodyTextCheckbox && !hasBodyTextCheckbox.dataset.listenerAttached) {
                hasBodyTextCheckbox.dataset.listenerAttached = 'true';
                hasBodyTextCheckbox.addEventListener('change', function(e) {
                    e.stopPropagation();
                    const container = doc.getElementById('message-body-container');
                    if (container) {
                        container.style.display = this.checked ? 'block' : 'none';
                    }
                });
            }

            // Handle "Send test" checkbox
            const hasBodyTestCheckbox = doc.getElementById('has-body-test');
            if (hasBodyTestCheckbox && !hasBodyTestCheckbox.dataset.listenerAttached) {
                hasBodyTestCheckbox.dataset.listenerAttached = 'true';
                hasBodyTestCheckbox.addEventListener('change', function(e) {
                    e.stopPropagation();
                    const recipientTest = doc.getElementById('recipient-test');
                    if (recipientTest) {
                        recipientTest.style.display = this.checked ? 'block' : 'none';
                        this.value = this.checked ? '1' : '0';
                    }
                });
            }
        });
    },

    /**
     * Setup MutationObserver to detect when modal content is loaded
     */
    setupModalObserver: function() {
        const self = this;

        const observer = new MutationObserver(function(mutations) {
            for (const mutation of mutations) {
                if (mutation.addedNodes.length) {
                    for (const node of mutation.addedNodes) {
                        if (node.nodeType === Node.ELEMENT_NODE) {
                            if (node.querySelector && (node.querySelector('#has-body-text') || node.querySelector('#has-body-test'))) {
                                setTimeout(() => self.initializeModalCheckboxes(), 50);
                                return;
                            }
                        }
                    }
                }
            }
        });

        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    },

    initializeEnqueueMessages: function () {
        const self = this;

        document.addEventListener('click', function (e) {
            // Ignore clicks on checkboxes and form elements inside modal
            if (e.target.closest('.modal') && e.target.matches('input, label, select, textarea')) {
                return;
            }

            const button = e.target.closest('.btn-send-message');
            if (!button) {
                return;
            }

            e.preventDefault();
            e.stopPropagation();

            const searchTerm = button.dataset.searchTerm || '';

            if (!window.TYPO3 || !window.TYPO3.settings || !window.TYPO3.settings.ajaxUrls) {
                Notification.error('Error', 'TYPO3 configuration not loaded. Please refresh the page.');
                return;
            }

            const displayModalUrl = TYPO3.settings.ajaxUrls.newsletter_display_send_message_modal;
            const sendTestUrl = TYPO3.settings.ajaxUrls.newsletter_send_test_messages;
            const enqueueUrl = TYPO3.settings.ajaxUrls.newsletter_enqueue_messages;

            if (!displayModalUrl || !sendTestUrl || !enqueueUrl) {
                console.error('Enqueue messages URLs not found in TYPO3 settings');
                Notification.error('Error', 'Enqueue messages configuration error');
                return;
            }

            const url = self.getEditRecipientUrl(displayModalUrl, [], searchTerm);

            self.modal = Modal.advanced({
                type: Modal.types.ajax,
                title: 'Enqueue messages',
                severity: top.TYPO3.Severity.notice,
                content: url,
                staticBackdrop: false,
                buttons: [
                    {
                        text: 'Cancel',
                        btnClass: 'btn btn-default',
                        trigger: function () {
                            Modal.dismiss();
                        },
                    },
                    {
                        text: 'Send',
                        btnClass: 'btn btn-primary',
                        trigger: function () {
                            self.handleSend(sendTestUrl, enqueueUrl, searchTerm);
                        },
                    },
                ],
            });

            // Initialize checkboxes after modal content is loaded
            setTimeout(() => self.initializeModalCheckboxes(), 300);
        });
    },

    handleSend: function(sendTestUrl, enqueueUrl, searchTerm) {
        const self = this;

        const modalElement = this.modal;
        if (modalElement) {
            const modalContent = modalElement.find ? modalElement.find('.modal-content')[0] : modalElement;
            if (modalContent) {
                const buttons = modalContent.querySelectorAll('.btn');
                buttons.forEach(btn => btn.setAttribute('disabled', 'disabled'));
            }
        }

        // Try to find form in parent document first, then current document
        let form = null;
        if (window.parent && window.parent.document !== document) {
            form = window.parent.document.querySelector('#form-bulk-send');
        }
        if (!form) {
            form = document.querySelector('#form-bulk-send');
        }

        if (!form) {
            Notification.error('Error', 'Send form not found');
            return;
        }

        // Check if test mode
        let isTestMessage = false;
        let testCheckbox = null;
        if (window.parent && window.parent.document !== document) {
            testCheckbox = window.parent.document.querySelector('#has-body-test');
        }
        if (!testCheckbox) {
            testCheckbox = document.querySelector('#has-body-test');
        }
        if (testCheckbox) {
            isTestMessage = testCheckbox.value === '1';
        }

        const updateUrl = isTestMessage ? sendTestUrl : enqueueUrl;
        const finalUpdateUrl = self.getEditRecipientUrl(updateUrl, [], searchTerm);
        const formData = new FormData(form);

        fetch(finalUpdateUrl, {
            method: 'POST',
            headers: {
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(async (response) => {
            if (response.ok) {
                const result = await response.text();
                if (result) {
                    Notification.success('Success', result);
                    Modal.dismiss();
                } else {
                    Notification.error('Error', 'No response from server');
                }
            } else {
                throw new Error('HTTP ' + response.status + ': ' + response.statusText);
            }
        })
        .catch((error) => {
            console.error('Send message error:', error);
            Notification.error('Error', 'Send message failed: ' + error.message);

            if (modalElement) {
                const modalContent = modalElement.find ? modalElement.find('.modal-content')[0] : modalElement;
                if (modalContent) {
                    const buttons = modalContent.querySelectorAll('.btn');
                    buttons.forEach(btn => btn.removeAttribute('disabled'));
                }
            }
        });
    }
};

// Expose globally for compatibility
window.MessengerEnqueueMessages = MessengerEnqueueMessages;

// Initialize once when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        MessengerEnqueueMessages.initialize();
    });
} else {
    MessengerEnqueueMessages.initialize();
}

export default MessengerEnqueueMessages;
