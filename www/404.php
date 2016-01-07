<?php

require('includes.php');

$location = getLocation();
$link = db_connect($location);

$afterBody = <<<EOT
    <div id='gallery'>
        <h1 class='gallery-title'>
        </h1>
          <span class='galleryClose' onClick="$('#gallery').fadeOut(); $('iframe.gallery').attr('src','');">X</span>
        <center>
        <iframe class='gallery'  frameborder='0' src=''></iframe>
        </center>
    </div>

EOT;


html_start(HOME, '', '', $afterBody);

$lang = getCurrentLanguage();

$lodgeShortDescription = LODGE_DESCRIPTION_HOME;
$hostelShortDescription = HOSTEL_DESCRIPTION_HOME;
$apartmentShortDescription = APARTMENTS_DESCRIPTION_HOME;
$locations = LOCATIONS;
$addressTitle = ADDRESS_TITLE;
$addressValueHostel = ADDRESS_VALUE_HOSTEL;
$addressValueHostelGeneral = ADDRESS_VALUE_HOSTEL_GENERAL;
$addressValueLodge = ADDRESS_VALUE_LODGE;
$addressValueLodgeGeneral = ADDRESS_VALUE_LODGE_GENERAL;
$addressValueApartment = ADDRESS_VALUE_APARTMENTS;
$addressValueApartmentGeneral = ADDRESS_VALUE_APARTMENTS_GENERAL;
$phone = PHONE;
$email = EMAIL;
$fax = FAX;
$phoneHostel = CONTACT_PHONE_HOSTEL;
$emailHostel = CONTACT_EMAIL_HOSTEL;
$faxHostel = CONTACT_FAX_HOSTEL;
$phoneLodge = CONTACT_PHONE_LODGE;
$emailLodge = CONTACT_EMAIL_LODGE;
$faxLodge = CONTACT_FAX_LODGE;

$lodgeLatitude = LATITUDE_LODGE;
$lodgeLongitude = LONGITUDE_LODGE;
$hostelLatitude = LATITUDE_HOSTEL;
$hostelLongitude = LONGITUDE_HOSTEL;


$somethinWentWrong = SOMETHING_WENT_WRONG;
$linkBroken = LINK_BROKEN;
$seeYouAtMaverick = SEE_YOU_AT_MAVERICK;


echo <<<EOT

      <div class="fluid-wrapper columns">

	<div class="e404">$somethinWentWrong</div>
    <div class="e404-2">$linkBroken</div>

    <center>
    <img src="/img/maverick_404_logo.png" alt="404" class="i404">
    </center>

    <div class="e404-3">$seeYouAtMaverick</div>
 


        <section id="location-list">
          <ul class="clearfix">
            <li class="hostel">
              <a href="maverick_hostel_ensuites.php">
                <h2>
                  <img width="470" height="470" src="/img/location-hostel.jpg">
                  <span class="title">$addressValueHostelGeneral</span>
                </h2>
                
              </a>
              
              <div class="info">
				<p>
                  $hostelShortDescription
                </p>
              </div>
            </li>
            
            <li class="lodge">
              <a href="maverick_city_lodge.php">
                <h2>
                  <img width="470" height="470" src="/img/location-lodge.jpg">
                  <span class="title">$addressValueLodgeGeneral</span>
                </h2>
              </a>
              
              <div class="info">
				<p>
                  $lodgeShortDescription<br>
				</p>
              </div>
            </li>
            <li class="apartments">
              <a href="maverick_apartments.php">
                <h2>
                  <img width="470" height="470" src="/img/location-apartment.jpg">
                  <span class="title">$addressValueApartmentGeneral</span>
                </h2>
              </a>
              
              <div class="info">
				<p>
                  $apartmentShortDescription
                </p>
              </div>
            </li>
          </ul>
        </section>

   
        <section id="location">

                <div id="map-container">
                    <div class="map" data-poi-url="/poi-contact-$lang.json" style="height: 346.15px; position: relative; overflow: hidden; transform: translateZ(0px); background-color: rgb(229, 227, 223);"><div class="gm-style" style="position: absolute; left: 0px; top: 0px; overflow: hidden; width: 100%; height: 100%; z-index: 0;"><div style="position: absolute; left: 0px; top: 0px; overflow: hidden; width: 100%; height: 100%; z-index: 0; cursor: url(https://maps.gstatic.com/mapfiles/openhand_8_8.cur) 8 8, default;"><div style="position: absolute; left: 0px; top: 0px; z-index: 1; width: 100%; transform-origin: 0px 0px 0px; transform: matrix(1, 0, 0, 1, 0, 0);"><div style="transform: translateZ(0px); position: absolute; left: 0px; top: 0px; z-index: 100; width: 100%;"><div style="position: absolute; left: 0px; top: 0px; z-index: 0;"><div aria-hidden="true" style="position: absolute; left: 0px; top: 0px; z-index: 1; visibility: inherit;"><div style="width: 256px; height: 256px; transform: translateZ(0px); position: absolute; left: 328px; top: -96px;"></div><div style="width: 256px; height: 256px; transform: translateZ(0px); position: absolute; left: 328px; top: 160px;"></div><div style="width: 256px; height: 256px; transform: translateZ(0px); position: absolute; left: 72px; top: -96px;"></div><div style="width: 256px; height: 256px; transform: translateZ(0px); position: absolute; left: 72px; top: 160px;"></div><div style="width: 256px; height: 256px; transform: translateZ(0px); position: absolute; left: 584px; top: -96px;"></div><div style="width: 256px; height: 256px; transform: translateZ(0px); position: absolute; left: 584px; top: 160px;"></div><div style="width: 256px; height: 256px; transform: translateZ(0px); position: absolute; left: -184px; top: -96px;"></div><div style="width: 256px; height: 256px; transform: translateZ(0px); position: absolute; left: -184px; top: 160px;"></div><div style="width: 256px; height: 256px; transform: translateZ(0px); position: absolute; left: 840px; top: -96px;"></div><div style="width: 256px; height: 256px; transform: translateZ(0px); position: absolute; left: 840px; top: 160px;"></div></div></div></div><div style="transform: translateZ(0px); position: absolute; left: 0px; top: 0px; z-index: 101; width: 100%;"></div><div style="transform: translateZ(0px); position: absolute; left: 0px; top: 0px; z-index: 102; width: 100%;"></div><div style="transform: translateZ(0px); position: absolute; left: 0px; top: 0px; z-index: 103; width: 100%;"><div style="position: absolute; left: 0px; top: 0px; z-index: -1;"><div aria-hidden="true" style="position: absolute; left: 0px; top: 0px; z-index: 1; visibility: inherit;"><div style="width: 256px; height: 256px; overflow: hidden; transform: translateZ(0px); position: absolute; left: 328px; top: -96px;"><canvas draggable="false" height="256" width="256" style="-webkit-user-select: none; position: absolute; left: 0px; top: 0px; height: 256px; width: 256px;"></canvas></div><div style="width: 256px; height: 256px; overflow: hidden; transform: translateZ(0px); position: absolute; left: 328px; top: 160px;"><canvas draggable="false" height="256" width="256" style="-webkit-user-select: none; position: absolute; left: 0px; top: 0px; height: 256px; width: 256px;"></canvas></div><div style="width: 256px; height: 256px; overflow: hidden; transform: translateZ(0px); position: absolute; left: 72px; top: -96px;"></div><div style="width: 256px; height: 256px; overflow: hidden; transform: translateZ(0px); position: absolute; left: 72px; top: 160px;"><canvas draggable="false" height="256" width="256" style="-webkit-user-select: none; position: absolute; left: 0px; top: 0px; height: 256px; width: 256px;"></canvas></div><div style="width: 256px; height: 256px; overflow: hidden; transform: translateZ(0px); position: absolute; left: 584px; top: -96px;"><canvas draggable="false" height="256" width="256" style="-webkit-user-select: none; position: absolute; left: 0px; top: 0px; height: 256px; width: 256px;"></canvas></div><div style="width: 256px; height: 256px; overflow: hidden; transform: translateZ(0px); position: absolute; left: 584px; top: 160px;"></div><div style="width: 256px; height: 256px; overflow: hidden; transform: translateZ(0px); position: absolute; left: -184px; top: -96px;"></div><div style="width: 256px; height: 256px; overflow: hidden; transform: translateZ(0px); position: absolute; left: -184px; top: 160px;"></div><div style="width: 256px; height: 256px; overflow: hidden; transform: translateZ(0px); position: absolute; left: 840px; top: -96px;"></div><div style="width: 256px; height: 256px; overflow: hidden; transform: translateZ(0px); position: absolute; left: 840px; top: 160px;"></div></div></div></div><div style="position: absolute; left: 0px; top: 0px; z-index: 0;"><div aria-hidden="true" style="position: absolute; left: 0px; top: 0px; z-index: 1; visibility: inherit;"><div style="transform: translateZ(0px); position: absolute; left: 328px; top: -96px; transition: opacity 200ms ease-out;"><img src="https://mts0.googleapis.com/vt?pb=!1m4!1m3!1i15!2i18118!3i11458!2m3!1e0!2sm!3i320063941!3m14!2shu-HU!3sUS!5e18!12m1!1e47!12m3!1e37!2m1!1ssmartmaps!12m4!1e26!2m2!1sstyles!2zcy50OjMzfHAudjpvZmY!4e0" draggable="false" style="width: 256px; height: 256px; -webkit-user-select: none; border: 0px; padding: 0px; margin: 0px;"></div><div style="transform: translateZ(0px); position: absolute; left: 328px; top: 160px; transition: opacity 200ms ease-out;"><img src="https://mts0.googleapis.com/vt?pb=!1m4!1m3!1i15!2i18118!3i11459!2m3!1e0!2sm!3i320063941!3m14!2shu-HU!3sUS!5e18!12m1!1e47!12m3!1e37!2m1!1ssmartmaps!12m4!1e26!2m2!1sstyles!2zcy50OjMzfHAudjpvZmY!4e0" draggable="false" style="width: 256px; height: 256px; -webkit-user-select: none; border: 0px; padding: 0px; margin: 0px;"></div><div style="transform: translateZ(0px); position: absolute; left: 72px; top: -96px; transition: opacity 200ms ease-out;"><img src="https://mts1.googleapis.com/vt?pb=!1m4!1m3!1i15!2i18117!3i11458!2m3!1e0!2sm!3i320056026!3m14!2shu-HU!3sUS!5e18!12m1!1e47!12m3!1e37!2m1!1ssmartmaps!12m4!1e26!2m2!1sstyles!2zcy50OjMzfHAudjpvZmY!4e0" draggable="false" style="width: 256px; height: 256px; -webkit-user-select: none; border: 0px; padding: 0px; margin: 0px;"></div><div style="transform: translateZ(0px); position: absolute; left: 72px; top: 160px; transition: opacity 200ms ease-out;"><img src="https://mts1.googleapis.com/vt?pb=!1m4!1m3!1i15!2i18117!3i11459!2m3!1e0!2sm!3i320058179!3m14!2shu-HU!3sUS!5e18!12m1!1e47!12m3!1e37!2m1!1ssmartmaps!12m4!1e26!2m2!1sstyles!2zcy50OjMzfHAudjpvZmY!4e0" draggable="false" style="width: 256px; height: 256px; -webkit-user-select: none; border: 0px; padding: 0px; margin: 0px;"></div><div style="transform: translateZ(0px); position: absolute; left: 584px; top: -96px; transition: opacity 200ms ease-out;"><img src="https://mts1.googleapis.com/vt?pb=!1m4!1m3!1i15!2i18119!3i11458!2m3!1e0!2sm!3i320063941!3m14!2shu-HU!3sUS!5e18!12m1!1e47!12m3!1e37!2m1!1ssmartmaps!12m4!1e26!2m2!1sstyles!2zcy50OjMzfHAudjpvZmY!4e0" draggable="false" style="width: 256px; height: 256px; -webkit-user-select: none; border: 0px; padding: 0px; margin: 0px;"></div><div style="transform: translateZ(0px); position: absolute; left: 584px; top: 160px; transition: opacity 200ms ease-out;"><img src="https://mts1.googleapis.com/vt?pb=!1m4!1m3!1i15!2i18119!3i11459!2m3!1e0!2sm!3i320063941!3m14!2shu-HU!3sUS!5e18!12m1!1e47!12m3!1e37!2m1!1ssmartmaps!12m4!1e26!2m2!1sstyles!2zcy50OjMzfHAudjpvZmY!4e0" draggable="false" style="width: 256px; height: 256px; -webkit-user-select: none; border: 0px; padding: 0px; margin: 0px;"></div><div style="transform: translateZ(0px); position: absolute; left: -184px; top: -96px; transition: opacity 200ms ease-out;"><img src="https://mts0.googleapis.com/vt?pb=!1m4!1m3!1i15!2i18116!3i11458!2m3!1e0!2sm!3i320056026!3m14!2shu-HU!3sUS!5e18!12m1!1e47!12m3!1e37!2m1!1ssmartmaps!12m4!1e26!2m2!1sstyles!2zcy50OjMzfHAudjpvZmY!4e0" draggable="false" style="width: 256px; height: 256px; -webkit-user-select: none; border: 0px; padding: 0px; margin: 0px;"></div><div style="transform: translateZ(0px); position: absolute; left: -184px; top: 160px; transition: opacity 200ms ease-out;"><img src="https://mts0.googleapis.com/vt?pb=!1m4!1m3!1i15!2i18116!3i11459!2m3!1e0!2sm!3i320056026!3m14!2shu-HU!3sUS!5e18!12m1!1e47!12m3!1e37!2m1!1ssmartmaps!12m4!1e26!2m2!1sstyles!2zcy50OjMzfHAudjpvZmY!4e0" draggable="false" style="width: 256px; height: 256px; -webkit-user-select: none; border: 0px; padding: 0px; margin: 0px;"></div><div style="transform: translateZ(0px); position: absolute; left: 840px; top: -96px; transition: opacity 200ms ease-out;"><img src="https://mts0.googleapis.com/vt?pb=!1m4!1m3!1i15!2i18120!3i11458!2m3!1e0!2sm!3i320063941!3m14!2shu-HU!3sUS!5e18!12m1!1e47!12m3!1e37!2m1!1ssmartmaps!12m4!1e26!2m2!1sstyles!2zcy50OjMzfHAudjpvZmY!4e0" draggable="false" style="width: 256px; height: 256px; -webkit-user-select: none; border: 0px; padding: 0px; margin: 0px;"></div><div style="transform: translateZ(0px); position: absolute; left: 840px; top: 160px; transition: opacity 200ms ease-out;"><img src="https://mts0.googleapis.com/vt?pb=!1m4!1m3!1i15!2i18120!3i11459!2m3!1e0!2sm!3i320063941!3m14!2shu-HU!3sUS!5e18!12m1!1e47!12m3!1e37!2m1!1ssmartmaps!12m4!1e26!2m2!1sstyles!2zcy50OjMzfHAudjpvZmY!4e0" draggable="false" style="width: 256px; height: 256px; -webkit-user-select: none; border: 0px; padding: 0px; margin: 0px;"></div></div></div></div><div style="position: absolute; left: 0px; top: 0px; z-index: 2; width: 100%; height: 100%;"></div><div style="position: absolute; left: 0px; top: 0px; z-index: 3; width: 100%; transform-origin: 0px 0px 0px; transform: matrix(1, 0, 0, 1, 0, 0);"><div style="transform: translateZ(0px); position: absolute; left: 0px; top: 0px; z-index: 104; width: 100%;"></div><div style="transform: translateZ(0px); position: absolute; left: 0px; top: 0px; z-index: 105; width: 100%;"></div><div style="transform: translateZ(0px); position: absolute; left: 0px; top: 0px; z-index: 106; width: 100%;"></div><div style="transform: translateZ(0px); position: absolute; left: 0px; top: 0px; z-index: 107; width: 100%;"></div></div></div><div style="margin-left: 5px; margin-right: 5px; z-index: 1000000; position: absolute; left: 0px; bottom: 0px;"><a target="_blank" href="https://maps.google.com/maps?ll=47.494547,19.057435&amp;z=15&amp;t=m&amp;hl=hu-HU&amp;gl=US&amp;mapclient=apiv3" title="Kattintson a terület Google Térképen való megjelenítéséhez." style="position: static; overflow: visible; float: none; display: inline;"><div style="width: 62px; height: 26px; cursor: pointer;"><img src="https://maps.gstatic.com/mapfiles/api-3/images/google_white2.png" draggable="false" style="position: absolute; left: 0px; top: 0px; width: 62px; height: 26px; -webkit-user-select: none; border: 0px; padding: 0px; margin: 0px;"></div></a></div><div style="padding: 15px 21px; border: 1px solid rgb(171, 171, 171); font-family: Roboto, Arial, sans-serif; color: rgb(34, 34, 34); box-shadow: rgba(0, 0, 0, 0.2) 0px 4px 16px; z-index: 10000002; display: none; width: 256px; height: 148px; position: absolute; left: 344px; top: 83px; background-color: white;"><div style="padding: 0px 0px 10px; font-size: 16px;">Térképadatok</div><div style="font-size: 13px;">Térképadatok ©2015 Google</div><div style="width: 13px; height: 13px; overflow: hidden; position: absolute; opacity: 0.7; right: 12px; top: 12px; z-index: 10000; cursor: pointer;"><img src="https://maps.gstatic.com/mapfiles/api-3/images/mapcnt6.png" draggable="false" style="position: absolute; left: -2px; top: -336px; width: 59px; height: 492px; -webkit-user-select: none; border: 0px; padding: 0px; margin: 0px;"></div></div><div class="gmnoprint" style="z-index: 1000001; position: absolute; right: 269px; bottom: 0px; width: 141px;"><div draggable="false" class="gm-style-cc" style="-webkit-user-select: none;"><div style="opacity: 0.7; width: 100%; height: 100%; position: absolute;"><div style="width: 1px;"></div><div style="width: auto; height: 100%; margin-left: 1px; background-color: rgb(245, 245, 245);"></div></div><div style="position: relative; padding-right: 6px; padding-left: 6px; font-family: Roboto, Arial, sans-serif; font-size: 10px; color: rgb(68, 68, 68); white-space: nowrap; direction: ltr; text-align: right;"><a style="color: rgb(68, 68, 68); text-decoration: none; cursor: pointer; display: none;">Térképadatok</a><span style="">Térképadatok ©2015 Google</span></div></div></div><div class="gmnoscreen" style="position: absolute; right: 0px; bottom: 0px;"><div style="font-family: Roboto, Arial, sans-serif; font-size: 11px; color: rgb(68, 68, 68); direction: ltr; text-align: right; background-color: rgb(245, 245, 245);">Térképadatok ©2015 Google</div></div><div class="gmnoprint gm-style-cc" draggable="false" style="z-index: 1000001; -webkit-user-select: none; position: absolute; right: 116px; bottom: 0px;"><div style="opacity: 0.7; width: 100%; height: 100%; position: absolute;"><div style="width: 1px;"></div><div style="width: auto; height: 100%; margin-left: 1px; background-color: rgb(245, 245, 245);"></div></div><div style="position: relative; padding-right: 6px; padding-left: 6px; font-family: Roboto, Arial, sans-serif; font-size: 10px; color: rgb(68, 68, 68); white-space: nowrap; direction: ltr; text-align: right;"><a href="https://www.google.com/intl/hu-HU_US/help/terms_maps.html" target="_blank" style="text-decoration: none; cursor: pointer; color: rgb(68, 68, 68);">Általános Szerződési Feltételek</a></div></div><div draggable="false" class="gm-style-cc" style="-webkit-user-select: none; position: absolute; right: 0px; bottom: 0px;"><div style="opacity: 0.7; width: 100%; height: 100%; position: absolute;"><div style="width: 1px;"></div><div style="width: auto; height: 100%; margin-left: 1px; background-color: rgb(245, 245, 245);"></div></div><div style="position: relative; padding-right: 6px; padding-left: 6px; font-family: Roboto, Arial, sans-serif; font-size: 10px; color: rgb(68, 68, 68); white-space: nowrap; direction: ltr; text-align: right;"><a target="_new" title="Az úttérkép vagy képek hibáinak bejelentése a Google számára" href="https://www.google.com/maps/@47.4945466,19.057435,15z/data=!10m1!1e1!12b1?source=apiv3&amp;rapsrc=apiv3" style="font-family: Roboto, Arial, sans-serif; font-size: 10px; color: rgb(68, 68, 68); text-decoration: none; position: relative;">Térképhiba bejelentése</a></div></div><div class="gmnoprint" draggable="false" controlwidth="20" controlheight="39" style="margin: 5px; -webkit-user-select: none; position: absolute; left: 0px; top: 0px;"><div class="gmnoprint" controlwidth="0" controlheight="0" style="opacity: 0.6; display: none; position: absolute;"><div title="Térkép elforgatása 90 fokkal" style="width: 22px; height: 22px; overflow: hidden; position: absolute; cursor: pointer;"><img src="https://maps.gstatic.com/mapfiles/api-3/images/mapcnt6.png" draggable="false" style="position: absolute; left: -38px; top: -360px; width: 59px; height: 492px; -webkit-user-select: none; border: 0px; padding: 0px; margin: 0px;"></div></div><div class="gmnoprint" controlwidth="20" controlheight="39" style="position: absolute; left: 0px; top: 0px;"><div style="width: 20px; height: 39px; overflow: hidden; position: absolute;"><img src="https://maps.gstatic.com/mapfiles/api-3/images/mapcnt6.png" draggable="false" style="position: absolute; left: -39px; top: -401px; width: 59px; height: 492px; -webkit-user-select: none; border: 0px; padding: 0px; margin: 0px;"></div><div title="Nagyítás" style="position: absolute; left: 0px; top: 2px; width: 20px; height: 17px; cursor: pointer;"></div><div title="Kicsinyítés" style="position: absolute; left: 0px; top: 19px; width: 20px; height: 17px; cursor: pointer;"></div></div></div></div></div>
                </div>            


      </div>

EOT;


html_end();
mysql_close($link);


?>
