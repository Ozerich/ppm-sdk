<?php

require_once "PPM.php";

$ppm = new \ppm\PPM();
$ppm->auth('ozerich', 'Ozi7Mafiozi');
$ppm->selectSport(\ppm\PPM::SPORT_HANDBALL);


print_r($ppm->getAvailableCountForScouting());exit;