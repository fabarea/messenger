// Description: Get selected values from checkboxes
function deleteItem(element) {
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
}

function allSelected() {
  $('#record-all').click(function () {
    var checkboxes;
    checkboxes = $('.checkboxes').find('.select');
    if ($(this).is(':checked')) {
      checkboxes.filter(':not(:checked)').click();
    } else {
      checkboxes.filter(':checked').click();
    }
  });
}

//   if (document.getElementById('record-all').checked) {
//     const checkboxes = document.getElementsByClassName('select');
//     for (let i = 0; i < checkboxes.length; i++) {
//       checkboxes[i].checked = true;
//     }
//   } else {
//     const checkboxes = document.getElementsByClassName('select');
//     for (let i = 0; i < checkboxes.length; i++) {
//       checkboxes[i].checked = false;
//     }
//   }
// }

function selectedItems() {
  const checkboxes = document.getElementsByClassName('select');
  const selected = [];
  for (let i = 0; i < checkboxes.length; i++) {
    if (checkboxes[i].checked) {
      selected.push(checkboxes[i].value);
    }
  }
  return selected;
}

function exportFormat(format) {
  const selected = selectedItems();
  if (selected.length === 0) {
    alert('Please select at least one item');
    return;
  }
  document.getElementById('btn-onclick-action').value = format;
  document.getElementById('btn-onclick-action').form.submit();
}
