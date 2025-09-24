<?php

namespace MediaWikiConfig;

/**
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 * http://www.gnu.org/copyleft/gpl.html
 *
 * @file
 */

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

	public const int UNIT_KIBIBYTE = 0;
	public const int UNIT_MEBIBYTE = 1;
	public const int UNIT_GIBIBYTE = 2;

	public function setMaxArticleSize( int $amount, int $unit ): self {
		$kibibytes = $amount * pow( 1024, $unit );
		return $this->conf( 'wgMaxArticleSize', $kibibytes );
	}

	public function useInstantCommons(): self {
		return $this->conf( 'wgUseInstantCommons', true );
	}

}
