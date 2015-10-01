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
</style>

<script type="text/javascript">
</script>
EOT;

if(isset($_REQUEST['past_start_date'])) {
  $_SESSION['cashflow_past_start_date'] = $_REQUEST['past_start_date'];
}
if(isset($_REQUEST['past_end_date'])) {
  $_SESSION['cashflow_past_end_date'] = $_REQUEST['past_end_date'];
}
if(isset($_REQUEST['future_start_date'])) {
  $_SESSION['cashflow_future_start_date'] = $_REQUEST['future_start_date'];
}
if(isset($_REQUEST['future_end_date'])) {
  $_SESSION['cashflow_future_end_date'] = $_REQUEST['future_end_date'];
}
if(isset($_REQUEST['source'])) {
  $_SESSION['cashflow_source'] = $_REQUEST['source'];
}
if(isset($_REQUEST['time_group'])) {
  $_SESSION['cashflow_time_group'] = $_REQUEST['time_group'];
}
if(!isset($_SESSION['cashflow_past_start_date'])) {
  $_SESSION['cashflow_past_start_date'] = date('Y/m') . '/01';
}
if(!isset($_SESSION['cashflow_past_end_date'])) {
  $_SESSION['cashflow_past_end_date'] = date('Y/m/t');
}
if(!isset($_SESSION['cashflow_future_start_date'])) {
  $_SESSION['cashflow_future_start_date'] = date('Y/m') . '/01';
}
if(!isset($_SESSION['cashflow_future_end_date'])) {
  $_SESSION['cashflow_future_end_date'] = date('Y/m/t');
}
if(!isset($_SESSION['cashflow_time_group'])) {
  $_SESSION['cashflow_time_group'] = 'week';
}
if(!isset($_SESSION['cashflow_source'])) {
  $_SESSION['cashflow_source'] = array();
}

$pastStartDate = $_SESSION['cashflow_past_start_date'];
$pastEndDate = $_SESSION['cashflow_past_end_date'];
$futureStartDate = $_SESSION['cashflow_future_start_date'];
$futureEndDate = $_SESSION['cashflow_future_end_date'];
$timeGroup = $_SESSION['cashflow_time_group'];
$source = $_SESSION['cashflow_source'];

$pastStartDateDash = str_replace('/','-',$pastStartDate);
$pastEndDateDash = str_replace('/','-',$pastEndDate);
$futureStartDateDash = str_replace('/','-',$futureStartDate);
$futureEndDateDash = str_replace('/','-',$futureEndDate);

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


$pastNumOfDays = round((strtotime(str_replace('/', '-', $pastEndDate)) - strtotime(str_replace('/', '-', $pastStartDate))) / (60*60*24)) + 1;
$futureNumOfDays = round((strtotime(str_replace('/', '-', $futureEndDate)) - strtotime(str_replace('/', '-', $futureStartDate))) / (60*60*24)) + 1;


$timeGroupOptions = '';
foreach(array('day','week','month') as $item) {
  $timeGroupOptions .= "      <option value=\"$item\"" . ($item == $timeGroup ? ' selected' : '') . ">$item</option>\n";
}

$sourceOptions = "    <option value=\"\">[ALL]</option>\n";
foreach($SOURCES as $item) {
  $sourceOptions .= "      <option value=\"$item\"" . (in_array($item, $source) ? ' selected' : '') . ">$item</option>\n";
}


$dateVal = '';
if($timeGroup == 'month') {
  $dateVal = 'LEFT(first_night,7)';
} elseif($timeGroup == 'week') {
  $dateVal = 'concat(cast(YEAR(STR_TO_DATE(first_night,\'%Y/%m/%d\')) as char(4)),\'/\',cast(WEEK(STR_TO_DATE(first_night,\'%Y/%m/%d\')) as char(2)))';
} else {
  $dateVal = 'first_night';
}

$whereClause = '';
if(!is_null($source) > 0 and count($source) > 0 and !in_array('', $source)) {
  $whereClause .= " AND source IN ('" . implode("','", $source) . "')";
}

$sqlFindBy = "";

$sql = "SELECT p.currency, p.pay_mode, sum(p.amount) as amount, $dateVal AS date_val FROM booking_descriptions bd INNER JOIN payments p ON bd.id=p.booking_description_id WHERE bd.first_night<='$pastEndDate' AND bd.first_night>='$pastStartDate' AND bd.cancelled<>1 $whereClause GROUP BY p.currency, p.pay_mode, $dateVal";
$result = mysql_query($sql, $link);
$pastTable = array();
if(!$result) {
	trigger_error("Cannot execute query. " . mysql_error($link) . " (SQL: $sql)");
}
while($row = mysql_fetch_assoc($result)) {
  if(!isset($pastTable[$row['pay_mode']])) {
    $pastTable[$row['pay_mode']] = array();
  }
  if(!isset($pastTable[$row['pay_mode']][$row['date_val']])) {
    $pastTable[$row['pay_mode']][$row['date_val']] = array();
  }
  $pastTable[$row['pay_mode']][$row['date_val']][$row['currency']] = $row['amount'];
}

$sql = "SELECT sum(b.room_payment) as amount, $dateVal AS date_val FROM booking_descriptions bd INNER JOIN bookings b ON bd.id=b.description_id WHERE bd.first_night<='$futureEndDate' AND bd.first_night>='$futureStartDate' AND bd.cancelled<>1 $whereClause GROUP BY $dateVal";
$result = mysql_query($sql, $link);
$futureTable = array();
if(!$result) {
	trigger_error("Cannot execute query. " . mysql_error($link) . " (SQL: $sql)");
}
while($row = mysql_fetch_assoc($result)) {
  if(!isset($future[$row['date_val']])) {
    $futureTable[$row['date_val']] = array();
  }
  $futureTable[$row['date_val']] = $row['amount'];
}



mysql_close($link);

html_start("Maverick Admin - Cashflow view", $extraHeader);


echo <<<EOT


<form action="view_past_projected_payments.php" method="POST">
<table>
  <tr><td>Past start date:</td><td>
    <input id="past_start_date" name="past_start_date" size="10" maxlength="10" type="text" value="$pastStartDate"><img src="js/datechooser/calendar.gif" onclick="showChooser(this, 'past_start_date', 'chooserSpanPSD', 2008, 2025, 'Y/m/d', false);">
    <div id="chooserSpanPSD" class="dateChooser select-free" style="display: none; visibility: hidden; width: 160px;"></div>
  </td></tr>
  <tr><td>Past end date:</td><td>
    <input id="past_end_date" name="past_end_date" size="10" maxlength="10" type="text" value="$pastEndDate"><img src="js/datechooser/calendar.gif" onclick="showChooser(this, 'past_end_date', 'chooserSpanPED', 2008, 2025, 'Y/m/d', false);">
    <div id="chooserSpanPED" class="dateChooser select-free" style="display: none; visibility: hidden; width: 160px;"></div>
  </td></tr>
  <tr><td>Furture start date:</td><td>
    <input id="future_start_date" name="future_start_date" size="10" maxlength="10" type="text" value="$futureStartDate"><img src="js/datechooser/calendar.gif" onclick="showChooser(this, 'future_start_date', 'chooserSpanFSD', 2008, 2025, 'Y/m/d', false);">
    <div id="chooserSpanFSD" class="dateChooser select-free" style="display: none; visibility: hidden; width: 160px;"></div>
  </td></tr>
  <tr><td>Future end date:</td><td>
    <input id="future_end_date" name="future_end_date" size="10" maxlength="10" type="text" value="$futureEndDate"><img src="js/datechooser/calendar.gif" onclick="showChooser(this, 'future_end_date', 'chooserSpanFED', 2008, 2025, 'Y/m/d', false);">
    <div id="chooserSpanFED" class="dateChooser select-free" style="display: none; visibility: hidden; width: 160px;"></div>
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
</table>
<input type="submit" value="Generate cashflow view">
</form>

<br><br>

EOT;

if($pastStartDate == $pastEndDate) {
	html_end();
	return;
}

echo <<<EOT

<h2>Past range</h2>
<table>
  <tr><th></th>
EOT;

$pastStartDate = str_replace('/','-',$pastStartDate);
$pastEndDate = str_replace('/','-',$pastEndDate);
$currDate = $pastStartDate;
$dateCols = array();
while($currDate <= $pastEndDate) {
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
	echo "<th style=\"text-align:left;\">$dateCol</th>";
}
echo "</tr>\n";

// Create CASH4 type that is CASH-CASH3
$pastTable['CASH4'] = array();
foreach($dateCols as $dateCol) {
	$dateCol = str_replace('-','/',$dateCol);
	$cash = array();
	$cash3 = array();
	$currencies = array();
	if(isset($pastTable['CASH'][$dateCol])) {
		foreach($pastTable['CASH'][$dateCol] as $curr => $amt) {
			$cash[$curr] = $amt;
			if(!in_array($curr, $currencies)) {
				$currencies[] = $curr;
			}
		}
	}
	if(isset($pastTable['CASH3'][$dateCol])) {
		foreach($pastTable['CASH3'][$dateCol] as $curr => $amt) {
			$cash3[$curr] = $amt;
			if(!in_array($curr, $currencies)) {
				$currencies[] = $curr;
			}
		}
	}
	$pastTable['CASH4'][$dateCol] = array();
	foreach($currencies as $curr) {
		$c = isset($cash[$curr]) ? $cash[$curr] : 0;
		$c3 = isset($cash3[$curr]) ? $cash3[$curr] : 0;
		$pastTable['CASH4'][$dateCol][$curr] = $c - $c3;
	}
}

$pastTotal = array();
$TYPES = array('CASH', 'CASH2', 'CASH3', 'CASH4', 'BANK_TRANSFER', 'CREDIT_CARD');
foreach($TYPES as $type) {
	if(!isset($pastTable[$type])) {
		continue;
	}
	$values = $pastTable[$type];
	echo "  <tr><td style=\"font-weight: bold;border-bottom: 1px solid black;\">$type</td>";
	foreach($dateCols as $dateCol) {
		$dateCol = str_replace('-','/',$dateCol);

		echo "    <td style=\"border-bottom: 1px solid black;\">\n";
		if(isset($values[$dateCol])) {
			foreach($values[$dateCol] as $curr => $amt) {
				if(!isset($pastTotal[$dateCol][$curr])) {
					$pastTotal[$dateCol][$curr] = 0;
				}
				$pastTotal[$dateCol][$curr] += $amt;
				echo "      <div class=\"$curr\" style=\"text-align: right;\">" . number_format($amt) . " $curr</div>\n";
			}
		}
		echo "    </td>\n";
	}
	echo "  </tr>";
}
echo "	<tr><td colspan=\"" . (count($dateCols) + 1) . "\"><hr></td></tr>\n";
echo "  <tr><td style=\"font-weight: bold;\">Totals</td>";
foreach($dateCols as $dateCol) {
	$dateCol = str_replace('-','/',$dateCol);
	echo "    <td>\n";
	if(isset($pastTotal[$dateCol])) {
		foreach($pastTotal[$dateCol] as $curr => $amt) {
			echo "      <div class=\"$curr\" style=\"text-align: right;\">" . number_format($amt) . " $curr</div>\n";
		}
	}
	echo "    </td>\n";
}
echo "</table>\n";


echo <<<EOT

<h2>Future range</h2>
<table>
  <tr>
EOT;

$futureStartDate = str_replace('/','-',$futureStartDate);
$futureEndDate = str_replace('/','-',$futureEndDate);
$currDate = $futureStartDate;
$dateCols = array();
while($currDate <= $futureEndDate) {
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
	echo "<th align=\"left\">$dateCol</th>";
}
echo "</tr>\n";

echo "  <tr>";
foreach($dateCols as $dateCol) {
	echo "    <td>\n";
	if(isset($futureTable[$dateCol])) {
		echo "      <div class=\"$curr\" style=\"text-align: right;\">" . number_format($futureTable[$dateCol]) . " EUR</div>\n";
	}
	echo "    </td>\n";
}
echo "  </tr>";
echo "</table>\n";


html_end();



?>
