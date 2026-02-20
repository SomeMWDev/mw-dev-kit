<?php

namespace MediaWikiConfig\Farm;

use MediaWiki\Config\SiteConfiguration;
use MediaWikiConfig\MediaWikiConfig;

class MWCFarm {

	public const USER_MANAGEMENT_STANDALONE = 1;
	public const USER_MANAGEMENT_CENTRAL_AUTH = 2;
	// ToDo support wgSharedDB

	private readonly IFarmUserManagement $userManagement;

	/**
	 * @param array<string, string> $wikis
	 * @param array $settings
	 * @param string $centralWiki The central wiki (will be used for maintenance scripts by default)
	 * @param int $userManagementType One of the USER_MANAGEMENT_ constants
	 */
	public function __construct(
		private readonly array $wikis,
		private array $settings,
		private readonly string $centralWiki,
		private readonly int $userManagementType,
	) {
		require_once 'IFarmUserManagement.php';
		switch ( $this->userManagementType ) {
			case self::USER_MANAGEMENT_STANDALONE:
				require_once 'StandaloneUserManagement.php';
				$this->userManagement = new StandaloneUserManagement();
				break;
			case self::USER_MANAGEMENT_CENTRAL_AUTH:
				require_once 'CentralAuthUserManagement.php';
				$this->userManagement = new CentralAuthUserManagement();
				break;
		}
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
			$wikiId = $this->centralWiki;
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

		// Setup user management before config
		$this->userManagement->setup( $this, $mwc );

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

	public function getUserManagement(): IFarmUserManagement {
		return $this->userManagement;
	}

	public function getCentralWiki(): string {
		return $this->centralWiki;
	}

}
