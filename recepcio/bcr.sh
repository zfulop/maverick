#!/bin/sh

cd /home/zolika/roomcaptain_recepcio

php -c ../php.ini send_bcr_one_week.php lodge deu
php -c ../php.ini send_bcr_one_week.php lodge eng
php -c ../php.ini send_bcr_one_week.php lodge fra
php -c ../php.ini send_bcr_one_week.php lodge ita
php -c ../php.ini send_bcr_one_week.php lodge esp
php -c ../php.ini send_bcr_one_week.php hostel deu
php -c ../php.ini send_bcr_one_week.php hostel eng
php -c ../php.ini send_bcr_one_week.php hostel fra
php -c ../php.ini send_bcr_one_week.php hostel ita
php -c ../php.ini send_bcr_one_week.php hostel esp
php -c ../php.ini send_bcr_3_days.php lodge deu
php -c ../php.ini send_bcr_3_days.php lodge eng
php -c ../php.ini send_bcr_3_days.php lodge fra
php -c ../php.ini send_bcr_3_days.php lodge ita
php -c ../php.ini send_bcr_3_days.php lodge esp
php -c ../php.ini send_bcr_3_days.php hostel deu
php -c ../php.ini send_bcr_3_days.php hostel eng
php -c ../php.ini send_bcr_3_days.php hostel fra
php -c ../php.ini send_bcr_3_days.php hostel ita
php -c ../php.ini send_bcr_3_days.php hostel esp

