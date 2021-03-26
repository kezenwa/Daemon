<?php
use Gt\Daemon\Process;

require_once(__DIR__ . "/../vendor/autoload.php");

$serverProcess = new Process("php", "-S", "0.0.0.0:8080", "07-greeter.php");
$serverProcess->exec();
if(!$serverProcess->isRunning()) {
	echo "Server is not running...", PHP_EOL;
	exit;
}

echo "Executing cURL...", PHP_EOL;
$curlCody = new Process("curl", "http://localhost:8080/Cody");
$curlCody->exec();

$curlScarlett = new Process("curl", "http://localhost:8080/Scarlett");
$curlScarlett->exec();


while($curlCody->isRunning() || $curlScarlett->isRunning()) {
	sleep(1);
}

usleep(1_000);
echo "Response from Cody's greeter: ", PHP_EOL, $curlCody->getOutput(), PHP_EOL;
echo "Response from Scarlett's greeter: ", PHP_EOL, $curlScarlett->getOutput(), PHP_EOL;

echo "This script will stay open. Try hitting the site yourself to see the server logs: http://localhost:8080", PHP_EOL, PHP_EOL;

while($serverProcess->isRunning()) {
	if($output = $serverProcess->getOutput()) {
		foreach(explode("\n", $output) as $line) {
			if(empty(trim($line))) {
				continue;
			}
			echo "[Server]: $line", PHP_EOL;
		}
	}
	usleep(1_000);
}
