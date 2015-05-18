<?php

define('DB_HOST_APARTMENTS', 'localhost');
define('DB_USER_APARTMENTS', 'website');
define('DB_PASSWORD_APARTMENTS', 'dsfv34ras2SQg');
define('DB_NAME_APARTMENTS', 'hostel');
define('DB_HOST_HOSTEL', 'localhost');
define('DB_USER_HOSTEL', 'website');
define('DB_PASSWORD_HOSTEL', 'dsfv34ras2SQg');
define('DB_NAME_HOSTEL', 'hostel');
define('DB_HOST_LODGE', 'localhost');
define('DB_USER_LODGE', 'website');
define('DB_PASSWORD_LODGE', 'dsfv34ras2SQg');
define('DB_NAME_LODGE', 'lodge');

define('BASE_DIR', "/home/zolika/www/");
define('HOSTEL_BASE_DIR', "/home/zolika/www/");
define('BASE_URL', "/");
define('RECEPCIO_BASE_DIR', "../lodge_recepcio/");

define('PHP_MAILER_LANGUAGE_DIR', BASE_DIR . "includes/phpmailer/language");
define('PHP_MAILER_SENDTYPE_SENDMAIL', 'SENDMAIL');
define('PHP_MAILER_SENDTYPE_MAIL', 'MAIL');
define('PHP_MAILER_SENDTYPE_SMTP', 'SMTP');
define('PHP_MAILER_SENDTYPE', PHP_MAILER_SENDTYPE_MAIL);
define('PHP_MAILER_SENDTYPE_SMTP_HOST', 'mail.t-online.hu');
define('PHP_MAILER_SENDTYPE_SMTP_PORT', '25');
define('PHP_MAILER_SENDTYPE_SMTP_AUTHORIZATION_REQ', true);
define('PHP_MAILER_SENDTYPE_SMTP_SECURE', '');
define('PHP_MAILER_SENDTYPE_SMTP_USER', 'zoltanfulop74@t-online.hu');
define('PHP_MAILER_SENDTYPE_SMTP_PASSWORD', 'gsm4nmH7');

define('EMAIL_IMG_DIR', BASE_DIR . "img/email/");
define('ROOMS_IMG_DIR', BASE_DIR . "img/rooms/");
define('ROOMS_IMG_URL_APARTMENTS', BASE_URL . "img/rooms/");
define('ROOMS_IMG_URL_HOSTEL', BASE_URL . "img/rooms/");
define('ROOMS_IMG_URL_LODGE', BASE_URL . "img/rooms/");
define('SERVICES_IMG_URL_APARTMENTS', BASE_URL . "img/services/");
define('SERVICES_IMG_URL_HOSTEL', BASE_URL . "img/services/");
define('SERVICES_IMG_URL_LODGE', BASE_URL . "img/services/");
define('AWARDS_IMG_URL_APARTMENTS', BASE_URL . "img/awards/");
define('AWARDS_IMG_URL_HOSTEL', BASE_URL . "img/awards/");
define('AWARDS_IMG_URL_LODGE', BASE_URL . "img/awards/");

define('GEO_IP_DAT_FILE', BASE_DIR . "includes/GeoIP.dat");

define('CONTACT_EMAIL_APARTMENTS', 'reservation@maverickhostel.com');
define('CONTACT_EMAIL_HOSTEL', 'reservation@maverickhostel.com');
define('CONTACT_EMAIL_LODGE', 'reservation@mavericklodges.com');
define('CONTACT_FAX_APARTMENTS', '+36 1 7004598');
define('CONTACT_FAX_HOSTEL', '+36 1 7004598');
define('CONTACT_FAX_LODGE', '+36 1 7004598');
define('CONTACT_PHONE_APARTMENTS', '+36 1 2673166');
define('CONTACT_PHONE_HOSTEL', '+36 1 2673166');
define('CONTACT_PHONE_LODGE', '+36 1 7931605');

define('LATITUDE_APARTMENTS', '47.492994');
define('LONGITUDE_APARTMENTS', '19.055293');
define('LATITUDE_HOSTEL', '47.492994');
define('LONGITUDE_HOSTEL', '19.055293');
define('LATITUDE_LODGE', '47.498401');
define('LONGITUDE_LODGE', '19.062738');

define('EXCHANGE_TABLE_FILE', '/home/zolika/lodge_recepcio/includes/exchange_table.php');

?>
