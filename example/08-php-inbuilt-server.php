<?php
use Gt\Daemon\Process;

require_once(__DIR__ . "/../vendor/autoload.php");

$serverProcess = new Process("php", "-S", "0.0.0.0:8080", "07-greeter.php");
// TODO!
