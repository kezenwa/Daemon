<?php
use \Gt\Daemon\Pool;
use \Gt\Daemon\Process;

require_once(__DIR__ . "/../vendor/autoload.php");

$pool = new Pool();

$pool->add("Letters", new Process("php 03-letters.php"));
$pool->add("Numbers", new Process("php 04-numbers.php"));
$pool->add("Ping", new Process("ping google.com -c 10"));

$pool->exec();

while($pool->numRunning() > 0){
    fwrite(STDOUT,  $pool->read());
    fwrite(STDERR, $pool->readError());
    sleep(3);
}

$pool->close();

echo ("Execution done." . PHP_EOL);
