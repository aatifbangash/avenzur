<?php
phpinfo();
require_once 'orderProcessor.php';

// Record start time
$start = microtime(true);
$startTimeFormatted = date("Y-m-d H:i:s");

// Run processor
$processor = new OrderProcessor();
$processedCount = $processor->processOrders();  

// Record end time
$end = microtime(true);
$endTimeFormatted = date("Y-m-d H:i:s");

$duration = round($end - $start, 4);

// Log summary
echo "==============================\n";
echo "Cron Run Summary\n";
echo "Start Time: $startTimeFormatted\n";
echo "End Time:   $endTimeFormatted\n";
echo "Duration:   {$duration} seconds\n";
echo "Processed:  {$processedCount} orders\n";
echo "==============================\n\n";
