<?php

require 'phpmailer/class.phpmailer.php';
require_once "HTML/Template/IT.php";

function sendMail($fromEmail, $fromName, $toEmail, $toName, $subject, $message, $inlineAttachments = array(), $attachments = array()) {
	MaverickMailer::send($fromEmail, $fromName, $toEmail, $toName, $subject, $message, $inlineAttachments, $attachments);
}

class MaverickMailer {
	
	private $fromEmail;
	private $fromName;
	private $toEmail;
	private $toName;
	
	function __construct($_fromEmail, $_fromName, $_toEmail, $_toName) {
		$this->fromEmail = $_fromEmail;
		$this->fromName = $_fromName;
		$this->toEmail = $_toEmail;
		$this->toName = $_toName;
	}

	public function sendTemplatedMail($subject, $templateFile, $data, $inlineAttachments = array(), $attachments = array()) {
		$tpl = new HTML_Template_IT(TEMPLATE_DIR);
		$tpl->loadTemplatefile($templateFile, false, true);
		$this->setTemplateVariables($tpl, $data);
		$messageBody = $tpl->get();
		$this->sendMail($subject, $messageBody, $inlineAttachments, $attachments);
	}

	private function setTemplateVariables(&$tpl, $data) {
		foreach($data as $key => $value) {
			if(is_array($value)) {
				foreach($value as $innerValue) {
					logDebug("set current block: $key");
					$tpl->setCurrentBlock($key);
					$this->setTemplateVariables($tpl, $innerValue);
					$tpl->parse($key);
					logDebug("parse current block: $key");
				}
			} else {
//				logDebug('Setting variable: ' . $key .  ' to value: ' . $value);
				$tpl->setVariable($key, $value);
			}
		}
	}
	
	/**
	 * Returns true if any of the values in the associated array given in the parameter is an array itself
	 */
	private function hasArrayValues($arr) {
		foreach($arr as $key => $value) {
			if(is_array($value)) {
				return true;
			}
		}
		return false;
	}
	
	public function sendMail($subject, $message, $inlineAttachments = array(), $attachments = array()) {
		$mail = new PHPMailer(true); // hogy az errorokat egyáltalán triggerelje
		//$mail = new PHPMailer();
		$mail->SetFrom($this->fromEmail, $this->fromName);
		$mail->AddReplyTo($this->fromEmail, $this->fromName);
		$mail->AddAddress($this->toEmail, $this->toName);
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
			logDebug("Sending mail from " . $this->fromName . " <" . $this->fromEmail . "> to " . $this->toName . " <" . $this->toEmail . ">");
			$mail->Send();
			logDebug("Mail successfully sent");
		}  catch (phpmailerException $e) {
			logError("Error sending mail: " . $e->errorMessage());
			return $e->errorMessage(); //Pretty error messages from PHPMailer
		} catch (Exception $e) {
			logError("Error sending mail: " . $e->getMessage());
			return $e->getMessage(); //Boring error messages from anything else!
		}
		return NULL;

	}

	public static function send($_fromEmail, $_fromName, $_toEmail, $_toName, $subject, $message, $inlineAttachments = array(), $attachments = array()) {
		$mailer = new MaverickMailer($_fromEmail, $_fromName, $_toEmail, $_toName);
		$mailer->sendMail($subject, $message, $inlineAttachments, $attachments);
	}
	
}
?>