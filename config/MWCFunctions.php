<?php

namespace MediaWikiConfig;

use MediaWiki\Json\FormatJson;

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

trait MWCFunctions {

	/**
	 * Automatically loads an extension and tries to load its dependencies as well.
	 */
	public function ext( string $name, ?string $extensionJson = null ): self {
		if ( in_array( $name, $this->loadedExtensions, true ) ) {
			wfDebugLog( 'mw-config/ConfigLoader', "Skipping duplicated registration of extension $name." );
			return $this;
		}

		$cachedDependencies = $this->fetchExtDependencies( $name, $extensionJson );

		foreach ( $cachedDependencies as $dependency ) {
			$this->loadExtensionOrSkin( $dependency );
		}

		$this->loadedExtensions[$name] = true;
		wfLoadExtension( $name, $extensionJson );

		return $this;
	}

	public function skin( string $name, bool $default = false, ?string $symbolicName = null ): self {
		if ( in_array( $name, $this->loadedSkins, true ) ) {
			wfDebugLog( 'mw-config/ConfigLoader', "Skipping duplicated registration of skin $name." );
			return $this;
		}

		$this->loadedSkins[$name] = true;
		wfLoadSkin( $name );

		if ( $default ) {
			$this->conf( 'wgDefaultSkin', $symbolicName ?? $name );
		}

		return $this;
	}

	private function fetchExtDependencies( string $name, ?string $extensionJson = null ): array {
		// ToDo support skin dependencies
		$key = "mw-config-ext-dependencies:$name";
		$cachedDependencies = apcu_fetch( $key );
		if ( $cachedDependencies === false ) {
			$extensionJson ??= $this->extensionFilePath( $name, 'extension.json' );
			$extJson = file_get_contents( $extensionJson );
			$extData = FormatJson::decode( $extJson );
			if ( !isset( $extData->requires ) ) {
				return [];
			}
			$requires = $extData->requires;
			if ( !isset( $requires->extensions ) ) {
				return [];
			}
			$cachedDependencies = array_keys( (array)$requires->extensions );
			apcu_store( $key, $cachedDependencies );
		}

		return $cachedDependencies;
	}

	public function conf( string $name, mixed $value ): self {
		$GLOBALS[$name] = $value;

		return $this;
	}

	/**
	 * @param string $to The config option to set
	 * @param string $from The config option to get the value from
	 * @return MediaWikiConfig|MWCFunctions
	 */
	public function cloneConf( string $to, string $from ): self {
		return $this->conf( $to, $this->getConf( $from ) );
	}

	public function getConf( string $name ): mixed {
		return $GLOBALS[$name];
	}

	public function modConf( string $name, callable $modify, mixed $defaultIfNotSet = [] ): self {
		if ( array_key_exists( $name, $GLOBALS ) ) {
			$val = $GLOBALS[$name];
		} else {
			$val = $defaultIfNotSet;
		}
		$modify( $val );
		$GLOBALS[$name] = $val;

		return $this;
	}

	/**
	 * @param string $name
	 * // phpcs:ignore MediaWiki.Commenting.FunctionComment.ObjectTypeHintParam
	 * @param callable|object $function a callable or an instance of a class implementing the hook interface
	 */
	public function hook( string $name, callable|object $function ): self {
		return $this->modConf( 'wgHooks', static function ( &$hooks ) use ( $name, $function ) {
			$hooks[$name][] = $function;
		} );
	}

	/**
	 * Registers a hook, but infers the hook name(s) automatically so only the object has to be provided
	 */
	public function autoHook( object $implementation ): self {
		$implements = class_implements( $implementation );
		foreach ( $implements as $className ) {
			$parts = explode( '\\', $className );
			$lastPart = end( $parts );
			if ( !str_ends_with( $lastPart, 'Hook' ) ) {
				wfLogWarning( "Cannot infer hook name from type '$className'!" );
				continue;
			}
			$hookName = substr( $lastPart, 0, -strlen( 'Hook' ) );
			$this->hook( $hookName, $implementation );
		}

		return $this;
	}

	public function grantPermission( string $group, string $permission, bool $grant = true ): self {
		return $this->modConf( 'wgGroupPermissions', static function ( &$c ) use ( $group, $permission, $grant ) {
			$c[$group][$permission] = $grant;
		} );
	}

	public function grantPermissions( string $group, string ...$permissions ): self {
		foreach ( $permissions as $permission ) {
			$this->grantPermission( $group, $permission );
		}

		return $this;
	}

	public function revokePermission( string $group, string $permission ): self {
		return $this->grantPermission( $group, $permission, false );
	}

	public function defaultUserOption( string $option, mixed $value ): self {
		return $this->modConf( 'wgDefaultUserOptions', static function ( &$c ) use ( $option, $value ) {
			$c[$option] = $value;
		} );
	}

	public function env( string $key ): string {
		global $wgMwcEnv;
		if ( $wgMwcEnv === null ) {
			// integration test fix
			$wgMwcEnv = $wgMwcEnv = parse_ini_file( '/srv/mediawiki-config/.env' );
		}
		return $wgMwcEnv[$key];
	}

}
