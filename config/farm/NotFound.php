<?php

global $mwcWikis;

use MediaWiki\Html\Html;

if ( MW_ENTRY_POINT !== 'cli' ) {
	$wikiLinks = '';
	// TODO bad
	$port = getenv( 'MW_DOCKER_PORT' );
	$path = parse_url( $_SERVER['REQUEST_URI'] ?? '|', PHP_URL_PATH ) ?? '';
	foreach ( $mwcWikis as $subdomain => $dbName ) {
		$link = Html::element(
			'a',
			[
				// TODO un-hardcode
				'href' => "http://$subdomain.localhost:$port$path",
				'class' => 'button',
			],
			$dbName,
		);
		$wikiLinks .= Html::rawElement( 'li', [], $link );
	}

	// TODO include simplecss in repo?
	$output = <<<EOF
		<!DOCTYPE html>
		<html lang="en">
			<head>
				<meta charset="utf-8" />
				<meta name="viewport" content="width=device-width, initial-scale=1.0" />
				<title>Wiki not found</title>
				<link rel="stylesheet" href="https://cdn.simplecss.org/simple.min.css">
			</head>
			<body>
				<header>
					<h1>Wiki not found</h1>
				</header>
				<main>
					No wiki was found at the current subdomain.
					<h2>Available wikis</h2>
					<ul>$wikiLinks</ul>
				</main>
				<footer>
					<p>
						Powered by mw-dev-kit (<a href="https://github.com/SomeMWDev/mw-dev-kit" target="_blank">GitHub</a>)
					</p>
				</footer>
			</body>
		</html>
	EOF;
	header( 'Content-length: ' . strlen( $output ) );
	http_response_code( 404 );
	echo $output;
	die( 1 );
} else {
	echo "The wiki database '{$this->getConf('wgDBName')}' was not found." . PHP_EOL;
}
