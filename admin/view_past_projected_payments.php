<?php

require("includes.php");
require(RECEPCIO_BASE_DIR . "room_booking.php");

if(!checkLogin(SITE_ADMIN)) {
	return;
}

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
  $_SESSION['cashflow_past_start_date'] = str_replace('-','/',$_REQUEST['past_start_date']);
}
if(isset($_REQUEST['past_end_date'])) {
  $_SESSION['cashflow_past_end_date'] = str_replace('-','/',$_REQUEST['past_end_date']);
}
if(isset($_REQUEST['past_date_by'])) {
  $_SESSION['cashflow_past_date_by'] = $_REQUEST['past_date_by'];
}
if(isset($_REQUEST['future_start_date'])) {
  $_SESSION['cashflow_future_start_date'] = str_replace('-','/',$_REQUEST['future_start_date']);
}
if(isset($_REQUEST['future_end_date'])) {
  $_SESSION['cashflow_future_end_date'] = str_replace('-','/',$_REQUEST['future_end_date']);
}
if(isset($_REQUEST['source'])) {
  $_SESSION['cashflow_source'] = $_REQUEST['source'];
}
if(isset($_REQUEST['type'])) {
  $_SESSION['cashflow_type'] = $_REQUEST['type'];
}
if(isset($_REQUEST['time_group'])) {
  $_SESSION['cashflow_time_group'] = $_REQUEST['time_group'];
}

if(!isset($_SESSION['cashflow_past_date_by'])) {
  $_SESSION['cashflow_past_date_by'] = 'payment';
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
if(!isset($_SESSION['cashflow_type'])) {
  $_SESSION['cashflow_type'] = array();
}

$pastStartDate = $_SESSION['cashflow_past_start_date'];
$pastEndDate = $_SESSION['cashflow_past_end_date'];
$futureStartDate = $_SESSION['cashflow_future_start_date'];
$futureEndDate = $_SESSION['cashflow_future_end_date'];
$timeGroup = $_SESSION['cashflow_time_group'];
$source = $_SESSION['cashflow_source'];
$type = $_SESSION['cashflow_type'];

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

$TYPES = array();
$sql = "SELECT DISTINCT type FROM payments ORDER BY type";
$result = mysql_query($sql, $link);
if(!$result) {
  trigger_error("Cannot get types from payments in admin interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
} else {
  while($row = mysql_fetch_assoc($result)) {
    $TYPES[] = $row['type'];
  }
}



$pastNumOfDays = round((strtotime($pastEndDateDash) - strtotime($pastStartDateDash)) / (60*60*24)) + 1;
$futureNumOfDays = round((strtotime($futureEndDateDash) - strtotime($futureStartDateDash)) / (60*60*24)) + 1;


$timeGroupOptions = '';
foreach(array('day','week','month') as $item) {
  $timeGroupOptions .= "      <option value=\"$item\"" . ($item == $timeGroup ? ' selected' : '') . ">$item</option>\n";
}

$sourceOptions = "    <option value=\"\">[ALL]</option>\n";
foreach($SOURCES as $item) {
  $sourceOptions .= "      <option value=\"$item\"" . (in_array($item, $source) ? ' selected' : '') . ">$item</option>\n";
}

$typeOptions = "    <option value=\"\">[ALL]</option>\n";
foreach($TYPES as $item) {
  $typeOptions .= "      <option value=\"$item\"" . (in_array($item, $type) ? ' selected' : '') . ">$item</option>\n";
}



$dateVal = '';
if($timeGroup == 'month') {
  $dateVal = 'LEFT(first_night,7)';
} elseif($timeGroup == 'week') {
  $dateVal = 'concat(cast(YEAR(STR_TO_DATE(first_night,\'%Y/%m/%d\')) as char(4)),\'/\',cast(WEEK(STR_TO_DATE(first_night,\'%Y/%m/%d\')) as char(2)))';
} else {
  $dateVal = 'first_night';
}

$pastWhereClause = '';
$futureWhereClause = '';
if(!is_null($source) > 0 and count($source) > 0 and !in_array('', $source)) {
  $pastWhereClause .= " AND source IN ('" . implode("','", $source) . "')";
  $futureWhereClause .= " AND source IN ('" . implode("','", $source) . "')";
}
if(!is_null($type) > 0 and count($type) > 0 and !in_array('', $type)) {
  $pastWhereClause .= " AND p.type IN ('" . implode("','", $type) . "')";
}

$sqlFindBy = "";

$pastDateVal = $dateVal;
$pastEndDateComp = $pastEndDate;
$pastStartDateComp = $pastStartDate;
if($_SESSION['cashflow_past_date_by'] == 'arrive') {
	$dateCol = 'bd.first_night';
} else {
	$dateCol = 'SUBSTR(time_of_payment,1,10)';
	$pastEndDateComp = $pastEndDateDash;
	$pastStartDateComp = $pastStartDateDash;
	if($timeGroup == 'month') {
		$pastDateVal = 'LEFT(SUBSTR(time_of_payment,1,10),7)';
	} elseif($timeGroup == 'week') {
		$pastDateVal = 'concat(cast(YEAR(STR_TO_DATE(SUBSTR(time_of_payment,1,10),\'%Y-%m-%d\')) as char(4)),\'/\',cast(WEEK(STR_TO_DATE(SUBSTR(time_of_payment,1,10),\'%Y-%m-%d\')) as char(2)))';
	} else {
		$pastDateVal = 'SUBSTR(time_of_payment,1,10)';
	}
}

$sql = "SELECT p.currency, p.pay_mode, sum(p.amount) as amount, $pastDateVal AS date_val FROM booking_descriptions bd RIGHT OUTER JOIN payments p ON bd.id=p.booking_description_id WHERE $dateCol<='$pastEndDateComp' AND $dateCol>='$pastStartDateComp' AND bd.cancelled<>1 AND p.storno<>1 $pastWhereClause GROUP BY p.currency, p.pay_mode, $pastDateVal";


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

$sql = "SELECT sum(b.room_payment) as amount, $dateVal AS date_val FROM booking_descriptions bd INNER JOIN bookings b ON bd.id=b.description_id WHERE bd.first_night<='$futureEndDate' AND bd.first_night>='$futureStartDate' AND bd.cancelled<>1 AND bd.maintenance<>1 $futureWhereClause GROUP BY $dateVal";
$result = mysql_query($sql, $link);
$futureTable = array();
if(!$result) {
	trigger_error("Cannot execute query. " . mysql_error($link) . " (SQL: $sql)");
}
while($row = mysql_fetch_assoc($result)) {
  $futureTable[$row['date_val']] = $row['amount'];
}


$sql = "SELECT sum(p.amount) as amount, p.currency, $dateVal AS date_val FROM booking_descriptions bd INNER JOIN payments p ON bd.id=p.booking_description_id WHERE bd.first_night<='$futureEndDate' AND bd.first_night>='$futureStartDate' AND bd.cancelled<>1 AND bd.maintenance<>1 $futureWhereClause GROUP BY p.currency, $dateVal";
$result = mysql_query($sql, $link);
$futureTablePayment = array();
if(!$result) {
	trigger_error("Cannot execute query. " . mysql_error($link) . " (SQL: $sql)");
}
while($row = mysql_fetch_assoc($result)) {
  if(!isset($futureTablePayment[$row['date_val']])) {
    $futureTablePayment[$row['date_val']] = convertAmount($row['amount'], $row['currency'], 'EUR', date('Y-m-d'));;
  } else {
    $futureTablePayment[$row['date_val']] += convertAmount($row['amount'], $row['currency'], 'EUR', date('Y-m-d'));;
  }
}



mysql_close($link);

$arriveChecked = ($_SESSION['cashflow_past_date_by'] == 'arrive' ? 'checked' : '');
$paymentChecked = ($_SESSION['cashflow_past_date_by'] == 'payment' ? 'checked' : '');

html_start("Cashflow view", $extraHeader);


echo <<<EOT


<form action="view_past_projected_payments.php" method="POST">
<table>
  <tr><td>Past start date:</td><td>
    <input id="past_start_date" name="past_start_date" size="10" maxlength="10" type="text" value="$pastStartDateDash"><img src="js/datechooser/calendar.gif" onclick="showChooser(this, 'past_start_date', 'chooserSpanPSD', 2008, 2025, 'Y/m/d', false);">
    <div id="chooserSpanPSD" class="dateChooser select-free" style="display: none; visibility: hidden; width: 160px;"></div>
  </td></tr>
  <tr><td>Past end date:</td><td>
    <input id="past_end_date" name="past_end_date" size="10" maxlength="10" type="text" value="$pastEndDateDash"><img src="js/datechooser/calendar.gif" onclick="showChooser(this, 'past_end_date', 'chooserSpanPED', 2008, 2025, 'Y/m/d', false);">
    <div id="chooserSpanPED" class="dateChooser select-free" style="display: none; visibility: hidden; width: 160px;"></div>
  </td></tr>
  <tr><td>Past date search by:</td><td>
    <input type="radio" name="past_date_by" style="float:none;display:inline;" value="arrive" $arriveChecked> Arrival  <input type="radio" style="float:none;display:inline;" name="past_date_by" value="payment" $paymentChecked> Date of payment
  </td></tr>
  <tr><td>Furture start date:</td><td>
    <input id="future_start_date" name="future_start_date" size="10" maxlength="10" type="text" value="$futureStartDateDash"><img src="js/datechooser/calendar.gif" onclick="showChooser(this, 'future_start_date', 'chooserSpanFSD', 2008, 2025, 'Y/m/d', false);">
    <div id="chooserSpanFSD" class="dateChooser select-free" style="display: none; visibility: hidden; width: 160px;"></div>
  </td></tr>
  <tr><td>Future end date:</td><td>
    <input id="future_end_date" name="future_end_date" size="10" maxlength="10" type="text" value="$futureEndDateDash"><img src="js/datechooser/calendar.gif" onclick="showChooser(this, 'future_end_date', 'chooserSpanFED', 2008, 2025, 'Y/m/d', false);">
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
  <tr><td>Payment type:</td><td>
  <select name="type[]" multiple="yes" size="6" style="height: 80px;">
$typeOptions
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

$currDate = $pastStartDateDash;
$dateCols = array();
while($currDate <= $pastEndDateDash) {
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
	if($_SESSION['cashflow_past_date_by'] == 'arrive') {
		$dateCol = str_replace('-','/',$dateCol);
	}
	$dateCols[] = $dateCol;
}


$cols = count($dateCols) + 1;

// echo "<pre>" . print_r($pastTable,true) . "</pre>\n"; 

echo <<<EOT

<table>
  <tr><td colspan="$cols"><h2>Past range</h2></td></tr>
  <tr><th></th>
EOT;

foreach($dateCols as $dateCol) {
	echo "<th style=\"text-align:left;\">$dateCol</th>";
}
echo "</tr>\n";

// Create CASH4 type that is CASH-CASH3
$pastTable['CASH4'] = array();
foreach($dateCols as $dateCol) {
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
$PAYMODES = array('CASH', 'CASH2', 'CASH3', 'CASH4', 'BANK_TRANSFER', 'CREDIT_CARD');
foreach($PAYMODES as $paym) {
	if(!isset($pastTable[$paym])) {
		continue;
	}
	$values = $pastTable[$paym];
	echo "  <tr><td style=\"font-weight: bold;border-bottom: 1px solid black;\">$paym</td>";
	foreach($dateCols as $dateCol) {
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
echo "	<tr><td colspan=\"$cols\"><hr></td></tr>\n";
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
echo "  </tr>\n";

echo <<<EOT

  <tr><td colspan="$cols"><br><br></td></tr>
  <tr><td colspan="$cols"><h2>Future range</h2></td></tr>
  <tr><td></td>
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

echo "  <tr><td></td>";
foreach($dateCols as $dateCol) {
	echo "    <td>\n";
	if(isset($futureTable[$dateCol])) {
		$amt = $futureTable[$dateCol];
		$pmt = '';
		if(isset($futureTablePayment[$dateCol])) {
			$amt -= $futureTablePayment[$dateCol];
			$pmt = $futureTablePayment[$dateCol];
		}
		echo "      <div class=\"EUR\" style=\"text-align: right;\">" . number_format($amt) . " EUR <span style=\"font-size:70%;\">(" . number_format($pmt) . ")</span></div>\n";
	}
	echo "    </td>\n";
}
echo "  </tr>";
echo "</table><br><br><br>\n";


html_end();



?>
