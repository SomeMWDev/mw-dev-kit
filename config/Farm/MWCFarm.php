<?php

namespace MediaWikiConfig\Farm;

use MediaWiki\Config\SiteConfiguration;
use MediaWikiConfig\MediaWikiConfig;

class MWCFarm {

	/**
	 * @param array<string, string> $wikis
	 * @param array $settings
	 * @param string $defaultWiki The wiki that will be used for maintenance scripts by default
	 */
	public function __construct(
		private readonly array $wikis,
		private array $settings,
		private readonly string $defaultWiki,
	) {
	}

	public function apply( MediaWikiConfig $mwc ): void {
		$port = $mwc->env( 'MW_DOCKER_PORT' );
		$serverVals = [];
		foreach ( $this->wikis as $subdomain => $dbname ) {
			$serverVals[$dbname] = "http://$subdomain.localhost:$port";
		}
		$this->settings['wgServer'] = $serverVals;

		if ( defined( 'MW_DB' ) ) {
			$wikiId = MW_DB;
		} elseif ( MW_ENTRY_POINT === 'cli' ) {
			$wikiId = $this->defaultWiki;
		} else {
			$subdomain = explode( '.', $_SERVER['SERVER_NAME'] )[0];
			if ( !array_key_exists( $subdomain, $this->wikis ) ) {
				$this->showWikiMap();
			} else {
				$wikiId = $this->wikis[$subdomain];
			}
		}

		$siteConfiguration = new SiteConfiguration();
		$siteConfiguration->wikis = array_values( array_unique( $this->wikis ) );
		$mwc
			->conf( 'wgLocalDatabases', $siteConfiguration->wikis )
			->conf( 'wgDBname', $wikiId );
		$siteConfiguration->suffixes = [ 'wiki' ];
		$siteConfiguration->settings = $this->settings;

		foreach ( $siteConfiguration->getAll( $wikiId ) as $key => $value ) {
			$mwc->conf( $key, $value );
		}

		$mwc->conf( 'wgConf', $siteConfiguration );
	}

	private function showWikiMap(): never {
		require_once __DIR__ . '/NotFound.php';
		die( 1 );
	}

	/**
	 * @return array<string, string>
	 */
	public function getWikis(): array {
		return $this->wikis;
	}

}
