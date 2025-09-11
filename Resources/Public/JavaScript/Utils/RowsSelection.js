define(['jquery'], function($) {
    'use strict';

    const Messenger = {
  deleteItem: function (element) {
    return top.TYPO3.Modal.confirm(
      'Delete',
      'Are you sure to delete this message from ' + element.dataset.name + ' ?',
      top.TYPO3.Severity.warning,
    )
      .on('confirm.button.ok', function () {
        window.location.href = element.dataset.deleteUrl;
        top.TYPO3.Modal.currentModal.trigger('modal-dismiss');
      })
      .on('confirm.button.cancel', function () {
        top.TYPO3.Modal.currentModal.trigger('modal-dismiss');
      });
  },

  selectAll: function () {
    const updateSelectionState = () => {
      const isChecked = $('#record-all').is(':checked');
      const checkboxes = $('.checkboxes .select');
      checkboxes.each(function () {
        $(this).prop('checked', isChecked).trigger('change');
      });
    };
    $('#record-all').on('change', updateSelectionState);
    updateSelectionState();
  },
  getSelectedItems: function () {
    return [...document.querySelectorAll('.select:checked')].map((element) => element.value);
  },
};

    // Initialize when DOM is ready
    $(document).ready(function() {
        // Initialize selectAll functionality
        if (window.Messenger && window.Messenger.selectAll) {
            window.Messenger.selectAll();
        }
        
        // Handle form submission for items per page
        $('#itemsPerPage').on('change', function() {
            $(this).closest('form').submit();
        });
        
        // Initialize other modules when they become available
        const initializeModules = function() {
            if (window.MessengerMassDeletion && !window.MessengerMassDeletion.initialized) {
                window.MessengerMassDeletion.initialize();
                window.MessengerMassDeletion.initialized = true;
            }
            if (window.MessengerDataExport && !window.MessengerDataExport.initialized) {
                window.MessengerDataExport.initialize();
                window.MessengerDataExport.initialized = true;
            }
            if (window.MessengerEnqueueMessages && !window.MessengerEnqueueMessages.initialized) {
                window.MessengerEnqueueMessages.initialize();
                window.MessengerEnqueueMessages.initialized = true;
            }
            if (window.MessengerSendAgain && !window.MessengerSendAgain.initialized) {
                window.MessengerSendAgain.initialize();
                window.MessengerSendAgain.initialized = true;
            }
            if (window.MessengerUpdateRecipient && !window.MessengerUpdateRecipient.initialized) {
                window.MessengerUpdateRecipient.initialize();
                window.MessengerUpdateRecipient.initialized = true;
            }
        };
        
        // Try to initialize immediately
        initializeModules();
        
        // Also try after a short delay
        setTimeout(initializeModules, 100);
        
        // And try again after a longer delay
        setTimeout(initializeModules, 500);
    });

    // Expose globally for compatibility
    window.Messenger = Messenger;

    return Messenger;
});
