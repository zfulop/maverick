#!/bin/sh

cd /home/zolika/roomcaptain_recepcio

php -c ../php.ini send_bcr_one_week.php lodge
php -c ../php.ini send_bcr_one_week.php hostel
php -c ../php.ini send_bcr_3_days.php lodge
php -c ../php.ini send_bcr_3_days.php hostel

