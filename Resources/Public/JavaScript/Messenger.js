
$(document).ready(function () {

	/**
	 * Select all recipient from the table at once.
	 */
	$('.checkbox-row-top').click(function () {
		var rows;
		rows = $('#table-recipients').find('tbody tr');
		if ($(this).is(':checked')) {
			rows.filter(':not(:has(:checkbox:checked))').click();
			$('#btn-send, #btn-send-caret').removeClass('disabled');
		} else {
			rows.filter(':has(:checkbox:checked)').click();
			$('#btn-send, #btn-send-caret').addClass('disabled');
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
				$('#btn-send, #btn-send-caret').removeClass('disabled')
			} else {
				$('#btn-send, #btn-send-caret').addClass('disabled')
			}
		});


	/**
	 * Send message action for *all* selected recipients
	 */
	$('#btn-send').click(function (e) {
		if ($(this).hasClass('disabled')) {
			return;
		}

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
				var message;

				message = data.message;
				if (data.status == 'success') {
					$(this).removeClass('disabled');
					$('.send-message-submit').removeClass('disabled');

					if (parseInt(data.message) > 1) {
						message = Messenger.format('x-messages-sent-successfully', data.message);
					} else {
						message = Messenger.format('message-sent-successfully', data.message);
					}
				}

				Messenger.FlashMessage.add(message, data.status);
				Messenger.FlashMessage.showAll();
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
				var message;

				message = data.message;
				if (data.status == 'success') {
					$(this).removeClass('disabled');
					message = Messenger.format('message-sent-successfully', data.message);
				}

				Messenger.FlashMessage.add(message, data.status);
				Messenger.FlashMessage.showAll();
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
				var message;

				message = data.message;
				if (data.status == 'success') {
					$('#send-message-test-submit').removeClass('disabled');
					$('#send-message-test-dropdown').removeClass('open');
					message = Messenger.format('message-sent-successfully', data.message);
				}

				Messenger.FlashMessage.add(message ,data.status);
				Messenger.FlashMessage.showAll();
			}
		})
	});

	/**
	 * Trigger form submit if menu filter change value.
	 */
	$('.menu-filter').change(function () {
		$(this).closest('form').submit();
	});
});



/**
 * Format a string give a place holder. Acts as the "sprintf" function in PHP
 *
 * Example:
 *
 * "Foo {0}".format('Bar') will return "Foo Bar"
 *
 * @param {string} key
 */
Messenger.format = function (key) {
	var s = Messenger.label(key),
		i = arguments.length + 1;

	while (i--) {
		s = s.replace(new RegExp('\\{' + i + '\\}', 'gm'), arguments[i + 1]);
	}
	return s;
};

/**
 * Shorthand method for getting a label.
 *
 * @param {string} key
 */
Messenger.label = function (key) {
	return Messenger.Label.get(key);
};

