# About

This is a simple TTS based on perl version: https://github.com/zaf/asterisk-googletts
Uses Google Translate API

# Installation

just clone the repository

# Requirements

You neeed to have:

- Asterisk 1.4+
- phpAGI  http://phpagi.sourceforge.net/
- php 5+
- sox
- mpg123


# Usage

look at the example.php file. Just run it as an AGI script from Asterisk's Dialplan:

exten => 602,1,Answer()
exten => 602,n,agi(example.php)


# Author

Cezary Siwek  (cezary.siwek@gmail.com)
