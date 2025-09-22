/**
 * Column Selector AJAX functionality
 * Handles dynamic column selection updates without page reload
 */
(function() {
    'use strict';

    /**
     * Initialize column selector functionality
     */
    function initializeColumnSelector() {
        const columnSelectors = document.querySelectorAll('[id^="column-selector-"]');

        columnSelectors.forEach(function(columnSelector) {
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
            checkboxes.forEach(function(checkbox) {
                checkbox.addEventListener('change', function(event) {
                    handleColumnChange(event, {
                        module: module,
                        tableName: tableName,
                        ajaxUrl: ajaxUrl,
                        checkboxes: checkboxes
                    });
                });
            });
        });
    }

    /**
     * Handle column checkbox change
     */
    function handleColumnChange(event, config) {
        const { module, tableName, ajaxUrl, checkboxes } = config;

        const selectedColumns = Array.from(checkboxes)
            .filter(cb => cb.checked)
            .map(cb => cb.value);

        showLoadingState(checkboxes, true);

        sendColumnUpdateRequest(ajaxUrl, {
            module: module,
            tableName: tableName,
            selectedColumns: selectedColumns
        }).then(function(response) {
            if (response.success) {
                showSuccessMessage('Column selection updated');

                setTimeout(function() {
                    window.location.reload();
                }, 500);
            } else {
                console.error('Column selector update failed:', response.error);
                showErrorMessage(response.error || 'Update failed');
            }
        }).catch(function(error) {
            console.error('AJAX request failed:', error);
            showErrorMessage('Network error occurred');
        }).finally(function() {
            showLoadingState(checkboxes, false);
        });
    }

    /**
     * Send AJAX request to update columns
     */
    function sendColumnUpdateRequest(ajaxUrl, data) {
        return new Promise(function(resolve, reject) {
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

            data.selectedColumns.forEach(function(column) {
                formData.append('selectedColumns[]', column);
            });

            xhr.send(formData.toString());
        });
    }

    /**
     * Show/hide loading state on checkboxes
     */
    function showLoadingState(checkboxes, isLoading) {
        checkboxes.forEach(function(checkbox) {
            checkbox.disabled = isLoading;

            if (isLoading) {
                checkbox.classList.add('loading');
            } else {
                checkbox.classList.remove('loading');
            }
        });
    }

    /**
     * Show success message (using TYPO3 notification system if available)
     */
    function showSuccessMessage(message) {
        if (typeof TYPO3 !== 'undefined' && TYPO3.Notification) {
            TYPO3.Notification.success('Success', message);
        } else {
            console.log('Success: ' + message);
        }
    }

    /**
     * Show error message (using TYPO3 notification system if available)
     */
    function showErrorMessage(message) {
        if (typeof TYPO3 !== 'undefined' && TYPO3.Notification) {
            TYPO3.Notification.error('Error', message);
        } else {
            alert('Error: ' + message);
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initializeColumnSelector);
    } else {
        initializeColumnSelector();
    }

    document.addEventListener('typo3:columnselector:reinit', initializeColumnSelector);

})();
