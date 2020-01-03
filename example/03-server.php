<?php
require __DIR__ . "/../vendor/autoload.php";

use Gt\Daemon\Pool;
use Gt\Daemon\Process;

$serverProcess = new Process("php", "-S", "0.0.0.0:8080");
$serverProcess->exec();

echo "Server is serving for the next 5 seconds..." . PHP_EOL;

for($i = 0; $i < 5; $i++) {
	echo ".";
	sleep(1);
}

echo PHP_EOL;

$serverProcess->terminate(SIGINT);

while($serverProcess->isRunning()) {
	echo "still running..." . PHP_EOL;
	sleep(1);
}

echo "Ended!" . PHP_EOL;
