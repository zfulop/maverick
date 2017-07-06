<?php

require 'phpmailer/class.phpmailer.php';

function sendMail($fromEmail, $fromName, $toEmail, $toName, $subject, $message, $inlineAttachments = array(), $attachments = array()) {
	
	$mail = new PHPMailer(true); // hogy az errorokat egyáltalán triggerelje
	//$mail = new PHPMailer();
	$mail->SetFrom($fromEmail, $fromName);
	$mail->AddReplyTo($fromEmail, $fromName);
	$mail->AddAddress($toEmail, $toName);
	$mail->Subject = $subject;
	$mail->CharSet = 'UTF-8';
	$mail->MsgHTML($message);
	//$mail->SMTPDebug = 2; // debuggolásra
	foreach($attachments as $oneAttachment) {
		$mail->AddAttachment($oneAttachment);
	}
	foreach($inlineAttachments as $cid => $path) {
		$mail->AddEmbeddedImage($path, $cid, basename($path));
	}

	//$locale = LOCALE;
	//list($lang, $co) = explode('_', $locale);
	$mail->SetLanguage('en', PHP_MAILER_LANGUAGE_DIR);


	if(PHP_MAILER_SENDTYPE == PHP_MAILER_SENDTYPE_SMTP) {
		$mail->IsSMTP();
		$mail->Host = PHP_MAILER_SENDTYPE_SMTP_HOST;
		$mail->Port = PHP_MAILER_SENDTYPE_SMTP_PORT;
		if(PHP_MAILER_SENDTYPE_SMTP_AUTHORIZATION_REQ) {
			$mail->SMTPAuth = true;
			$mail->SMTPSecue = PHP_MAILER_SENDTYPE_SMTP_SECURE;
			$mail->Username = PHP_MAILER_SENDTYPE_SMTP_USER;
			$mail->Password = PHP_MAILER_SENDTYPE_SMTP_PASSWORD;
		}
	} elseif(PHP_MAILER_SENDTYPE == PHP_MAILER_SENDTYPE_SENDMAIL) {
		$mail->IsSendMail();
	} else {
		$mail->IsMail();
	}

	try {
		$mail->Send();
	}  catch (phpmailerException $e) {
		return $e->errorMessage(); //Pretty error messages from PHPMailer
	} catch (Exception $e) {
		return $e->getMessage(); //Boring error messages from anything else!
	}
	return NULL;

}

?>