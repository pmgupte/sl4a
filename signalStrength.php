<?php
require_once("Android.php");

define('MY_NAME', 'Signal Strength Checker');
define('VERSION', '0.0.1');
define('MIN_BATTERY_LEVEL', 15);
define('MIN_GOOD_SIGNAL_STRENGTH', 7);

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

	if ($goodStrength && ($signalStrength <= MIN_GOOD_SIGNAL_STRENGTH )) {
		$goodStrength = false;
		$doNotify = true;
		$title = 'Bad Signal :(';
		$message = 'You are loosing network range.';
	}
	else if (!$goodStrength && ($signalStrength > MIN_GOOD_SIGNAL_STRENGTH )) {
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
notify(MY_NAME, 'Signal Strength checker exiting. Battery level is ' . $battery['result'] . '%');

/**
 * function to notify user
 * 
 * @param $title String title of notification
 * @param $message String message to be shown in notification
 * @return none
 */
function notify($title, $message) {
	global $droid;

	echo "\n$message";
	$droid->vibrate();
	$droid->notify($title, $message);
	$droid->ttsSpeak($message);
}
?>