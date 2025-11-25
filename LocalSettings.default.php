<?php

# Protect against web entry
if ( !defined( 'MEDIAWIKI' ) ) {
	exit;
}

$c = require_once '/srv/mediawiki-config/Defaults.php';

# Custom Configuration

$c
	->enableDebugToolbar();
