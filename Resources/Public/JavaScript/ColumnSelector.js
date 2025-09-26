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
                    return;
                }

                this.restoreSelectionsFromLocalStorage(module, tableName, checkboxes);

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
         * NOUVELLE MÉTHODE : Restaurer les sélections depuis localStorage
         */
        restoreSelectionsFromLocalStorage(module, tableName, checkboxes) {
            try {
                const storageKey = 'messenger_columns_' + module + '_' + (tableName || 'default');
                const savedData = localStorage.getItem(storageKey);

                if (savedData) {
                    const parsedData = JSON.parse(savedData);
                    const selectedColumns = parsedData.selectedColumns || [];

                    checkboxes.forEach((checkbox) => {
                        checkbox.checked = selectedColumns.includes(checkbox.value);
                    });
                }
            } catch (error) {
                console.warn('Error restoring selections from localStorage:', error);
            }
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
                        const currentUrl = new URL(window.location.href);

                        currentUrl.searchParams.delete('selectedColumns[]');

                        selectedColumns.forEach(column => {
                            currentUrl.searchParams.append('selectedColumns[]', column);
                        });

                        window.location.href = currentUrl.toString();
                    }, 200);
                } else {
                    this.showErrorMessage(response.error || 'Update failed');
                }
            }).catch((error) => {
                this.showErrorMessage('Save failed');
            }).finally(() => {
                this.showLoadingState(checkboxes, false);
            });
        }

        /**
         * Send AJAX request to update columns - SIMPLIFIED VERSION
         */
        sendColumnUpdateRequest(ajaxUrl, data) {
            return new Promise((resolve, reject) => {
                try {
                    // Créer une clé unique pour ce module/table
                    const storageKey = 'messenger_columns_' + data.module + '_' + (data.tableName || 'default');

                    localStorage.setItem(storageKey, JSON.stringify({
                        selectedColumns: data.selectedColumns,
                        timestamp: Date.now()
                    }));


                    resolve({
                        success: true,
                        message: 'Column selection updated (localStorage)'
                    });

                } catch (error) {
                    reject(new Error('Failed to save column selection'));
                }
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

    document.addEventListener('typo3:columnselector:reinit', () => columnSelector.initialize());

    window.MessengerColumnSelector = columnSelector;

})();
