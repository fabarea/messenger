/**
 * Column Selector AJAX functionality
 * Handles dynamic column selection updates without page reload
 */
(function() {
    'use strict';

    class ColumnSelector {
        constructor() {
            this.initialized = false;
        }

        /**
         * Initialize column selector functionality
         */
        initialize() {
            if (this.initialized) return;

            const columnSelectors = document.querySelectorAll('[id^="column-selector-"]');

            columnSelectors.forEach((columnSelector) => {
                if (!columnSelector) return;

                const module = columnSelector.dataset.module;
                const tableName = columnSelector.dataset.tableName;
                const ajaxUrl = columnSelector.dataset.ajaxUrl;
                const checkboxes = columnSelector.querySelectorAll('.column-selector-checkbox');

                if (!ajaxUrl || !module) {
                    console.warn('Column selector missing required data attributes');
                    return;
                }

                // Add event listeners to all checkboxes
                checkboxes.forEach((checkbox) => {
                    checkbox.addEventListener('change', (event) => {
                        this.handleColumnChange(event, {
                            module: module,
                            tableName: tableName,
                            ajaxUrl: ajaxUrl,
                            checkboxes: checkboxes
                        });
                    });
                });
            });

            this.initialized = true;
        }

        /**
         * Handle column checkbox change
         */
        handleColumnChange(event, config) {
            const { module, tableName, ajaxUrl, checkboxes } = config;

            const selectedColumns = Array.from(checkboxes)
                .filter(cb => cb.checked)
                .map(cb => cb.value);

            this.showLoadingState(checkboxes, true);

            this.sendColumnUpdateRequest(ajaxUrl, {
                module: module,
                tableName: tableName,
                selectedColumns: selectedColumns
            }).then((response) => {
                if (response.success) {
                    this.showSuccessMessage('Column selection updated');

                    setTimeout(() => {
                        window.location.reload();
                    }, 500);
                } else {
                    console.error('Column selector update failed:', response.error);
                    this.showErrorMessage(response.error || 'Update failed');
                }
            }).catch((error) => {
                console.error('AJAX request failed:', error);
                this.showErrorMessage('Network error occurred');
            }).finally(() => {
                this.showLoadingState(checkboxes, false);
            });
        }

        /**
         * Send AJAX request to update columns
         */
        sendColumnUpdateRequest(ajaxUrl, data) {
            return new Promise((resolve, reject) => {
                const xhr = new XMLHttpRequest();
                xhr.open('POST', ajaxUrl, true);
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

                xhr.onreadystatechange = function() {
                    if (xhr.readyState === 4) {
                        if (xhr.status === 200) {
                            try {
                                const response = JSON.parse(xhr.responseText);
                                resolve(response);
                            } catch (e) {
                                reject(new Error('Error parsing response: ' + e.message));
                            }
                        } else {
                            reject(new Error('HTTP error: ' + xhr.status));
                        }
                    }
                };

                xhr.onerror = function() {
                    reject(new Error('Network error'));
                };

                // Prepare form data
                const formData = new URLSearchParams();
                formData.append('module', data.module);
                if (data.tableName) {
                    formData.append('tableName', data.tableName);
                }

                data.selectedColumns.forEach((column) => {
                    formData.append('selectedColumns[]', column);
                });

                xhr.send(formData.toString());
            });
        }

        /**
         * Show/hide loading state on checkboxes
         */
        showLoadingState(checkboxes, isLoading) {
            checkboxes.forEach((checkbox) => {
                checkbox.disabled = isLoading;

                if (isLoading) {
                    checkbox.classList.add('loading');
                } else {
                    checkbox.classList.remove('loading');
                }
            });
        }

        /**
         * Show success message (using TYPO3 notification system)
         */
        showSuccessMessage(message) {
            const Notification = window.TYPO3?.Notification || top.TYPO3?.Notification;
            if (Notification) {
                Notification.success('Success', message);
            } else {
                console.log('Success: ' + message);
            }
        }

        /**
         * Show error message (using TYPO3 notification system)
         */
        showErrorMessage(message) {
            const Notification = window.TYPO3?.Notification || top.TYPO3?.Notification;
            if (Notification) {
                Notification.error('Error', message);
            } else {
                alert('Error: ' + message);
            }
        }
    }

    // Initialize when DOM is ready
    const columnSelector = new ColumnSelector();

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => columnSelector.initialize());
    } else {
        columnSelector.initialize();
    }

    // Listen for reinit events
    document.addEventListener('typo3:columnselector:reinit', () => columnSelector.initialize());

    // Make available globally if needed
    window.MessengerColumnSelector = columnSelector;

})();
