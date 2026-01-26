<?php

namespace MediaWikiConfig;

use MediaWiki\Config\SiteConfiguration;

trait MWCConfig {

	public function allowExternalImages(): self {
		return $this->conf( 'wgAllowExternalImages', true );
	}

	public function allowFileExtensions( string ...$fileExtensions ): self {
		return $this->modConf( 'wgFileExtensions', static function ( &$c ) use ( $fileExtensions ) {
			$c = array_merge( $c, $fileExtensions );
		} );
	}

	public function allowInterwikiEditing(): self {
		return $this->grantPermission( 'sysop', 'interwiki' );
	}

	public function contentLanguage( string $code ): self {
		return $this->conf( 'wgLanguageCode', $code );
	}

	public function defaultSkin( string $symbolicName ): self {
		return $this->conf( 'wgDefaultSkin', $symbolicName );
	}

	public function disableSQLStrictMode(): self {
		return $this->conf( 'wgSQLMode', '' );
	}

	public function disableTempAccounts(): self {
		return $this->modConf( 'wgAutoCreateTempUser', static function ( &$c ) {
			$c['enabled'] = false;
		} );
	}

	public function enableAnonUploads(): self {
		return $this->grantPermission( '*', 'upload' );
	}

	public function enableDebugToolbar(): self {
		return $this->conf( 'wgDebugToolbar', true );
	}

	public function enableDjvuRendering(): self {
		// requires mwutil bash --root 'apt update && apt install djvulibre-bin netpbm'
		// https://www.mediawiki.org/wiki/Manual:How_to_use_DjVu_with_MediaWiki
		return $this
			->allowFileExtensions( 'djvu' )
			->conf( 'wgDjvuDump', 'djvudump' )
			->conf( 'wgDjvuRenderer', 'ddjvu' )
			->conf( 'wgDjvuTxt', 'djvutxt' )
			->conf( 'wgDjvuPostProcessor', 'pnmtojpeg' )
			->conf( 'wgDjvuOutputExtension', 'jpg' );
	}

	public function enableExceptionListener(): self {
		return $this
			->MWDevHelper()
			->conf( 'wgMWDevHelperEnableExceptionListener', true );
	}

	public function enableInstantCommons(): self {
		return $this->conf( 'wgUseInstantCommons', true );
	}

	public function enableNativeSVGRendering(): self {
		return $this->conf( 'wgSVGNativeRendering', true );
	}

	public function enableUploads(): self {
		return $this->conf( 'wgEnableUploads', true );
	}

	public function forceDeferredUpdatesPostSend(): self {
		return $this->conf( 'wgForceDeferredUpdatesPreSend', false );
	}

	public const UNIT_KIBIBYTE = 0;
	public const UNIT_MEBIBYTE = 1;
	public const UNIT_GIBIBYTE = 2;

	public function setMaxArticleSize( int $amount, int $unit ): self {
		$kibibytes = $amount * pow( 1024, $unit );
		return $this->conf( 'wgMaxArticleSize', $kibibytes );
	}

	// TODO move all of this to MWCFarm
	public function setupFarm(
		array $wikis,
		array $settings,
	): self {
		// TODO hacky
		$port = $this->env( 'MW_DOCKER_PORT' );
		$serverVals = [];
		foreach ( $wikis as $subdomain => $dbname ) {
			$serverVals[$dbname] = "http://$subdomain.localhost:$port";
		}
		$settings['wgServer'] = $serverVals;

		// TODO make more customizable via options to this method
		if ( defined( 'MW_DB' ) ) {
			$wikiId = MW_DB;
		} else {
			$subdomain = explode( '.', $_SERVER['SERVER_NAME'] )[0];
			if ( !array_key_exists( $subdomain, $wikis ) ) {
				$this->showWikiMap( $wikis );
			} else {
				$wikiId = $wikis[$subdomain];
			}
		}

		$siteConfiguration = new SiteConfiguration();
		$siteConfiguration->wikis = array_values( array_unique( $wikis ) );
		$this
			->conf( 'wgLocalDatabases', $siteConfiguration->wikis )
			->conf( 'wgDBname', $wikiId );
		$siteConfiguration->suffixes = [ 'wiki' ];
		$siteConfiguration->settings = $settings;

		foreach ( $siteConfiguration->getAll( $wikiId ) as $key => $value ) {
			// TODO check if this works with appending values
			$this->conf( $key, $value );
		}

		return $this
			->conf( 'wgConf', $siteConfiguration );
	}

	public function showWikiMap( array $wikis ): never {
		$this->conf( 'mwcWikis', $wikis );
		require_once __DIR__ . '/farm/WikiMap.php';
		die( 1 );
	}

}
