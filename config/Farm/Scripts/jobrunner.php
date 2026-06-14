<?php

use MediaWikiConfig\Farm\Config\FarmConfigLoader;

// TODO Should this be a MW maintenance script?

if ( PHP_SAPI !== 'cli' ) {
	die( 'This script can only be run from the command line.' );
}

ob_implicit_flush( true );

require_once '/srv/mediawiki-config/Farm/Config/bootstrap.php';
$farmConfig = FarmConfigLoader::getInstance()->getConfig();
echo 'Found ' . count( $farmConfig->wikis ) . " wikis.\n";

// Wait for other services, e.g. DB
sleep( 20 );

$processes = [];

foreach ( $farmConfig->wikis as $db => $wiki ) {
	$interval = $wiki->jobrunnerSpec->intervalSeconds;
	if ( $interval < 1 ) {
		trigger_error( "Invalid interval '$interval' for $db!", E_USER_WARNING );
		continue;
	}

	$cmd = [
		'/srv/mediawiki-config/Farm/Scripts/run.sh',
		$db,
		$wiki->jobrunnerSpec->intervalSeconds,
		$wiki->jobrunnerSpec->batchSize,
	];

	echo "Starting runner for $db.\n";


	$proc = proc_open(
		$cmd,
		[
			0 => [ 'file', '/dev/null', 'r' ],
			1 => [ 'file', "/tmp/jobrunner-$db.out.log", 'a' ],
			2 => [ 'file', "/tmp/jobrunner-$db.err.log", 'a' ],
		],
		$pipes
	);

	if ( is_resource( $proc ) ) {
		$processes[$db] = $proc;
	} else {
		echo "Failed to start runner for $db.\n";
	}
}

while ( $processes ) {
	foreach ( $processes as $db => $proc ) {
		$status = proc_get_status( $proc );

		if ( !$status['running'] ) {
			echo sprintf(
				"[%s] Runner for %s exited with code %s\n",
				date( 'c' ),
				$db,
				$status['exitcode']
			);

			proc_close( $proc );
			unset( $processes[$db] );
		}
	}

	sleep( 10 );
}

echo "All runners exited.\n";
