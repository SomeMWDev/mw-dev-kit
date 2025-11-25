<?php

namespace MediaWikiConfig;

use AutoLoader;

trait MWCServices {

	/**
	 * @param string $installDirectory
	 * @param array<string, string> $additionalVirtualRestConfig
	 * @return MediaWikiConfig|MWCServices
	 * @see https://www.mediawiki.org/wiki/Parsoid#Linking_a_developer_checkout_of_Parsoid
	 */
	public function useLocalParsoid(
		string $installDirectory = 'services/parsoid',
		array $additionalVirtualRestConfig = []
	): self {
		$interceptParsoidLoading = static function ( $className ) {
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
