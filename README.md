# About

This is a simple TTS based on perl version: https://github.com/zaf/asterisk-googletts
Uses Google Translate API

# Installation

just clone the repository

# Requirements

You need to have:

- Asterisk 1.4+
- phpAGI  http://phpagi.sourceforge.net/
- php 5+
- sox
- mpg123


# Usage

look at the example.php file. Just run it as an AGI script from Asterisk's Dialplan:

exten => 602,1,Answer()
exten => 602,n,agi(example.php)


# Remarks

- The input text must be UTF-8 encoded. 
- Sometimes does not work if the input stting is too long. If so, try splitting to shorter parts. I think that happens because of some limitations on google side.
- sounds are automatically cached in /tmp

# Author

Cezary Siwek  (cezary.siwek@gmail.com)
