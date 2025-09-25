/**
 * Module: Fab/Messenger/DataExport
 */
import Modal from '@typo3/backend/modal.js';
import Notification from '@typo3/backend/notification.js';

class MessengerDataExport {
    constructor() {
        this.initialized = false;
        this.modal = null;
    }

    /**
     * Get export storage URL
     */
    getExportStorageUrl(url, format, module, type, searchTerm = '') {
        const uri = new URL(url, window.location.origin);

        // Get selected items
        const columnsToSend = [...document.querySelectorAll('.select:checked')].map((element) => element.value);

        const params = new URLSearchParams();
        params.append('tx_messenger_user_messenger[matches][uid]', columnsToSend.join(','));
        params.append('format', format);
        params.append('dataType', type);
        params.append('search', searchTerm);
        params.append('module', module);

        uri.search = params.toString();
        return uri.toString();
    }

    /**
     * Initialize data export functionality
     */
    initialize() {
        if (this.initialized) return;

        document.addEventListener('click', (e) => {
            const exportButton = e.target.closest('.btn-export');
            if (exportButton) {
                e.preventDefault();
                this.handleExportClick(exportButton);
            }
        });

        this.initialized = true;
    }

    /**
     * Handle export button click
     */
    handleExportClick(button) {
        const format = button.dataset.format;
        const module = button.dataset.module;
        const type = button.dataset.dataType;
        const searchTerm = button.dataset.searchTerm || '';

        const ajaxUrls = window.TYPO3?.settings?.ajaxUrls || {};
        const confirmUrl = ajaxUrls.messenger_export_data_confirm;
        const exportUrl = ajaxUrls.messenger_export_data_export;

        if (!confirmUrl || !exportUrl) {
            console.error('Export URLs not found in TYPO3 settings');
            this.showError('Export configuration error');
            return;
        }

        const url = this.getExportStorageUrl(confirmUrl, format, module, type, searchTerm);

        this.modal = Modal.advanced({
            type: Modal.types.ajax,
            title: `Export as ${format}`,
            severity: top.TYPO3.Severity.notice,
            content: url,
            buttons: [
                {
                    text: 'Cancel',
                    btnClass: 'btn btn-default',
                    trigger: () => {
                        Modal.dismiss();
                    },
                },
                {
                    text: 'Export data',
                    btnClass: 'btn btn-primary',
                    trigger: () => {
                        this.performExport(exportUrl, format, module, type, searchTerm);
                    },
                },
            ],
        });
    }

    /**
     * Perform the actual export
     */
    async performExport(exportUrl, format, module, type, searchTerm) {
        // Disable buttons during export
        const buttons = this.modal.querySelectorAll('.btn');
        buttons.forEach(btn => btn.disabled = true);

        const finalExportUrl = this.getExportStorageUrl(exportUrl, format, module, type, searchTerm);

        try {
            const response = await fetch(finalExportUrl, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json'
                }
            });

            if (response.ok) {
                const result = await response.json();
                
                if (result.success) {
                    this.showSuccess('Export completed successfully');
                    
                    // Download the file if URL is provided
                    if (result.downloadUrl) {
                        this.downloadFile(result.downloadUrl);
                    }
                    
                    Modal.dismiss();
                } else {
                    this.showError(result.message || 'Export failed');
                }
            } else {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
        } catch (error) {
            console.error('Export error:', error);
            this.showError('Export failed: ' + error.message);
        } finally {
            // Re-enable buttons
            buttons.forEach(btn => btn.disabled = false);
        }
    }

    /**
     * Download file
     */
    downloadFile(url) {
        const link = document.createElement('a');
        link.href = url;
        link.download = '';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }

    /**
     * Show success message
     */
    showSuccess(message) {
        if (Notification) {
            Notification.success('Success', message);
        } else {
            console.log('Success: ' + message);
        }
    }

    /**
     * Show error message
     */
    showError(message) {
        if (Notification) {
            Notification.error('Error', message);
        } else {
            alert('Error: ' + message);
        }
    }
}

// Initialize when DOM is ready
const dataExport = new MessengerDataExport();

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => dataExport.initialize());
} else {
    dataExport.initialize();
}

// Make available globally
window.MessengerDataExport = dataExport;

export default MessengerDataExport;
