define(['jquery', 'TYPO3/CMS/Backend/Modal'], function($, Modal) {
    'use strict';

    const Messenger = {
  deleteItem: function (element) {
    // Use TYPO3 Modal.advanced with HTML content
    const modal = Modal.advanced({
      type: Modal.types.default,
      title: 'Delete Message',
      severity: top.TYPO3.Severity.warning,
      content: $('<div>').html('Are you sure you want to delete this message from <strong>' + element.dataset.name + '</strong>?<br><br><small class="text-muted">This action cannot be undone.</small>'),
      buttons: [
        {
          text: 'Cancel',
          btnClass: 'btn btn-default',
          trigger: function () {
            Modal.dismiss();
          },
        },
        {
          text: 'Delete',
          btnClass: 'btn btn-danger',
          trigger: function () {
            Modal.dismiss();
            // Small delay to ensure modal is closed before redirect
            setTimeout(function() {
              window.location.href = element.dataset.deleteUrl;
            }, 100);
          },
        },
      ],
    });
    
    return modal;
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
        
        // Handle individual delete buttons
        $(document).on('click', '[data-action="delete-item"]', function(e) {
            e.preventDefault();
            Messenger.deleteItem(this);
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
