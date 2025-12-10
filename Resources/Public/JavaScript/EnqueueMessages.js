/**
 * Module: Fab/Messenger/EnqueueMessages
 */
import Modal from '@typo3/backend/modal.js';
import Notification from '@typo3/backend/notification.js';
import AjaxRequest from "@typo3/core/ajax/ajax-request.js";

const MessengerEnqueueMessages = {
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

        const finalUrl = urlObj.toString();

        return finalUrl;
    },

    initialize: function () {
        this.initializeEnqueueMessages();
    },

    /**
     * Initialize enqueue messages functionality
     * @return void
     */
    /**
     * Initialize checkbox handlers in the modal
     */
    initializeModalCheckboxes: function () {
        console.log('Initializing modal checkboxes...');

        // Try different document contexts
        const contexts = [
            document,
            window.parent ? window.parent.document : null,
            window.top ? window.top.document : null
        ].filter(Boolean);

        contexts.forEach(doc => {
            // Handle "Replace message body" checkbox
            const hasBodyTextCheckbox = doc.getElementById('has-body-text');
            if (hasBodyTextCheckbox && !hasBodyTextCheckbox.dataset.listenerAttached) {
                console.log('Found and initializing has-body-text checkbox');
                hasBodyTextCheckbox.dataset.listenerAttached = 'true';

                hasBodyTextCheckbox.addEventListener('change', function() {
                    console.log('has-body-text changed to:', this.checked);
                    const container = doc.getElementById('message-body-container');
                    if (container) {
                        container.style.display = this.checked ? 'block' : 'none';
                        console.log('Toggled message-body-container to:', container.style.display);
                    } else {
                        console.error('message-body-container not found in document');
                    }
                });
            }

            // Handle "Send test" checkbox
            const hasBodyTestCheckbox = doc.getElementById('has-body-test');
            if (hasBodyTestCheckbox && !hasBodyTestCheckbox.dataset.listenerAttached) {
                console.log('Found and initializing has-body-test checkbox');
                hasBodyTestCheckbox.dataset.listenerAttached = 'true';

                hasBodyTestCheckbox.addEventListener('change', function() {
                    console.log('has-body-test changed to:', this.checked);
                    const recipientTest = doc.getElementById('recipient-test');
                    if (recipientTest) {
                        recipientTest.style.display = this.checked ? 'block' : 'none';
                        this.value = this.checked ? '1' : '0';
                        console.log('Toggled recipient-test to:', recipientTest.style.display);
                    } else {
                        console.error('recipient-test not found in document');
                    }
                });
            }
        });
    },

    initializeEnqueueMessages: function () {
        document.addEventListener('click', function (e) {
            const button = e.target.closest('.btn-send-message');
            if (!button) {
                return;
            }

            e.preventDefault();
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

            const url = MessengerEnqueueMessages.getEditRecipientUrl(displayModalUrl, [], searchTerm);

            MessengerEnqueueMessages.modal = Modal.advanced({
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
                            const modalElement = MessengerEnqueueMessages.modal.find ? MessengerEnqueueMessages.modal.find('.modal-content')[0] : MessengerEnqueueMessages.modal;
                            if (modalElement) {
                                const buttons = modalElement.querySelectorAll('.btn');
                                buttons.forEach(btn => btn.setAttribute('disabled', 'disabled'));
                            } else {
                                console.log('Modal element not found for button disabling');
                            }

                            const form = window.parent.document.querySelector('#form-bulk-send');
                            if (!form) {
                                Notification.error('Error', 'Send form not found');
                                return;
                            }

                            const isTestMessage = window.parent.document.querySelector('#has-body-test')?.value === '1';
                            const updateUrl = isTestMessage ? sendTestUrl : enqueueUrl;
                            const finalUpdateUrl = MessengerEnqueueMessages.getEditRecipientUrl(updateUrl, [], searchTerm);
                            const formData = new FormData(form);

                            // Try POST method first
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
                                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                                }
                            })
                            .catch((error) => {
                                console.error('Send message error:', error);
                                Notification.error('Error', 'Send message failed: ' + error.message);

                                if (modalElement) {
                                    const buttons = modalElement.querySelectorAll('.btn');
                                    buttons.forEach(btn => btn.removeAttribute('disabled'));
                                }
                            });
                        },
                    },
                ],
            });

            // Initialize checkboxes after modal is shown
            // Listen for when the modal HTML is loaded
            const modalElement = MessengerEnqueueMessages.modal;
            if (modalElement) {
                // For TYPO3 v11+, listen for modal shown event
                const checkModalContent = () => {
                    setTimeout(() => {
                        MessengerEnqueueMessages.initializeModalCheckboxes();
                    }, 500);
                };

                // Try multiple approaches
                if (modalElement.on) {
                    modalElement.on('shown.bs.modal', checkModalContent);
                } else if (modalElement.addEventListener) {
                    modalElement.addEventListener('shown.bs.modal', checkModalContent);
                }

                // Also try immediately in case modal is already loaded
                checkModalContent();
            }
        });
    },
};

// Expose globally for compatibility
window.MessengerEnqueueMessages = MessengerEnqueueMessages;
window.MessengerEnqueueMessages.initialized = false;

// Initialize immediately if DOM is already loaded, otherwise wait for DOMContentLoaded
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        window.MessengerEnqueueMessages.initialize();
    });
} else {
    window.MessengerEnqueueMessages.initialize();
}

export default MessengerEnqueueMessages;
