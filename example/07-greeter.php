<!doctype html>
<meta charset="utf-8" />
<title>Greeter!</title>

<h1>Hello, <?php
$requested = trim($_SERVER["REQUEST_URI"], "/");
$name = $requested ?: "World";
echo "$name!";
?></h1>
<p>This is just an example HTML page to interactively greet the user.</p>
<p>To use it, run 08-php-inbuilt-server.php - it will spawn a new server process and use THIS script as its router.</p>
<p>Being a router, <a href="/Example">all requests</a> are routed through it.</p>
<p>Have fun!</p>
<?php
// Log the request, so it shows up in the 08-php-inbuilt-server.php logs:
$stdout = fopen("php://stdout", "w");
$responseCode = http_response_code();
$logMsg = "[$responseCode] $_SERVER[REQUEST_METHOD] /$requested";
fwrite($stdout, $logMsg . PHP_EOL);
