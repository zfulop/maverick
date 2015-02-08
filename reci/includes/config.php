<?php

define('DB_HOST', 'localhost');
define('DB_USER', 'mavericklo_11826');
define('DB_PASSWORD', 'dsf5vwe2vv');
define('DB_NAME', 'mavericklo_11826');

define('PHP_MAILER_LANGUAGE_DIR', "/var/hosting/web/mavericklodges.com/website/www/includes/phpmailer/language");
define('PHP_MAILER_SENDTYPE_SENDMAIL', 'SENDMAIL');
define('PHP_MAILER_SENDTYPE_MAIL', 'MAIL');
define('PHP_MAILER_SENDTYPE_SMTP', 'SMTP');
define('PHP_MAILER_SENDTYPE', PHP_MAILER_SENDTYPE_SMTP);

define('PHP_MAILER_SENDTYPE_SMTP_HOST', 'mail.maxer.hu');
define('PHP_MAILER_SENDTYPE_SMTP_PORT', '25');
define('PHP_MAILER_SENDTYPE_SMTP_AUTHORIZATION_REQ', 'true');
define('PHP_MAILER_SENDTYPE_SMTP_SECURE', '');
define('PHP_MAILER_SENDTYPE_SMTP_USER', 'mailsender@mavericklodges.com');
define('PHP_MAILER_SENDTYPE_SMTP_PASSWORD', 'mailsender01');

define('EMAIL_IMG_DIR', "/var/hosting/web/mavericklodges.com/website/www/img/email/");

define('IMG_ROOT_URL', 'http://www.mavericklodges.com/img/');
define('IMG_ROOT_DIR', '/var/hosting/web/mavericklodges.com/website/www/img/');

define('ROOMS_IMG_DIR', IMG_ROOT_DIR . "rooms/");
define('ROOMS_IMG_URL', IMG_ROOT_URL . "rooms/");
define('SERVICES_IMG_DIR', IMG_ROOT_DIR . "services/");
define('SERVICES_IMG_URL', IMG_ROOT_URL . "services/");
define('AWARDS_IMG_DIR', IMG_ROOT_DIR . "awards/");
define('AWARDS_IMG_URL', IMG_ROOT_URL . "awards/");

define('ROOT_URL', '/');
define('LOCATION', 'lodge');
define('CONTACT_EMAIL', 'reservation@mavericklodges.com');

define('EXCHANGE_TABLE_FILE', '/var/hosting/web/mavericklodges.com/website/recepcio/includes/exchange_table.php');

define('FPDF_FONTPATH','/var/hosting/web/mavericklodges.com/website/recepcio/includes/font');

define('CONFIRM_BOOKING_URL', 'http://www.mavericklodges.com/LANG/confirm_booking.php?location=lodge&confirmCode=CONFIRM_CODE');
define('LANG_DIR', '/var/hosting/web/mavericklodges.com/website/www/includes/language/');


?>
