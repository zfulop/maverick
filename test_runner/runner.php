<?php

ini_set("include_path", '/home/maveric3/php:' . ini_get("include_path") );

require '../includes/config/teszt_hostel.php';
require 'includes.php';
require '../reception/room_booking.php';

logDebug("Running tests");

$handle = opendir('.');
while ($entry = readdir($handle)) {
	if(isStepsFile($entry)) {
		echo "Loading Steps file $entry\n";
		require $entry;
	}
}
closedir($handle);

$results = array();
if(isset($argv[1])) {
	$result = runStory($argv[1]);
	$results[$argv[1]] = $result;
} else {
	$handle = opendir('.');
	while ($entry = readdir($handle)) {
		if(isStoryFile($entry)) {
			$result = runStory($entry);
			$results[$entry] = $result;
		}
	}
	closedir($handle);
}

echo "\n\nSummary of test runs\n";
foreach($results as $storyFile => $result) {
	echo $storyFile . "\n";
	foreach($result as $scenario => $errors) {
		echo "\t$scenario - " . (count($errors) > 0 ? 'ERROR' : 'SUCCCESS') . "\n";
	}
}


function isStepsFile($file) {
	$path_parts = pathinfo('./' . $file);
	return (endsWith($path_parts['filename'], "Steps") and ("php" == $path_parts['extension']));
}

function isStoryFile($file) {
	$path_parts = pathinfo('./' . $file);
	return "story" == $path_parts['extension'];
}



function runStory($file) {
	echo "\nRunning story file $file\n";
	echo   "===================" . str_repeat("=", strlen($file)) . "\n";

	$successes = array();
	$errors = array();	
	$fcontent = file_get_contents('./' . $file);
	$scenarios = splitIntoScenarios($fcontent);
	foreach($scenarios as $oneScenario) {
		$commands = splitIntoCommands($oneScenario);
		if(count($commands) < 2 or $commands[0] == '') {
			continue;
		}
		echo "\n" . $commands[0] . "\n";
		$success = true;
		for($i = 1; $i < count($commands); $i++) {
			$oneCommand = $commands[$i];
			$error = executeOneCommand($oneCommand);
			if(!is_null($error)) {
				$errors[$commands[0]] = $error;
				echo "Error occured. Stopping scenario.\n";
				$success = false;
				break;
			}
		}
		if($success) {
			$successes[] = $commands[0];
		}
	}
	
	if(count($errors) > 0) {
		echo "Errors were found while executing the story: \n";
		foreach($errors as $scenario => $error) {
			echo " scenario: " . $scenario . "\n";
			echo "  command: " . $error['command'] . "\n";
			echo "    error: " . $error['error'] . "\n";
		}
	} else {
		echo "Story file executed successfully.\n";
	}

	foreach($successes as $item) {
		$errors[$item] = array();
	}
	
	return $errors;
}

function splitIntoScenarios($content) {
	$lines = explode("\n", $content);
	$scenarios = array();
	$currentScenario = '';
	foreach($lines as $oneLine) {
		$oneLine = trim($oneLine);
		if($oneLine == '') {
			continue;
		}
		if(startsWith($oneLine, 'Scenario')) {
			$scenarios[] = $currentScenario;
			$currentScenario = '';
		}
		$currentScenario .= $oneLine . "\n";
	}
	$scenarios[] = $currentScenario;
	return $scenarios;
}

function splitIntoCommands($content) {
	$lines = explode("\n", $content);
	$commands = array();
	$currentCommand = '';
	foreach($lines as $oneLine) {
		$oneLine = trim($oneLine);
		if($oneLine == '') {
			continue;
		}
		if(startsWith($oneLine, 'Given') or startsWith($oneLine, 'When') or startsWith($oneLine, 'Then') or startsWith($oneLine, 'Verify')) {
			$commands[] = trim($currentCommand);
			$currentCommand = '';
		}
		$currentCommand .= $oneLine . "\n";
	}
	$commands[] = trim($currentCommand);
	return $commands;
}

function startsWith($string, $startsWith) {
	if(0 === strpos($string, $startsWith)) {
		return true;
	}
	return false;
}

function endsWith($string, $endString) {
	return substr($string, -1*strlen($endString)) == $endString;
}

function executeOneCommand($oneCommand) {
	$lines = explode("\n", $oneCommand);
	if($lines[0] == '') {
		return;
	}
	$comm = strtolower(str_replace(' ', '', $lines[0]));
	$table = loadTable($lines);
	try {
		$comm($table);
	} catch(Exception $e) {
		echo "ERROR: " . $e->getMessage() . "\n";
		return array('command' => $lines[0], 'error' => $e->getMessage());
	}
	return null;
}

// 1st line is the command then we have the table
// the 1st line of the table contains the column titles
function loadTable($oneCommand) {
	if(count($oneCommand) < 3) {
		return array();
	}
	$titles = array();
	foreach(explode('|', $oneCommand[1]) as $title) {
		$titles[] = trim($title);
	}
	$table = array('rows'=> array(), 'titles' => $titles);
	for($i = 2; $i < count($oneCommand); $i++) {
		$values = explode('|', $oneCommand[$i]);
		if(count($values) != count($titles)) {
			continue;
		}
		$assoc = array();
		for($j = 0; $j < count($titles); $j++) {
			$assoc[strtolower($titles[$j])] = trim($values[$j]);
		}
		$table['rows'][] = $assoc;
	}
	return $table;
}

function compareList($expectedList, $testList, $compareFct) {
	if(count($expectedList) != count($testList)) {
		throw new Exception("Comparison failed. Expected list has " . count($expectedList) . " elements, but we only see " . count($testList) . " elements.");
	}
	if(!function_exists($compareFct)) {
		throw new Exception("Comparison failed. The compare function $compareFct does not exists");
	}
	usort($expectedList, $compareFct);
	usort($testList, $compareFct);
	for($i = 0; $i < max(count($expectedList), count($testList)); $i++) {
		$cmp = $compareFct($expectedList[$i], $testList[$i]);
		if($cmp !== 0) {
			throw new Exception("Comparison failed (cmpFct: $compareFct, result: $cmp). Expected: " . print_r($expectedList[$i], true) . " but found " . print_r($testList[$i], true));
		}
	}
}



?>
