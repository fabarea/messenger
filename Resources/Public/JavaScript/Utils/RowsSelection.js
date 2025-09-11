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
    });

    // Expose globally for compatibility
    window.Messenger = Messenger;

    return Messenger;
});
