
$(document).ready(function () {

	// Bind action on the top checkbox to (un)select all rows at once.
	$('.checkbox-row-top').click(function () {
		var rows;
		rows = $('#table-recipients').find('tbody tr');
		if ($(this).is(':checked')) {
			rows.filter(':not(:has(:checkbox:checked))').click();
		} else {
			rows.filter(':has(:checkbox:checked)').click();
		}
	});

	// Bind action when a row get clicked -> the row is selected.
	$('#table-recipients').find('tbody tr')
		.filter(':has(:checkbox:checked)')
		.addClass('selected')
		.end()
		.click(function (event) {
			$(this).toggleClass('selected');
			if (event.target.type !== 'checkbox') {
				var checkbox;
				checkbox = $(":checkbox", this);
				checkbox.attr("checked", checkbox.is(':not(:checked)'));
			}
		});

	// Bind action on the button that will send emails
	$('#btn-send').click(function () {
		var selectedUsers;

		selectedUsers = [];
		$('#table-recipients').find('tbody tr').filter(':has(:checkbox:checked)').each(function () {
			selectedUsers.push($(this).data('uid'))
		});
		$('#field-selected-recipients').val(selectedUsers.join(','));
	});

	// bind submit handler to form
	$('#send-message-test-action').on('submit', function (e) {
		e.preventDefault(); // prevent native submit
		$(this).ajaxSubmit({
			beforeSubmit: function () {
				$('#send-message-test-button').addClass('disabled')
			},
			success: function (data) {
				if (data == 'ok') {
					$('#send-message-test-button').removeClass('disabled');
					$('#send-message-test-dropdown').removeClass('open');
				} else {
					alert('Something went wrong with message: ' + data);
				}
			}
		})
	});
});
