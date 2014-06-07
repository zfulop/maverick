<?php

$num1 = gregoriantojd(9, 21, 2010);
$num2 = gregoriantojd(1, 1, 1900);
echo "num1: " . $num1 . "\n";
echo "num2: " . $num2 . "\n";
echo "2010-09-20: " . ($num1-$num2) . "\n";


?>
