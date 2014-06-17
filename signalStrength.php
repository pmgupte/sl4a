<?php
require_once("Android.php");

define('MY_NAME', 'Signal Strength Checker');
define('MIN_BATTERY_LEVEL', 80);

$droid = new Android();
$droid->startTrackingSignalStrengths();
$droid->batteryStartMonitoring();

$goodStrength = true;
$run = true;
$doNotify = false;
$battery = null;

while ($run) {
	sleep(5);
	$signal = $droid->readSignalStrengths();
	$signalStrength = $signal['result']->gsm_signal_strength;

	if ($goodStrength && ($signalStrength < 7)) {
		$goodStrength = false;
		$doNotify = true;
		$title = 'Bad Signal :(';
		$message = 'You are loosing network range.';
	}
	else if (!$goodStrength && ($signalStrength >= 7)) {
		$goodStrength = true;
		$doNotify = true;
		$title = 'Good Signal :)';
		$message = 'You regained good network range.';
	}

	if ($doNotify) {
		notify($title . " [$signalStrength]", $message);
		$doNotify = false;
	}

	$battery = $droid->batteryGetLevel();
	if ($battery['result'] < MIN_BATTERY_LEVEL) {
		$run = false;
	}
}
$droid->stopTrackingSignalStrengths();
$droid->batteryStopMonitoring();
notify(MY_NAME, 'Exiting because battery level is ' . $battery . '%');

function notify($title, $message) {
	global $droid, $battery;

	echo "\n$message";
	$droid->vibrate();
	$droid->notify($title, $message);
	$droid->ttsSpeak($message);
	// $droid->ttsSpeak('battery level is ' . $battery);
}
?>