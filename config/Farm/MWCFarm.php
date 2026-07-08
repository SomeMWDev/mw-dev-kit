<?php

namespace MediaWikiConfig\Farm;

use MediaWiki\Config\SiteConfiguration;
use MediaWikiConfig\Farm\Config\FarmConfigLoader;
use MediaWikiConfig\Farm\Config\WikiSpec;
use MediaWikiConfig\MediaWikiConfig;
use Wikimedia\FileBackend\FSFile\TempFSFile;

class MWCFarm {

	public const USER_MANAGEMENT_STANDALONE = 1;
	public const USER_MANAGEMENT_CENTRAL_AUTH = 2;
	// ToDo support wgSharedDB

	private readonly IFarmUserManagement $userManagement;

	public static function fromFarmConfig( int $userManagementType, array $settings = [] ): self {
		require_once '/srv/mediawiki-config/Farm/Config/bootstrap.php';
		$config = FarmConfigLoader::getInstance()->getConfig();

		$defaultSettings = [];

		foreach ( $config->wikis as $db => $wiki ) {
			$defaultSettings['wgSitename'][$db] = $wiki->name ?? $db;
			$defaultSettings['wgLanguageCode'][$db] = $wiki->language;
		}

		return new self(
			wikis: $config->wikis,
			settings: array_merge_recursive( $settings, $defaultSettings ),
			centralWiki: $config->centralWiki,
			// TODO move to farm config
			userManagementType: $userManagementType,
		);
	}

	/**
	 * @param array<string, WikiSpec> $wikis
	 * @param array $settings
	 * @param string $centralWiki The central wiki (will be used for maintenance scripts by default)
	 * @param int $userManagementType One of the USER_MANAGEMENT_ constants
	 */
	private function __construct(
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
		if ( defined( 'MW_DB' ) ) {
			$wikiId = MW_DB;
		} elseif ( MW_ENTRY_POINT === 'cli' ) {
			$wikiId = $this->centralWiki;
		} else {
			$subdomain = explode( '.', $_SERVER['SERVER_NAME'] )[0];
			$wikiId = $this->userManagement->overrideWikiExists( $this, $mwc, $subdomain );
			if ( $wikiId === null ) {
				$wikiId = array_find_key( $this->wikis, static fn ( $w ) => $w->subdomain === $subdomain );
				if ( $wikiId === null ) {
					$this->showWikiMap();
				}
			}
		}

		$port = $mwc->env( 'MW_DOCKER_PORT' );
		$serverVals = array_map(
			static fn ( $wiki ) => "http://$wiki->subdomain.localhost:$port",
			$this->wikis
		);

		$cacheDirectory = TempFSFile::getUsableTempDirectory() . DIRECTORY_SEPARATOR . rawurlencode( $wikiId );
		// We set the option here already so it applies to standalone wikis
		// In theory, this should be handled by DevelopmentSettings.php, but it's included in Defaults.php
		// before MWC is initialized.
		$mwc->conf( 'wgCacheDirectory', $cacheDirectory );

		if ( $this->wikis[$wikiId]->standalone ) {
			$mwc->conf( 'wgServer', $serverVals[$wikiId] )
				->conf( 'wgDBname', $wikiId );
			return;
		}

		$this->settings['wgServer'] = $serverVals;
		$this->settings['wgArticlePath'] = [
			'default' => $mwc->getConf( 'wgArticlePath' ),
		];

		$siteConfiguration = new SiteConfiguration();
		$siteConfiguration->wikis = array_keys( $this->wikis );
		$mwc
			->conf( 'wgLocalDatabases', $siteConfiguration->wikis )
			->conf( 'wgDBname', $wikiId );
		$siteConfiguration->suffixes = [ 'wiki' ];
		$siteConfiguration->settings = $this->settings;

		foreach ( $siteConfiguration->getAll( $wikiId ) as $key => $value ) {
			$mwc->conf( $key, $value );
		}

		$mwc->conf( 'wgConf', $siteConfiguration );

		// Setup user management after config
		$this->userManagement->setup( $this, $mwc );
	}

	private function showWikiMap(): never {
		require_once __DIR__ . '/NotFound.php';
		die( 1 );
	}

	/**
	 * @return array<string, WikiSpec>
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
