#!/usr/bin/php
<?
ini_set('error_reporting', E_ALL);
ini_set('error_log', 'syslog');

require 'googletts.php';
require '/var/lib/asterisk/agi-bin//phpagi.php';

	$tts = new GoogleTTS(array('speed' => 1.2, 'lang' => 'en-GB'));
	$agi = new AGI();
	$tts->setAGI($agi);
	$agi->exec("Progress", "");
	$tts->say_tts("Trying to connect this call", 'en', 1, "noanswer");
	$agi->answer();
	$tts->say_tts("I'm sorry. The number you've dialed is incorrect", 'en', 1);
	
?>
