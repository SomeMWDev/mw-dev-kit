<?php

# Protect against web entry
if ( !defined( 'MEDIAWIKI' ) ) {
	exit;
}

/** @var \MediaWikiConfig\MediaWikiConfig $c */
$c = require_once '/srv/mediawiki-config/Defaults.php';

# Custom Configuration

$c
	->enableDebugToolbar();
