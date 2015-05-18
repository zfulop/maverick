<?php

define('DB_HOST', 'localhost');
define('DB_USER', 'website');
define('DB_PASSWORD', 'dsfv34ras2SQg');
define('DB_NAME', 'lodge');

define('PHP_MAILER_LANGUAGE_DIR', "/home/zolika/lodge_recepcio/includes/phpmailer/language");
define('PHP_MAILER_SENDTYPE_SENDMAIL', 'SENDMAIL');
define('PHP_MAILER_SENDTYPE_MAIL', 'MAIL');
define('PHP_MAILER_SENDTYPE_SMTP', 'SMTP');
define('PHP_MAILER_SENDTYPE', PHP_MAILER_SENDTYPE_MAIL);

define('PHP_MAILER_SENDTYPE_SMTP_HOST', 'mail.maxer.hu');
define('PHP_MAILER_SENDTYPE_SMTP_PORT', '25');
define('PHP_MAILER_SENDTYPE_SMTP_AUTHORIZATION_REQ', 'true');
define('PHP_MAILER_SENDTYPE_SMTP_SECURE', '');
define('PHP_MAILER_SENDTYPE_SMTP_USER', 'mailsender@mavericklodges.com');
define('PHP_MAILER_SENDTYPE_SMTP_PASSWORD', 'mailsender01');

define('EMAIL_IMG_DIR', "/home/zolika/www/img/email/");

define('IMG_ROOT_URL', 'http://www.mavericklodges.com/img/');
define('IMG_ROOT_DIR', '/home/zolika/www/img/');


define('ROOMS_IMG_DIR', IMG_ROOT_DIR . "rooms/");
define('ROOMS_IMG_URL', IMG_ROOT_URL . "rooms/");
define('SERVICES_IMG_DIR', IMG_ROOT_DIR . "services/");
define('SERVICES_IMG_URL', IMG_ROOT_URL . "services/");
define('AWARDS_IMG_DIR', IMG_ROOT_DIR . "awards/");
define('AWARDS_IMG_URL', IMG_ROOT_URL . "awards/");

define('ROOT_URL', '/');
define('LOCATION', 'lodge');
define('CONTACT_EMAIL', 'reservation@mavericklodges.com');

define('EXCHANGE_TABLE_FILE', '/home/zolika/lodge_recepcio/includes/exchange_table.php');

define('FPDF_FONTPATH','/home/zolika/lodge_recepcio/includes/font');

define('CONFIRM_BOOKING_URL', 'http://www.mavericklodges.com/LANG/confirm_booking.php?CONFIRM_CODE');
define('LANG_DIR', '/home/zolika/www/includes/language/');

?>
