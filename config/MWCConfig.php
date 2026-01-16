<?php

namespace MediaWikiConfig;

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

}
