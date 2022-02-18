<?php

use Adger01\Chuantongwenhua\CalendarClient;

require_once "autoload.php";
require_once "./vendor/autoload.php";

$time = strtotime(date("1950-12-14 14:00:00"));

$client = new CalendarClient();
$solar = $client->solar(date("Y", $time), date("m", $time), date("d", $time), date("H", $time));


print_r($solar);

die;
