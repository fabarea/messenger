window.Messenger = {
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
    $('#record-all').click(function () {
      const checkboxes = $('.checkboxes').find('.select');
      if ($(this).is(':checked')) {
        checkboxes.filter(':not(:checked)').click();
      } else {
        checkboxes.filter(':checked').click();
      }
    });
  },
  getSelectedItems: function () {
    return [...document.querySelectorAll('.select:checked')].map((element) => element.value);
  },

  exportFormat: function (format) {
    const selected = this.getSelectedItems();
    if (selected.length === 0) {
      alert('Please select at least one item');
      return;
    }
    document.getElementById('field-format').value = format;
    document.getElementById('field-format').form.submit();
  },
};
