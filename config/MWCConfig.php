<?php

namespace MediaWikiConfig;

use MediaWiki\MainConfigNames;
use MediaWikiConfig\Farm\MWCFarm;

trait MWCConfig {

	public function addNoFollowDomainExceptions( string ...$exceptions ): self {
		return $this->appendMultipleToIndexedConfArray(
			$this->wg( MainConfigNames::NoFollowDomainExceptions ),
			$exceptions,
		);
	}

	public function allowExternalImages( bool $doAllow = true ): self {
		return $this->conf( $this->wg( MainConfigNames::AllowExternalImages ), $doAllow );
	}

	public function allowFileExtensions( string ...$fileExtensions ): self {
		return $this->modConf(
			$this->wg( MainConfigNames::FileExtensions ),
			static function ( &$c ) use ( $fileExtensions ) {
				$c = array_merge( $c, $fileExtensions );
			}
		);
	}

	public function allowInterwikiEditing( bool $doAllow = true ): self {
		return $this->grantPermission( 'sysop', 'interwiki', grant: $doAllow );
	}

	public function contentLanguage( string $code ): self {
		return $this->conf( $this->wg( MainConfigNames::LanguageCode ), $code );
	}

	public function disableHashedUploadDirectory( bool $doDisable = true ): self {
		return $this->conf( $this->wg( MainConfigNames::HashedUploadDirectory ), !$doDisable );
	}

	public function defaultSkin( string $symbolicName ): self {
		return $this->conf( $this->wg( MainConfigNames::DefaultSkin ), $symbolicName );
	}

	public function disableSQLStrictMode(): self {
		wfWarn( 'SQL strict mode is disabled!' );
		return $this->conf( $this->wg( MainConfigNames::SQLMode ), '' );
	}

	public function disableTempAccounts( bool $doDisable = true ): self {
		return $this->setAssociativeConfArrayValue(
			$this->wg( MainConfigNames::AutoCreateTempUser ),
			'enabled',
			!$doDisable
		);
	}

	public function enableAnonUploads( bool $doEnable = true ): self {
		return $this->grantPermission( '*', 'upload', grant: $doEnable );
	}

	public function enableDebugToolbar( bool $doEnable = true ): self {
		return $this->conf( $this->wg( MainConfigNames::DebugToolbar ), $doEnable );
	}

	public function enableDjvuRendering(): self {
		// requires mwutil bash --root 'apt update && apt install djvulibre-bin netpbm'
		// https://www.mediawiki.org/wiki/Manual:How_to_use_DjVu_with_MediaWiki
		return $this
			->allowFileExtensions( 'djvu' )
			->conf( $this->wg( MainConfigNames::DjvuDump ), 'djvudump' )
			->conf( $this->wg( MainConfigNames::DjvuRenderer ), 'ddjvu' )
			->conf( $this->wg( MainConfigNames::DjvuTxt ), 'djvutxt' )
			->conf( $this->wg( MainConfigNames::DjvuPostProcessor ), 'pnmtojpeg' )
			->conf( $this->wg( MainConfigNames::DjvuOutputExtension ), 'jpg' );
	}

	public function enableExceptionListener( bool $doEnable = true ): self {
		return $this
			->MWDevHelper()
			->conf( 'wgMWDevHelperEnableExceptionListener', $doEnable );
	}

	public function enableInstantCommons( bool $doEnable = true ): self {
		return $this->conf( $this->wg( MainConfigNames::UseInstantCommons ), $doEnable );
	}

	public function enableMultiBlocks( bool $doEnable = true ): self {
		return $this->conf( $this->wg( MainConfigNames::EnableMultiBlocks ), $doEnable );
	}

	public function enableNativeSVGRendering( bool $doEnable = true ): self {
		return $this->conf( $this->wg( MainConfigNames::SVGNativeRendering ), $doEnable );
	}

	public function enableUploads( bool $doEnable = true ): self {
		return $this->conf( $this->wg( MainConfigNames::EnableUploads ), $doEnable );
	}

	public function enableWatchlistExpiry( bool $doEnable = true ): self {
		return $this->conf( $this->wg( MainConfigNames::WatchlistExpiry ), $doEnable );
	}

	public function enableWatchlistLabels( bool $doEnable = true ): self {
		return $this->conf( $this->wg( MainConfigNames::EnableWatchlistLabels ), $doEnable );
	}

	public function forceDeferredUpdatesPostSend( bool $doForce = true ): self {
		return $this->conf( $this->wg( MainConfigNames::ForceDeferredUpdatesPreSend ), !$doForce );
	}

	public const UNIT_KIBIBYTE = 0;
	public const UNIT_MEBIBYTE = 1;
	public const UNIT_GIBIBYTE = 2;

	public function setMaxArticleSize( int $amount, int $unit ): self {
		$kibibytes = $amount * pow( 1024, $unit );
		return $this->conf( $this->wg( MainConfigNames::MaxArticleSize ), $kibibytes );
	}

	public function useCodexSpecialBlock( bool $doUse = true ): self {
		return $this->conf( $this->wg( MainConfigNames::UseCodexSpecialBlock ), $doUse );
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
