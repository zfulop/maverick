<?php

require("includes.php");
require(RECEPCIO_BASE_DIR . "room_booking.php");

$link = db_connect();


$extraHeader = <<<EOT

<script src="js/datechooser/date-functions.js" type="text/javascript"></script>
<script src="js/datechooser/datechooser.js" type="text/javascript"></script>
<link rel="stylesheet" type="text/css" href="js/datechooser/datechooser.css">
<!--[if lte IE 6.5]>
<link rel="stylesheet" type="text/css" href="js/datechooser/select-free.css"/>
<![endif]-->

<script type="text/javascript" src="js/prototype.js"></script>

<style type="text/css">
  .num_of_person {
    background-color: rgb(0,176,240);
  }
  .num_of_booking {
    background-color: rgb(146,208,80);
  }
  .max_daily_rate {
    background-color: rgb(204,192,218);
  }
  .min_daily_rate {
    background-color: rgb(197,190,151);
  }
  .avg_daily_rate {
    background-color: rgb(255,192,0);
  }
  .room_payment {
    background-color: rgb(255,255,0);
  }
</style>

<script type="text/javascript">
  function toggleVisibility(divId,checkboxElement) {
	if(checkboxElement.checked==true) {
		$$(divId).invoke('show');
	} else {
		$$(divId).invoke('hide');
	}
  }
  
  function init() {
	$$("div.num_of_person").invoke('hide');
	$$("div.num_of_booking").invoke('hide');
	$$("div.min_daily_rate").invoke('hide');
	$$("div.max_daily_rate").invoke('hide');
	$$("div.avg_daily_rate").invoke('hide');
  }
</script>
EOT;

if(isset($_REQUEST['find_by'])) {
  $_SESSION['statistics_find_by'] = $_REQUEST['find_by'];
}
if(isset($_REQUEST['include_y2y'])) {
  $_SESSION['statistics_include_y2y'] = true;
} else {
  $_SESSION['statistics_include_y2y'] = false;
}
if(isset($_REQUEST['start_date'])) {
  $_SESSION['statistics_start_date'] = $_REQUEST['start_date'];
}
if(isset($_REQUEST['end_date'])) {
  $_SESSION['statistics_end_date'] = $_REQUEST['end_date'];
}
if(isset($_REQUEST['time_group'])) {
  $_SESSION['statistics_time_group'] = $_REQUEST['time_group'];
}
if(isset($_REQUEST['source'])) {
  $_SESSION['statistics_source'] = $_REQUEST['source'];
}
if(isset($_REQUEST['nationality'])) {
  $_SESSION['statistics_nationality'] = $_REQUEST['nationality'];
}
if(isset($_REQUEST['room_type'])) {
  $_SESSION['statistics_room_type'] = $_REQUEST['room_type'];
}
if(isset($_REQUEST['grouping_1'])) {
  $_SESSION['statistics_grouping_1'] = $_REQUEST['grouping_1'];
}
if(isset($_REQUEST['grouping_2'])) {
  $_SESSION['statistics_grouping_2'] = $_REQUEST['grouping_2'];
}
if(isset($_REQUEST['status'])) {
  $_SESSION['statistics_status'] = $_REQUEST['status'];
}

if(!isset($_SESSION['statistics_find_by'])) {
  $_SESSION['statistics_find_by'] = 'arrival_date';
}
if(!isset($_SESSION['statistics_include_y2y'])) {
  $_SESSION['statistics_include_y2y'] = false;
}
if(!isset($_SESSION['statistics_start_date'])) {
  $_SESSION['statistics_start_date'] = date('Y/m') . '/01';
}
if(!isset($_SESSION['statistics_end_date'])) {
  $_SESSION['statistics_end_date'] = date('Y/m/t');
}
if(!isset($_SESSION['statistics_time_group'])) {
  $_SESSION['statistics_time_group'] = 'week';
}
if(!isset($_SESSION['statistics_source'])) {
  $_SESSION['statistics_source'] = array();
}
if(!isset($_SESSION['statistics_nationality'])) {
  $_SESSION['statistics_nationality'] = array();
}
if(!isset($_SESSION['statistics_room_type'])) {
  $_SESSION['statistics_room_type'] = array();
}
if(!isset($_SESSION['statistics_status'])) {
  $_SESSION['statistics_status'] = array();
}
if(!isset($_SESSION['statistics_grouping_1'])) {
  $_SESSION['statistics_grouping_1'] = 'room_type';
}
if(!isset($_SESSION['statistics_grouping_2'])) {
  $_SESSION['statistics_grouping_2'] = '';
}

$findBy = $_SESSION['statistics_find_by'];
$includeY2y = $_SESSION['statistics_include_y2y'];
$startDate = $_SESSION['statistics_start_date'];
$endDate = $_SESSION['statistics_end_date'];
$timeGroup = $_SESSION['statistics_time_group'];
$source = $_SESSION['statistics_source'];
$nationality = $_SESSION['statistics_nationality'];
$roomType = $_SESSION['statistics_room_type'];
$status = $_SESSION['statistics_status'];
$grouping1 = $_SESSION['statistics_grouping_1'];
$grouping2 = $_SESSION['statistics_grouping_2'];

$startDateDash = str_replace('/','-',$startDate);
$endDateDash = str_replace('/','-',$endDate);

$SOURCES = array();
$sql = "SELECT * FROM sources ORDER BY source";
$result = mysql_query($sql, $link);
if(!$result) {
  trigger_error("Cannot get sources in admin interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
} else {
  while($row = mysql_fetch_assoc($result)) {
    $SOURCES[] = $row['source'];
  }
}

$NATIONALITIES = array();
$sql = "SELECT nationality, COUNT(*) FROM booking_descriptions GROUP BY nationality ORDER BY COUNT(*) DESC";
$result = mysql_query($sql, $link);
while($row = mysql_fetch_assoc($result)) {
  $NATIONALITIES[] = $row['nationality'];
}
$NATIONALITIES[] = '----------';
$countries = file_get_contents(RECEPCIO_BASE_DIR . 'includes/countries.txt');
foreach(explode("\n", $countries) as $cntry) {
  $cntry = trim($cntry);
  if(strlen($cntry) < 1)
    continue;

  $NATIONALITIES[] = $cntry;
}

$ROOM_TYPES = array();
$sql = "SELECT rt.* FROM room_types rt order by rt.name";
$result = mysql_query($sql, $link);
while($row = mysql_fetch_assoc($result)) {
  $ROOM_TYPES[$row['id']] = $row;
}

$GROUPING_OPTIONS = array('room_type' => 'Room type','source' => 'Source','nationality' => 'Nationality','status' => 'Status','special_offer' => 'Sepcial Offer');

$STATUS = array('created','no show','cancelled','confirmed','checked in','paid','maintenance');


$numOfDays = round((strtotime(str_replace('/', '-', $endDate)) - strtotime(str_replace('/', '-', $startDate))) / (60*60*24)) + 1;

$FINDBY = array('arrival_date' => 'Arrival date', 'booking_date' => 'Booking date');
$findByOptions = '';
foreach($FINDBY as $key => $descr) {
	$findByOptions .= "<input type=\"radio\" name=\"find_by\" value=\"$key\"" . ($findBy == $key ? " checked" : "") . "> $descr<br>";
}

$includeY2yChecked = ($includeY2y ? ' checked' : '');

$timeGroupOptions = '';
foreach(array('day','week','month') as $item) {
  $timeGroupOptions .= "      <option value=\"$item\"" . ($item == $timeGroup ? ' selected' : '') . ">$item</option>\n";
}

$sourceOptions = "    <option value=\"\">[ALL]</option>\n";
foreach($SOURCES as $item) {
  $sourceOptions .= "      <option value=\"$item\"" . (in_array($item, $source) ? ' selected' : '') . ">$item</option>\n";
}

$nationalityOptions = "    <option value=\"\">[ALL]</option>\n";
foreach($NATIONALITIES as $item) {
  $nationalityOptions .= "      <option value=\"$item\"" . (in_array($item, $nationality) ? ' selected' : '') . ">$item</option>\n";
}

$roomTypeOptions = "    <option value=\"\">[ALL]</option>\n";
foreach($ROOM_TYPES as $id => $row) {
  $roomTypeOptions .= "      <option value=\"$id\"" . (in_array($id, $roomType) ? ' selected' : '') . ">" . $row['name'] . "</option>\n";
}

$statusOptions = "    <option value=\"\">[ALL]</option>\n";
foreach($STATUS as $item) {
  $statusOptions .= "      <option value=\"$item\"" . (in_array($item, $status) ? ' selected' : '') . ">$item</option>\n";
}

$grouping1Options = "";
foreach($GROUPING_OPTIONS as $key => $value) {
  $grouping1Options .= "      <option value=\"$key\"" . ($key == $grouping1 ? ' selected' : '') . ">$value</option>\n";
}

$grouping2Options = "    <option value=\"\">---</option>\n";
foreach($GROUPING_OPTIONS as $item) {
  $grouping2Options .= "      <option value=\"$item\"" . ($item == $grouping2 ? ' selected' : '') . ">$item</option>\n";
}



$dateVal = '';
if($timeGroup == 'month') {
  $dateVal = 'LEFT(first_night,7)';
} elseif($timeGroup == 'week') {
  $dateVal = 'concat(cast(YEAR(STR_TO_DATE(first_night,\'%Y/%m/%d\')) as char(4)),\'/\',cast(WEEK(STR_TO_DATE(first_night,\'%Y/%m/%d\')) as char(2)))';
} else {
  $dateVal = 'first_night';
}
$sqlGroupBy = '';
$sqlColumns = '';
if(strlen($grouping1) > 0) {
  $sqlGroupBy .= ",$grouping1";
  if($grouping1 == 'status') {
    $sqlColumns .= ",case when bd.maintenance=1 then 'maintenance' when bd.cancelled=1 and bd.comment like 'no show%' then 'cancelled' when bd.cancelled=1 then 'cancelled' when bd.paid=1 then 'paid' when bd.checked_in=1 then 'checked in' when bd.confirmed=1 then 'confirmed' else 'created' end as status";
  } elseif($grouping1 == 'special_offer') {
    $sqlColumns .= ",CONCAT(so.name, ' ',so.start_date,'-',so.end_date) AS special_offer";
  } elseif($grouping1 == 'room_type') {
    $sqlColumns .= ',rt.name AS room_type';
  } else {
    $sqlColumns .= ',' . $grouping1;
  }
}
if(strlen($grouping2) > 0 and $grouping1 != $grouping2) {
  $sqlGroupBy .= ",$grouping2";
  if($grouping2 == 'status') {
    $sqlColumns .= ",case when bd.maintenance=1 then 'maintenance' when bd.cancelled=1 and bd.comment like 'no show%' then 'cancelled' when bd.cancelled=1 then 'cancelled' when bd.paid=1 then 'paid' when bd.checked_in=1 then 'checked in' when bd.confirmed=1 then 'confirmed' else 'created' end as status";
  } elseif($grouping2 == 'special_offer') {
    $sqlColumns .= ",CONCAT(so.name, ' ',so.start_date,'-',so.end_date) AS special_offer";
  } elseif($grouping2 == 'room_type') {
    $sqlColumns .= ',rt.name AS room_type';
  } else {
    $sqlColumns .= ',' . $grouping2;
  }
}

$whereClause = '';
if(!is_null($source) > 0 and count($source) > 0 and !in_array('', $source)) {
  $whereClause .= " AND source IN ('" . implode("','", $source) . "')";
}
if(!is_null($nationality) > 0 and count($nationality) > 0 and !in_array('', $nationality)) {
  $whereClause .= " AND nationality IN ('" . implode("','", $nationality) . "')";
}
if(!is_null($roomType) > 0 and count($roomType) > 0 and !in_array('', $roomType)) {
  $whereClause .= " AND rt.id IN (" . implode(",", $roomType) . ")";
}
if(!is_null($status) > 0 and count($status) > 0 and !in_array('', $status)) {
	$whereClause .= " AND (";
	$first = true;
	foreach($status as $oneStatus) {
		if($oneStatus=='created') {
			if($first) {
				$first = false;
			} else {
				$whereClause .= ' OR ';
			}
			$whereClause .= '(cancelled=0 AND paid=0 AND checked_in=0 AND confirmed=0 AND maintenance=0)';
		} elseif($oneStatus=='no show') {
			if($first) {
				$first = false;
			} else {
				$whereClause .= ' OR ';
			}
			$whereClause .= '(cancelled=1 AND comment LIKE \'no show%\')';
		} elseif($oneStatus=='cancelled') {
			if($first) {
				$first = false;
			} else {
				$whereClause .= ' OR ';
			}
			$whereClause .= '(cancelled=1 AND comment NOT LIKE \'no show%\')';
		} elseif($oneStatus=='confirmed') {
			if($first) {
				$first = false;
			} else {
				$whereClause .= ' OR ';
			}
			$whereClause .= 'confirmed=1';
		} elseif($oneStatus=='checked in') {
			if($first) {
				$first = false;
			} else {
				$whereClause .= ' OR ';
			}
			$whereClause .= 'checked_in=1';
		} elseif($oneStatus=='paid') {
			if($first) {
				$first = false;
			} else {
				$whereClause .= ' OR ';
			}
			$whereClause .= 'paid=1';
		} elseif($oneStatus=='maintenance') {
			if($first) {
				$first = false;
			} else {
				$whereClause .= ' OR ';
			}
			$whereClause .= 'maintenance=1';
		}
	}
	$whereClause .= ")";
}

$sqlFindBy = '';
if($findBy == 'arrival_date') {
	$sqlFindBy = "bd.first_night<='$endDate' AND bd.first_night>='$startDate'";
} else {
	$sqlFindBy = "bd.create_time<='$endDateDash 23:59:59' AND bd.create_time>='$startDateDash 00:00:00'";
}

if(isset($_REQUEST['export'])) {
	$sql = "SELECT bd.name,bd.email,bd.first_night,bd.last_night,bd.cancelled,bd.confirmed,bd.checked_in,bd.paid,bd.source,b.num_of_person,b.booking_type,b.room_payment,rt.name as room_name,so.name as so_name FROM booking_descriptions bd INNER JOIN bookings b ON bd.id=b.description_id INNER JOIN rooms r on b.room_id=r.id INNER JOIN room_types rt on r.room_type_id=rt.id LEFT OUTER JOIN special_offers so on b.special_offer_id=so.id WHERE $sqlFindBy $whereClause";
	$result = mysql_query($sql, $link);
	if(!$result) {
		trigger_error("Cannot execute query. " . mysql_error($link) . " (SQL: $sql)");
	}
	exportToExcel($result);
	mysql_close($link);
	return;
}

$sql = "SELECT sum(b.num_of_person) as num_of_person, sum(b.room_payment) as room_payment, avg(room_payment/num_of_nights) as avg_daily_rate, min(room_payment/num_of_nights) as min_daily_rate, max(room_payment/num_of_nights) as max_daily_rate, count(*) as num_of_booking, $dateVal AS date_val$sqlColumns FROM booking_descriptions bd INNER JOIN bookings b ON bd.id=b.description_id INNER JOIN rooms r on b.room_id=r.id INNER JOIN room_types rt on r.room_type_id=rt.id LEFT OUTER JOIN special_offers so on b.special_offer_id=so.id WHERE $sqlFindBy $whereClause GROUP BY $dateVal$sqlGroupBy";
$result = mysql_query($sql, $link);
$table = array();
if(!$result) {
	trigger_error("Cannot execute query. " . mysql_error($link) . " (SQL: $sql)");
}
while($row = mysql_fetch_assoc($result)) {
  if(!isset($table[$row[$grouping1]])) {
    $table[$row[$grouping1]] = array();
  }
  if(isset($table[$row[$grouping1]][$row['date_val']])) {
    $table[$row[$grouping1]][$row['date_val']]['num_of_person'] += $row['num_of_person'];
    $table[$row[$grouping1]][$row['date_val']]['room_payment'] += $row['room_payment'];
    $table[$row[$grouping1]][$row['date_val']]['num_of_booking'] += $row['num_of_booking'];
  } else {
    $table[$row[$grouping1]][$row['date_val']] = $row;
  }
  if($grouping2 <> '') {
    if(!isset($table[$row[$grouping1]]['subgrouping'])) {
      $table[$row[$grouping1]]['subgrouping'] = array();
    }
    if(!isset($table[$row[$grouping1]]['subgrouping'][$row[$grouping2]])) {
      $table[$row[$grouping1]]['subgrouping'][$row[$grouping2]] = array();
    }
    $table[$row[$grouping1]]['subgrouping'][$row[$grouping2]][$row['date_val']] = $row;
  }
}


$tableYearBefore = array();
$sql2 = $sql;
if($includeY2y) {
	$startDateYearBefore = date('Y/m/d', strtotime($startDateDash . ' -1 year'));
	$startDateDashYearBefore = str_replace('/','-',$startDateYearBefore);
	$endDateYearBefore = date('Y/m/d', strtotime($endDateDash . ' -1 year'));
	$endDateDashYearBefore = str_replace('/','-',$endDateYearBefore);
	$sql2 = str_replace($startDate, $startDateYearBefore, $sql2);
	$sql2 = str_replace($endDate, $endDateYearBefore, $sql2);
	$sql2 = str_replace($startDateDash, $startDateDashYearBefore, $sql2);
	$sql2 = str_replace($endDateDash, $endDateDashYearBefore, $sql2);
	$result = mysql_query($sql2, $link);
	if(!$result) {
		trigger_error("Cannot execute query. " . mysql_error($link) . " (SQL: $sql)");
	}
	while($row = mysql_fetch_assoc($result)) {
	  if(!isset($tableYearBefore[$row[$grouping1]])) {
		$tableYearBefore[$row[$grouping1]] = array();
	  }
	  if(isset($tableYearBefore[$row[$grouping1]][$row['date_val']])) {
		$tableYearBefore[$row[$grouping1]][$row['date_val']]['num_of_person'] += $row['num_of_person'];
		$tableYearBefore[$row[$grouping1]][$row['date_val']]['room_payment'] += $row['room_payment'];
		$tableYearBefore[$row[$grouping1]][$row['date_val']]['num_of_booking'] += $row['num_of_booking'];
	  } else {
		$tableYearBefore[$row[$grouping1]][$row['date_val']] = $row;
	  }
	  if($grouping2 <> '') {
		if(!isset($tableYearBefore[$row[$grouping1]]['subgrouping'])) {
		  $tableYearBefore[$row[$grouping1]]['subgrouping'] = array();
		}
		if(!isset($tableYearBefore[$row[$grouping1]]['subgrouping'][$row[$grouping2]])) {
		  $tableYearBefore[$row[$grouping1]]['subgrouping'][$row[$grouping2]] = array();
		}
		$tableYearBefore[$row[$grouping1]]['subgrouping'][$row[$grouping2]][$row['date_val']] = $row;
	  }
	}
}

mysql_close($link);

html_start("Maverick Admin - Statistics", $extraHeader, 'init();');


echo <<<EOT


<form action="view_statistics.php" method="POST">
<table>
  <tr><td>Find by: </td><td>$findByOptions</td></tr>
  <tr><td>Include Y2Y comparison</td><td><input type="checkbox" name="include_y2y" value="true"$includeY2yChecked></td></tr>
  <tr><td>Start date:</td><td>
    <input id="start_date" name="start_date" size="10" maxlength="10" type="text" value="$startDate"><img src="js/datechooser/calendar.gif" onclick="showChooser(this, 'start_date', 'chooserSpanSD', 2008, 2025, 'Y/m/d', false);">
    <div id="chooserSpanSD" class="dateChooser select-free" style="display: none; visibility: hidden; width: 160px;"></div>
  </td></tr>
  <tr><td>End date:</td><td>
    <input id="end_date" name="end_date" size="10" maxlength="10" type="text" value="$endDate"><img src="js/datechooser/calendar.gif" onclick="showChooser(this, 'end_date', 'chooserSpanED', 2008, 2025, 'Y/m/d', false);">
    <div id="chooserSpanED" class="dateChooser select-free" style="display: none; visibility: hidden; width: 160px;"></div>
  </td></tr>
  <tr><td>Time group:</td><td>
    <select name="time_group">
$timeGroupOptions
    </select>
  </td></tr>
  <tr><td>Source:</td><td>
  <select name="source[]" multiple="yes" size="6" style="height: 80px;">
$sourceOptions
    </select>
  </td></tr>
  <tr><td>Nationality:</td><td>
    <select name="nationality[]" multiple="yes" size="6" style="height: 80px;">
$nationalityOptions
    </select>
  </td></tr>
  <tr><td>Status:</td><td>
    <select name="status[]" multiple="yes" size="6" style="height: 80px;">
$statusOptions
    </select>
  </td></tr>
  <tr><td>Grouping 1:</td><td>
    <select name="grouping_1">
$grouping1Options
    </select>
  </td></tr>
  <tr><td>Grouping 2:</td><td>
    <select name="grouping_2">
$grouping2Options
    </select>
  </td></tr>

</table>
<input type="checkbox" name="export" value="1"> Export to excel<br>
<input type="submit" value="Generate Statistics">
</form>

<br>
<form style="display: block;">
  <span class="room_payment" style="padding: 5px 10px;width:160px;display:block;margin-bottom:3px;"><input type="checkbox" checked="true" onclick="toggleVisibility('div.room_payment', this);"> Room payment</span>
  <span class="num_of_booking" style="padding: 5px 10px;width:160px;display:block;margin-bottom:3px;"><input type="checkbox" onclick="toggleVisibility('div.num_of_booking', this);"> Number of bookings</span>
  <span class="num_of_person" style="padding: 5px 10px;width:160px;display:block;margin-bottom:3px;"><input type="checkbox" onclick="toggleVisibility('div.num_of_person', this);"> Number of person</span>
  <span class="avg_daily_rate" style="padding: 5px 10px;width:160px;display:block;margin-bottom:3px;"><input type="checkbox" onclick="toggleVisibility('div.avg_daily_rate', this);"> Average daily rate</span>
  <span class="min_daily_rate" style="padding: 5px 10px;width:160px;display:block;margin-bottom:3px;"><input type="checkbox" onclick="toggleVisibility('div.min_daily_rate', this);"> Minimum daily rate</span>
  <span class="max_daily_rate" style="padding: 5px 10px;width:160px;display:block;margin-bottom:3px;"><input type="checkbox" onclick="toggleVisibility('div.max_daily_rate', this);"> Maximum daily rate</span>
</form>


EOT;

if($startDate == $endDate) {
	html_end();
	return;
}

echo <<<EOT

<table>
  <tr><th></th>
EOT;

$totals = array();
$totalsYearBefore = array();
$startDate = str_replace('/','-',$startDate);
$endDate = str_replace('/','-',$endDate);
$currDate = $startDate;
$dateCols = array();
while($currDate <= $endDate) {
	$dateCol = '';
	if($timeGroup == 'day') {
		$dateCol = $currDate;
		$currDate = date('Y-m-d', strtotime($currDate . " +1 day"));
	} elseif($timeGroup == 'week') {
		$dateCol = substr($currDate,0,4) . '/' . date('W',strtotime($currDate));
		$dateCol = str_replace('/0', '/', $dateCol);
		$currDate = date('Y-m-d', strtotime($currDate . " +7 day"));
	} else { // month
		$dateCol = substr($currDate, 0, 7);
		$currDate = date('Y-m-d', strtotime($currDate . " +1 month"));
	}
	$dateCol = str_replace('-','/',$dateCol);
	$dateCols[] = $dateCol;
	$totals[$dateCol] = array('room_payment' => 0, 'num_of_booking' => 0, 'num_of_person' => 0);
	$totalsYearBefore[$dateCol] = array('room_payment' => 0, 'num_of_booking' => 0, 'num_of_person' => 0);
	echo "<th align=\"left\">$dateCol</th>";
}
echo "</tr>\n";

foreach($table as $grouping1 => $values) {
	echo "  <tr><td style=\"font-weight: bold;\">$grouping1</td>";
	foreach($dateCols as $dateCol) {
		$dateColYearBefore = (intval(substr($dateCol,0,4))-1) . substr($dateCol,4);
		$dateCol = str_replace('-','/',$dateCol);
		$dateColYearBefore = str_replace('-','/',$dateColYearBefore);

		$roomPayment = 0;
		$numOfBooking = 0;
		$numOfPerson = 0;
		$avgDailyRate = 0;
		$minDailyRate = 0;
		$maxDailyRate = 0;
		$roomPaymentYB = 0;
		$numOfBookingYB = 0;
		$numOfPersonYB = 0;
		$avgDailyRateYB = 0;
		$minDailyRateYB = 0;
		$maxDailyRateYB = 0;

		if(isset($values[$dateCol])) {
			$roomPayment = $values[$dateCol]['room_payment'];
			$numOfBooking = $values[$dateCol]['num_of_booking'];
			$numOfPerson = $values[$dateCol]['num_of_person'];
			$avgDailyRate = $values[$dateCol]['avg_daily_rate'];
			$minDailyRate = $values[$dateCol]['min_daily_rate'];
			$maxDailyRate = $values[$dateCol]['max_daily_rate'];
		}

		if(isset($tableYearBefore[$grouping1][$dateColYearBefore])) {
			$roomPaymentYB = $tableYearBefore[$grouping1][$dateColYearBefore]['room_payment'];
			$numOfBookingYB = $tableYearBefore[$grouping1][$dateColYearBefore]['num_of_booking'];
			$numOfPersonYB = $tableYearBefore[$grouping1][$dateColYearBefore]['num_of_person'];
			$avgDailyRateYB = $tableYearBefore[$grouping1][$dateColYearBefore]['avg_daily_rate'];
			$minDailyRateYB = $tableYearBefore[$grouping1][$dateColYearBefore]['min_daily_rate'];
			$maxDailyRateYB = $tableYearBefore[$grouping1][$dateColYearBefore]['max_daily_rate'];
		}

		$totals[$dateCol]['room_payment'] = $totals[$dateCol]['room_payment'] + $roomPayment;
		$totals[$dateCol]['num_of_booking'] = $totals[$dateCol]['num_of_booking'] + $numOfBooking;
		$totals[$dateCol]['num_of_person'] = $totals[$dateCol]['num_of_person'] + $numOfPerson;
		$totalsYearBefore[$dateCol]['room_payment'] = $totalsYearBefore[$dateCol]['room_payment'] + $roomPaymentYB;
		$totalsYearBefore[$dateCol]['num_of_booking'] = $totalsYearBefore[$dateCol]['num_of_booking'] + $numOfBookingYB;
		$totalsYearBefore[$dateCol]['num_of_person'] = $totalsYearBefore[$dateCol]['num_of_person'] + $numOfPersonYB;
		echo "    <td style=\"font-weight: bold;\">\n";
		echo "      <table><tr>\n";
		echo "        <td><div class=\"num_of_person\">" . $numOfPerson . ($includeY2y ? '/' . $numOfPersonYB : '') . "</div></td>\n";
		echo "        <td><div class=\"num_of_booking\">" . $numOfBooking . ($includeY2y ? '/' . $numOfBookingYB : '') . "</div></td>\n";
		echo "        <td><div class=\"room_payment\">" . sprintf("%.1f",$roomPayment) . "&#8364;" . ($includeY2y ? '/' . sprintf("%.1f",$roomPaymentYB) . '&#8364;' : '') . "</div></td>\n";
		echo "        <td><div class=\"avg_daily_rate\">" . sprintf("%.1f",$avgDailyRate) . "&#8364;"  . ($includeY2y ? '/' . sprintf("%.1f",$avgDailyRateYB) . '&#8364;' : '') . "</div></td>\n";
		echo "        <td><div class=\"min_daily_rate\">" . sprintf("%.1f", $minDailyRate) . "&#8364;" . ($includeY2y ? '/' . sprintf("%.1f",$minDailyRate) . '&#8364;' : '') . "</div></td>\n";
		echo "        <td><div class=\"max_daily_rate\">" . sprintf("%.1f", $maxDailyRate) . "&#8364;" . ($includeY2y ? '/' . sprintf("%.1f",$maxDailyRate) . '&#8364;' : '') . "</div></td>\n";
		echo "      </tr></table>\n";
		echo "    </td>\n";
	}
	echo "  </tr>";
	if(isset($values['subgrouping'])) {
		foreach($values['subgrouping'] as $grouping2 => $subvalues) {
			echo "  <tr><td style=\"padding-left: 10px;\">$grouping2</td>";
			foreach($dateCols as $dateCol) {
				$dateColYearBefore = (intval(substr($dateCol,0,4))-1) . substr($dateCol,4);
				$dateCol = str_replace('-','/',$dateCol);
				$dateColYearBefore = str_replace('-','/',$dateColYearBefore);

				$roomPayment = 0;
				$numOfBooking = 0;
				$numOfPerson = 0;
				$avgDailyRate = 0;
				$minDailyRate = 0;
				$maxDailyRate = 0;
				$roomPaymentYB = 0;
				$numOfBookingYB = 0;
				$numOfPersonYB = 0;
				$avgDailyRateYB = 0;
				$minDailyRateYB = 0;
				$maxDailyRateYB = 0;

				if(isset($subvalues[$dateCol])) {
					$roomPayment = $subvalues[$dateCol]['room_payment'];
					$numOfBooking = $subvalues[$dateCol]['num_of_booking'];
					$numOfPerson = $subvalues[$dateCol]['num_of_person'];
					$avgDailyRate = $subvalues[$dateCol]['avg_daily_rate'];
					$minDailyRate = $subvalues[$dateCol]['min_daily_rate'];
					$maxDailyRate = $subvalues[$dateCol]['max_daily_rate'];
				}

				if(isset($tableYearBefore[$grouping1][$dateColYearBefore]['subgrouping'][$grouping2][$dateColYearBefore])) {
					$roomPaymentYB = $tableYearBefore[$grouping1]['subgrouping'][$grouping2][$dateColYearBefore]['room_payment'];
					$numOfBookingYB = $tableYearBefore[$grouping1]['subgrouping'][$grouping2][$dateColYearBefore]['num_of_booking'];
					$numOfPersonYB = $tableYearBefore[$grouping1]['subgrouping'][$grouping2][$dateColYearBefore]['num_of_person'];
					$avgDailyRateYB = $tableYearBefore[$grouping1]['subgrouping'][$grouping2][$dateColYearBefore]['avg_daily_rate'];
					$minDailyRateYB = $tableYearBefore[$grouping1]['subgrouping'][$grouping2][$dateColYearBefore]['min_daily_rate'];
					$maxDailyRateYB = $tableYearBefore[$grouping1]['subgrouping'][$grouping2][$dateColYearBefore]['max_daily_rate'];
				}


				echo "    <td style=\"font-weight: bold;\">\n";
				echo "      <table><tr>\n";
				echo "        <td><div class=\"num_of_person\">" . $numOfPerson . ($includeY2y ? '/' . $numOfPersonYB : '') . "</div></td>\n";
				echo "        <td><div class=\"num_of_booking\">" . $numOfBooking . ($includeY2y ? '/' . $numOfBookingYB : '') . "</div></td>\n";
				echo "        <td><div class=\"room_payment\">" . sprintf("%.1f",$roomPayment) . "&#8364;" . ($includeY2y ? '/' . sprintf("%.1f",$roomPaymentYB) . '&#8364;' : '') . "</div></td>\n";
				echo "        <td><div class=\"avg_daily_rate\">" . sprintf("%.1f",$avgDailyRate) . "&#8364;"  . ($includeY2y ? '/' . sprintf("%.1f",$avgDailyRateYB) . '&#8364;' : '') . "</div></td>\n";
				echo "        <td><div class=\"min_daily_rate\">" . sprintf("%.1f", $minDailyRate) . "&#8364;" . ($includeY2y ? '/' . sprintf("%.1f",$minDailyRateYB) . '&#8364;' : '') . "</div></td>\n";
				echo "        <td><div class=\"max_daily_rate\">" . sprintf("%.1f", $maxDailyRate) . "&#8364;" . ($includeY2y ? '/' . sprintf("%.1f",$maxDailyRateYB) . '&#8364;' : '') . "</div></td>\n";
				echo "      </tr></table>\n";
				echo "    </td>\n";


			}
			echo "  </tr>";
		}
	}
}
echo "	<tr><td colspan=\"" . (count($dateCols) + 1) . "\"><hr></td></tr>\n";
echo "  <tr><td style=\"font-weight: bold;\">Totals</td>";
foreach($dateCols as $dateCol) {
	$dateCol = str_replace('-','/',$dateCol);
	echo "    <td>\n";
	echo "      <table><tr>\n";
	echo "        <td><div class=\"num_of_person\">" . $totals[$dateCol]['num_of_person'] . ($includeY2y ? '/' . $totalsYearBefore[$dateCol]['num_of_person'] : '') . "</div></td>\n";
	echo "        <td><div class=\"num_of_booking\">" . $totals[$dateCol]['num_of_booking'] . ($includeY2y ? '/' . $totalsYearBefore[$dateCol]['num_of_booking'] : '') . "</div></td>\n";
	echo "        <td><div class=\"room_payment\">" . sprintf("%.1f",$totals[$dateCol]['room_payment']) . "&#8364;" . ($includeY2y ? '/' . $totalsYearBefore[$dateCol]['room_payment'] . '&#8364;' : '') . "</div></td>\n";
	echo "      </tr></table>\n";
	echo "    </td>\n";
}
echo "</table>\n";

html_end();


function exportToExcel($result) {
	header('Content-Type: text/csv');
	header('Content-Disposition: attachment; filename="stat_export.csv"');
	echo "name,email,first night,last night,cancelled,confirmed,checked in,paid,source,num of person,booking type,room payment,room name,special offer\n";
	while($row = mysql_fetch_array($result, MYSQL_NUM)) {
		if(strpos($row[0],',') > 0) {
			$row[0] = str_replace(',', ' ', $row[0]);
		}
		echo implode(',',$row) . "\n";
	}
}


?>
