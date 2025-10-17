<?php
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

namespace MediaWikiConfig;

use AutoLoader;

trait MWCServices {

	// https://www.mediawiki.org/wiki/Parsoid#Linking_a_developer_checkout_of_Parsoid
	public function useLocalParsoid(
		$installDirectory = 'services/parsoid',
		$additionalVirtualRestConfig = []
	): self {
		$interceptParsoidLoading = function ( $className ) {
			// Only intercept Parsoid namespace classes
			if ( preg_match( '/(MW|Wikimedia\\\\)Parsoid\\\\/', $className ) ) {
				$fileName = Autoloader::find( $className );
				if ( $fileName !== null ) {
					require $fileName;
				}
			}
		};

		spl_autoload_register( $interceptParsoidLoading, true, true );
		// AutoLoader::registerNamespaces was added in MW 1.39
		AutoLoader::registerNamespaces( [
			// Keep this in sync with the "autoload" clause in
			// $parsoidInstallDir/composer.json
			'Wikimedia\\Parsoid\\' => $this->coreFilePath( "$installDirectory/src/" ),
		] );

		$this->ext( 'Parsoid', $this->coreFilePath( "$installDirectory/extension.json" ) );

		$this->conf( 'wgVisualEditorParsoidAutoConfig', false );
		$this->conf( 'wgParsoidSettings', [
			'useSelser' => true,
			'rtTestMode' => false,
			'linting' => false,
		] );
		$this->modConf( 'wgVirtualRestConfig', fn ( &$c ) => $c['modules']['parsoid'] = [
			'url' => $this->getConf( 'wgServer' ) . $this->getConf( 'wgScriptPath' ) . '/rest.php',
			...$additionalVirtualRestConfig
		] );

		return $this;
	}

}
