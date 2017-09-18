<?php

date_default_timezone_set('Europe/Budapest');
set_time_limit(180);


define('LOG_DIR', '/home/maveric3/logs/');

require('includes/db.php');
require('includes/db_config.php');
require('includes/logger.php');

$hostel = $argv[1];

echo "Backing up $hostel\n";
$link = db_connect($hostel);

//get all of the tables
$tables = array();
$result = mysql_query('SHOW TABLES');
while($row = mysql_fetch_row($result)) {
	$tables[] = $row[0];
}

$handle = fopen('db_backup/db-backup-'.$hostel.'-'.date('Ymd').'-'.time().'.sql','w+');

//cycle through
foreach($tables as $table) {
	echo "\t$table\n";
	$result = mysql_query('SELECT * FROM '.$table);
	$num_fields = mysql_num_fields($result);
	
	$return = 'DROP TABLE '.$table.';';
	fwrite($handle,$return);
	$row2 = mysql_fetch_row(mysql_query('SHOW CREATE TABLE '.$table));
	$return = "\n\n".$row2[1].";\n\n";
	fwrite($handle,$return);
	
	for ($i = 0; $i < $num_fields; $i++) {
		while($row = mysql_fetch_row($result)) {
			$return = 'INSERT INTO '.$table.' VALUES(';
			for($j=0; $j < $num_fields; $j++) {
				$row[$j] = addslashes($row[$j]);
				$row[$j] = ereg_replace("\n","\\n",$row[$j]);
				if (isset($row[$j])) { $return.= '"'.$row[$j].'"' ; } else { $return.= '""'; }
				if ($j < ($num_fields-1)) { $return.= ','; }
			}
			$return .= ");\n";
			fwrite($handle,$return);
		}
	}
	$return ="\n\n\n";
	fwrite($handle,$return);
}

//save file
fclose($handle);
echo "Done\n";

?>