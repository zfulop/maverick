<?php

function  loadCashRegisterData($viewDay, $lastDayCloseTime, $dayCloseEur, $dayCloseHuf, $dayCloseHuf2, $dayCloseEur2, $link) {
	$payments = array();
	$cashOuts = array();
	$gtransfers = array();
	$eurCasse = $dayCloseEur;
	$hufCasse = $dayCloseHuf;
	$eurCasse2 = $dayCloseEur2;
	$hufCasse2 = $dayCloseHuf2;

	$sql = "SELECT payments.*, booking_descriptions.name FROM payments LEFT OUTER JOIN booking_descriptions ON payments.booking_description_id=booking_descriptions.id WHERE ";
	if(!is_null($viewDay)) {
		$sql .= "SUBSTR(payments.time_of_payment,1,10)='$viewDay'";
	} else {
		$sql .= "payments.time_of_payment>'$lastDayCloseTime'";
	}
	$sql .= " ORDER BY payments.time_of_payment";
	$result = mysql_query($sql, $link);
	if(!$result) {
		trigger_error("Cannot get payments at cash register: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
	} else {
		while($row = mysql_fetch_assoc($result)) {
			if($row['comment'] == '*booking deposit*')
				continue;

			if($row['pay_mode'] != 'CASH3') {
				$payments[] = $row;
			}
			if(($row['pay_mode'] == 'CASH' or $row['pay_mode'] == 'CASH2') and $row['storno'] != 1) {
				if($row['currency'] == 'EUR')
					$eurCasse += $row['amount'];
				else
					$hufCasse += $row['amount'];
			}
			if(($row['pay_mode'] == 'CASH3' or $row['pay_mode'] == 'CASH2') and $row['storno'] != 1) {
				if($row['currency'] == 'EUR')
					$eurCasse2 += $row['amount'];
				else
					$hufCasse2 += $row['amount'];
			}

		}
	}

	if(!is_null($viewDay)) {
		$sql = "SELECT * FROM cash_out WHERE SUBSTR(time_of_payment,1,10)='$viewDay' ORDER BY time_of_payment";
	} else {
		$sql = "SELECT * FROM cash_out WHERE time_of_payment>'$lastDayCloseTime' ORDER BY time_of_payment";
	}
	$result = mysql_query($sql, $link);
	if(!$result) {
		trigger_error("Cannot get cashout at cash register: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
	} else {
		while($row = mysql_fetch_assoc($result)) {
			if($row['pay_mode'] != 'CASH3') {
				$cashOuts[] = $row;
			}
			if(($row['pay_mode'] == 'CASH' or $row['pay_mode'] == 'CASH2') and $row['storno'] != 1) {
				if($row['currency'] == 'EUR')
					$eurCasse -= $row['amount'];
				else
					$hufCasse -= $row['amount'];
			}
			if(($row['pay_mode'] == 'CASH3' or $row['pay_mode'] == 'CASH2') and $row['storno'] != 1) {
				if($row['currency'] == 'EUR')
					$eurCasse2 -= $row['amount'];
				else
					$hufCasse2 -= $row['amount'];
			}

		}
	}

	if(!is_null($viewDay)) {
		$sql = "SELECT * FROM guest_transfer WHERE SUBSTR(time_of_enter,1,10)='$viewDay' AND amount_value>0 ORDER BY time_of_enter";
	} else {
		$sql = "SELECT * FROM guest_transfer WHERE time_of_enter>'$lastDayCloseTime' AND amount_value>0 ORDER BY time_of_enter";
	}
	$result = mysql_query($sql, $link);
	if(!$result) {
		trigger_error("Cannot get guest transfers at cash register: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
	} else {
		while($row = mysql_fetch_assoc($result)) {
			if($row['pay_mode'] != 'CASH3') {
				$gtransfers[] = $row;
			}
			if(($row['pay_mode'] == 'CASH' or $row['pay_mode'] == 'CASH2') and $row['storno'] != 1) {
				if($row['amount_currency'] == 'EUR')
					$eurCasse += $row['amount_value'];
				else
					$hufCasse += $row['amount_value'];
			}
			if(($row['pay_mode'] == 'CASH3' or $row['pay_mode'] == 'CASH2') and $row['storno'] != 1) {
				if($row['amount_currency'] == 'EUR')
					$eurCasse2 += $row['amount_value'];
				else
					$hufCasse2 += $row['amount_value'];
			}

		}
	}

	return array($payments, $cashOuts, $gtransfers, $eurCasse, $hufCasse, $eurCasse2, $hufCasse2);
}

?>