<?php

# Configuration
# 
define('DESTINATION', 'tai@g5n.co.uk');
define('SCRIPT_URI',  './jquery-mailer.php');
define('FORM_ID',     'jquery_mailer');
define('FORM_TEXT',   'Contact us by e-mail...');
define('FORM_BUTTON', 'Send message');

###############################################################

function ok () {
	header("Content-Type: application/json");
	print json_encode(array("status" => "ok"));
	exit();
}

function not_ok ($e) {
	header("Content-Type: application/json");
	print json_encode(array("status" => "not ok", "error" => $e));
	exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST'
&&  isset($_POST['message'])
&&  isset($_POST['subject'])
&&  isset($_POST['from']))
{
	$from = filter_var($_POST['from'], FILTER_VALIDATE_EMAIL);
	if ($from === FALSE) {
		not_ok("E-mail address not valid: '${_POST['from']}'");
	}
	
	$subject = $_POST['subject'];
	if (strpos($subject, "\n") !== FALSE) {
		not_ok("Subject line not valid: '${_POST['subject']}'");
	}
	
	$message = $_POST['message'];
	
	mail(DESTINATION, $subject, $message, "From: $from");
	ok();
}
elseif ($_SERVER['REQUEST_METHOD'] == 'POST')
{
	not_ok("Need to set 'message', 'subject' and 'from' parameters");
}

header("Content-Type: text/javascript");

?>

$(function () {
	var $form = $("#<?php echo FORM_ID ?>");
	var $slug = Math.floor((Math.random()*100000)+1);
	$form.text("<?php echo addslashes(FORM_TEXT) ?>");
	$form.click(function () {
		if ($form.hasClass('open')) {
			return;
		}
		
		$form.addClass('open');
		$form.append(
			$('<div class="form_pair">').append(
				'<label for="from_'+$slug+'">Your e-mail address:</label>',
				'<input name="from" id="from_'+$slug+'" type="email">'
			)
		);
		$form.append(
			$('<div class="form_pair">').append(
				'<label for="subject_'+$slug+'">Message subject:</label>',
				'<input name="subject" id="subject_'+$slug+'" type="text">'
			)
		);
		$form.append(
			$('<div class="form_main_part">').append(
				'<label for="message_'+$slug+'">Message:</label>',
				'<textarea name="message" id="message_'+$slug+'" rows="6" cols="60"></textarea>'
			)
		);
		
		var $submit_button = $('<input name="submit" id="submit_'+$slug+'" type="submit" value="<?php echo addslashes(FORM_BUTTON) ?>">');
		$form.append(
			$('<div class="form_submit_part">').append($submit_button)
		);
		$submit_button.click(function () {
			$submit_button.attr("disabled", "disabled");
			$.post(
				"<?php echo addslashes(SCRIPT_URI) ?>",
				$form.serialize(),
				function ($response) {
					if ($response.status == "ok") {
						window.alert("Message sent!");
					}
					else {
						window.alert("Message NOT sent! " + $response.error);
					}
				},
				"json"
			);
			$submit_button.removeAttr("disabled");
			return false;
		});
	});
});

