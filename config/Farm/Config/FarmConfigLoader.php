<?php

namespace MediaWikiConfig\Farm\Config;

class FarmConfigLoader {

	private static ?self $instance = null;
	private ?FarmConfig $cached = null;

	private function __construct() {
	}

	public static function getInstance(): self {
		self::$instance ??= new self();
		return self::$instance;
	}

	/**
	 * @throws ConfigException
	 */
	public function getConfig(): FarmConfig {
		$this->cached ??= $this->load();
		return $this->cached;
	}

	/**
	 * @throws ConfigException
	 */
	private function load(): FarmConfig {
		$json = file_get_contents( '/srv/mediawiki-config/farm-config.json' );
		if ( $json === false ) {
			throw new ConfigException( 'Unable to load farm-config.json' );
		}

		$data = json_decode( $json, true );
		return FarmConfig::deserialize( $data );
	}

}
