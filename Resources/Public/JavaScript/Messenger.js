
$(document).ready(function () {

	/**
	 * Select all recipient from the table at once.
	 */
	$('.checkbox-row-top').click(function () {
		var rows;
		rows = $('#table-recipients').find('tbody tr');
		if ($(this).is(':checked')) {
			rows.filter(':not(:has(:checkbox:checked))').click();
			$('#btn-send').removeClass('disabled');
		} else {
			rows.filter(':has(:checkbox:checked)').click();
			$('#btn-send').addClass('disabled');
		}
	});

	/**
	 * Select a recipient from the table.
	 */
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

			// make the top bottom activated if a row is selected
			if ($('#table-recipients').find('tbody tr').filter(':has(:checkbox:checked)').length > 0) {
				$('#btn-send').removeClass('disabled')
			} else {
				$('#btn-send').addClass('disabled')
			}
		});


	/**
	 * Send message action for *all* selected recipients
	 */
	$('#btn-send').click(function (e) {

		var selectedUsers;

		// Fetch selected users from the table
		selectedUsers = [];
		$('#table-recipients').find('tbody tr').filter(':has(:checkbox:checked)').each(function () {
			selectedUsers.push($(this).data('uid'))
		});
		$('#field-selected-recipients').val(selectedUsers.join(','));

		// Gui update
		$(this).addClass('disabled');
		$('.send-message-submit').addClass('disabled');

		// Send request
		$($(this).closest('form')).ajaxSubmit({
			context: this,
			success: function (data) {
				if (data == 'ok') {
					$(this).removeClass('disabled');
					$('.send-message-submit').removeClass('disabled');
				} else {
					alert('Something went wrong with message: ' + data);
				}
			}
		});
		e.stopPropagation();
		e.preventDefault();
	});


	/**
	 * Send message (to recipients) action
	 */
	$('.send-message-submit').click(function (e) {
		var messageUid;

		// Gui update
		$(this).addClass('disabled');

		// Get the message uid which must be set!
		messageUid = $(this).closest('form')
			.find('.hidden-message-template-uid')
			.val();

		// Send request
		$($(this).closest('form')).ajaxSubmit({
			context: this,
			beforeSubmit: function () {
				if (messageUid <= 0) {
					alert("No message template was selected. Please set one \"uid\" in the Extension Manager. \n\n" +
						"A proper template message picker is in the pipeline but he's waiting for a sponsor opportunity...");
					return false;
				}
			},
			success: function (data) {
				if (data == 'ok') {
					$(this).removeClass('disabled');
				} else {
					alert('Something went wrong with message: ' + data);
				}
			}
		});

		e.stopPropagation();
		e.preventDefault();
	});

	/**
	 * Send message testing action
	 */
	$('#send-message-test-action').on('submit', function (e) {
		e.preventDefault(); // prevent native submit
		$(this).ajaxSubmit({
			beforeSubmit: function () {
				$('#send-message-test-submit').addClass('disabled')
			},
			success: function (data) {
				if (data == 'ok') {
					$('#send-message-test-submit').removeClass('disabled');
					$('#send-message-test-dropdown').removeClass('open');
				} else {
					alert('Something went wrong with message: ' + data);
				}
			}
		})
	});
});
