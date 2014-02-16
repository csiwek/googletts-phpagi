<?php

class GoogleTTS {
	var $speed =1;
	var $lang = 'pl';
	var $debug = 1;	
	var $tmp = '/tmp';
	var $sox = '/usr/bin/sox';
	var $mpg123 = '/usr/bin/mpg123';
	var $ttsURL = "http://translate.google.com/translate_tts";
	var $useCache = true;
	var $agi = null;
	var $format=null;
	var $codec=null;
	var $destFile = null;
	public function __construct($config = null){
		$this->dbg("Initializing Google TTS");
		if (isset($config['speed'])) {
			$this->speed = $config['speed'];
			$this->dbg("setting speed to ".$this->speed);
		}
		if (isset($config['lang'])) {
			$this->lang = $config['lang'];
			$this->dbg("setting lang to ".$this->lang);

		}
		$this->checkSOX();
	}
	
	private function checkSOX() {
		$this->dbg("soxv: checking sox version");
		$cmd = $this->sox." --version > /dev/null 2>&1";
		$soxv = exec($cmd) == 0 ? 14 : 12;
		$this->SoxVer = $soxv;
		$this->dbg("soxv: $soxv");
	}
	public function setAGI($agi){
		if($agi){
			$this->dbg("Setting the AGI instaance");
			$this->agi =$agi;
		} else {
			$this->dbg("Looks like we are running on astersk. We need an AGI instance");
		}

	}
	public function say_tts($text, $lang = null, $speed = null, $noanswer=null){
		if (!$lang) {
			 $lang = $this->lang;
		}
		if (!$speed){
			$speed = $this->speed;
		}
		
		
		$filename = $this->tmp . "/gtts_" . md5($text.$lang.$speed) ;
		$this->filename = $filename;
		if (!file_exists($filename.".mp3")){

			$tuCurl = curl_init();
			$url = $this->ttsURL . "?tl=" . $lang . "&q=" . urlencode($text);
			$this->dbg("Fetching URL: $url");
			curl_setopt($tuCurl, CURLOPT_URL, $url);
			curl_setopt($tuCurl, CURLOPT_VERBOSE, 0);
			curl_setopt($tuCurl, CURLOPT_HEADER, 0);
			curl_setopt($tuCurl, CURLOPT_TIMEOUT, 5);
			curl_setopt($tuCurl, CURLOPT_USERAGENT, "Mozilla/5.0 (X11; Linux; rv:8.0) Gecko/20100101");
			curl_setopt($tuCurl, CURLOPT_RETURNTRANSFER, 1); 
			curl_setopt($tuCurl, CURLOPT_FOLLOWLOCATION, 1);
			$ret = curl_exec($tuCurl);
			if (strlen($ret) == 0){
				$this->dbg("receoved an empty MP3 file. TTS failrd");
				return 0;
			}
			file_put_contents($filename.".mp3", $ret);
			$this->dbg("saved temportary MP3 file: $filename");
		} else {
			$this->dbg("Mp3 file already cached: ".$filename.".mp3");
		}
		$this->convertMP3ToWAV($filename);
		$this->detect_format();
		$destFile = $this->CreateAsteriskFile($speed);
		$this->dbg("Will try to play file: $destFile");
		if($destFile) {
			$playstring = $destFile;
			if ($noanswer){
				$playstring .=  ",noanswer";
			}	
			//$this->agi->stream_file($this->destFile);
			$this->agi->exec("Playback", $playstring);
		}
		
	}

	public function convertMP3ToWAV($filename){
		if(file_exists($filename.".wav")){
			$this->dbg("WAV Filename already exists: ".$filename);
			return 1;
		}
		$mpg123cmd = $this->mpg123." -q -w $filename.wav $filename.mp3 ";
		$this->dbg("executing mpg123: $mpg123cmd");
		$retMpg123 = exec($mpg123cmd);
		$this->dbg("mpg123 returned $retMpg123");
	}

	private function CreateAsteriskFile($speed){
		$destFile = $this->filename . "_" . $this->samplerate."_".$speed ;
		if (file_exists($destFile . "." . $this->format)){
			$this->dbg("Asterisk sound file already exists: ".$destFile." In ".$this->samplerate."_".$speed);
			return $destFile;
		}
	        $soxcmd = $this->sox ." ".  $this->filename.".wav -q -r " . $this->samplerate . " -t raw ". $destFile . "." . $this->format;
		if ($speed != 1) {
			if ($this->SoxVer >= 14) {
				$soxcmd .=  " tempo -s " . $speed;
			} else {
				$soxcmd .=  " stretch 1/" . $speed . " 80";
			}
		}
		$this->dbg("executing sox: $soxcmd");
		exec($soxcmd);
		return $destFile;

	}

	private function dbg($text){

		if ($this->debug){ 
			//echo $text."\n";
			if (isset($this->agi)){
				$this->agi->verbose($text);
			} else {
				error_log($text);

			}
		}
	}

	public function detect_format(){
		if (!$this->agi){
			$this->dbg("AGI not set. cannot detect the format");
			exit;
		}
		$codec = $this->agi->get_variable("CHANNEL(audionativeformat)"); $codec = $codec['data'];
		$this->dbg("Detected codec: $codec");
		$this->codec = $codec;
		if (preg_match('/(silk|sln)12/', $codec)) { $this->format = "sln12" ; $this->samplerate = 12000;}
		elseif (preg_match('/(speex|slin|silk)16|g722|siren7/', $codec)) { $this->format = "sln16" ;  $this->samplerate =  16000;}
		elseif (preg_match('/(speex|slin|celt)32|siren14/', $codec)) { $this->format = "sln32";  $this->samplerate =  32000;}
		elseif (preg_match('/(celt|slin)44/', $codec)) { $this->format =  "sln44";  $this->samplerate = 44100;}
		elseif (preg_match('/(celt|slin)48/', $codec)) { $this->format = "sln48"; $this->samplerate =  48000;}
		else { $this->format = "sln"; $this->samplerate =  8000;}
		$this->dbg("samling rate for this format is : ". $this->format.":".$this->samplerate);
		return 1;
	}
	
}



?>
