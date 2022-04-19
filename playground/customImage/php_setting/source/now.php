<?php
namespace app\source;

use DateTime;

$now = new DateTime();
echo $now->format('Y-m-d H:i:s') . PHP_EOL;