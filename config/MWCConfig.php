<?php

namespace MediaWikiConfig;

use MediaWikiConfig\Farm\MWCFarm;

trait MWCConfig {

	public function allowExternalImages( bool $doAllow = true ): self {
		return $this->conf( 'wgAllowExternalImages', $doAllow );
	}

	public function allowFileExtensions( string ...$fileExtensions ): self {
		return $this->modConf( 'wgFileExtensions', static function ( &$c ) use ( $fileExtensions ) {
			$c = array_merge( $c, $fileExtensions );
		} );
	}

	public function allowInterwikiEditing( bool $doAllow = true ): self {
		return $this->grantPermission( 'sysop', 'interwiki', grant: $doAllow );
	}

	public function contentLanguage( string $code ): self {
		return $this->conf( 'wgLanguageCode', $code );
	}

	public function defaultSkin( string $symbolicName ): self {
		return $this->conf( 'wgDefaultSkin', $symbolicName );
	}

	public function disableSQLStrictMode(): self {
		wfWarn( 'SQL strict mode is disabled!' );
		return $this->conf( 'wgSQLMode', '' );
	}

	public function disableTempAccounts( bool $doDisable = true ): self {
		return $this->setAssociativeConfArrayValue( 'wgAutoCreateTempUser', 'enabled', !$doDisable );
	}

	public function enableAnonUploads( bool $doEnable = true ): self {
		return $this->grantPermission( '*', 'upload', grant: $doEnable );
	}

	public function enableDebugToolbar( bool $doEnable = true ): self {
		return $this->conf( 'wgDebugToolbar', $doEnable );
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

	public function enableExceptionListener( bool $doEnable = true ): self {
		return $this
			->MWDevHelper()
			->conf( 'wgMWDevHelperEnableExceptionListener', $doEnable );
	}

	public function enableInstantCommons( bool $doEnable = true ): self {
		return $this->conf( 'wgUseInstantCommons', $doEnable );
	}

	public function enableMultiBlocks( bool $doEnable = true ): self {
		return $this->conf( 'wgEnableMultiBlocks', $doEnable );
	}

	public function enableNativeSVGRendering( bool $doEnable = true ): self {
		return $this->conf( 'wgSVGNativeRendering', $doEnable );
	}

	public function enableUploads( bool $doEnable = true ): self {
		return $this->conf( 'wgEnableUploads', $doEnable );
	}

	public function forceDeferredUpdatesPostSend( bool $doForce = true ): self {
		return $this->conf( 'wgForceDeferredUpdatesPreSend', !$doForce );
	}

	public const UNIT_KIBIBYTE = 0;
	public const UNIT_MEBIBYTE = 1;
	public const UNIT_GIBIBYTE = 2;

	public function setMaxArticleSize( int $amount, int $unit ): self {
		$kibibytes = $amount * pow( 1024, $unit );
		return $this->conf( 'wgMaxArticleSize', $kibibytes );
	}

	public function useCodexSpecialBlock( bool $doUse = true ): self {
		return $this->conf( 'wgUseCodexSpecialBlock', $doUse );
	}

	public function setupFarm( MWCFarm $farm ): self {
		global $wgMwcFarm;
		$wgMwcFarm = $farm;
		$farm->apply( $this );
		return $this;
	}

	public function getFarm(): ?MWCFarm {
		global $wgMwcFarm;
		return $wgMwcFarm;
	}

}
