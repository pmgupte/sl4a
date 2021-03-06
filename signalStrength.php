<?php
require_once("Android.php");

define('MY_NAME', 'Signal Strength Checker');
define('VERSION', '0.0.1');
define('MIN_BATTERY_LEVEL', 15);
define('MIN_GOOD_SIGNAL_STRENGTH', 7);

define('BAD_SIGNAL_TITLE', 'Range Lost');
define('BAD_SIGNAL_MESSAGE', 'Callable range lost');
define('GOOD_SIGNAL_TITLE', 'Range Found');
define('GOOD_SIGNAL_MESSAGE', 'Back in callable range');
define('LOW_BATTERY_MESSAGE', 'Signal Strength checker exiting. Battery level is ');

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
		$title = BAD_SIGNAL_TITLE;
		$message = BAD_SIGNAL_MESSAGE;
	}
	else if (!$goodStrength && ($signalStrength > MIN_GOOD_SIGNAL_STRENGTH )) {
		$goodStrength = true;
		$doNotify = true;
		$title = GOOD_SIGNAL_TITLE;
		$message = GOOD_SIGNAL_MESSAGE;
	}

	if ($doNotify) {
		notify($title, $message);
		$doNotify = false;
	}

	$battery = $droid->batteryGetLevel();
	if ($battery['result'] < MIN_BATTERY_LEVEL) {
		$run = false;
	}
}
$droid->stopTrackingSignalStrengths();
$droid->batteryStopMonitoring();
notify(MY_NAME, LOW_BATTERY_MESSAGE . $battery['result'] . '%');

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
	$droid->notify($title, $message);

	$droid->vibrate();
	$silentMode = $droid->checkRingerSilentMode();

	if (!$silentMode['result']) {
		$droid->ttsSpeak($title);
	}
}
?>
