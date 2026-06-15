<?php

namespace MediaWikiConfig\Farm\Config;

class FarmConfig implements ConfigEntity {

	/**
	 * @param array<string, WikiSpec> $wikis
	 * @param WikiSpec $defaults
	 * @param string $centralWiki
	 */
	public function __construct(
		public readonly array $wikis,
		public readonly WikiSpec $defaults,
		public readonly string $centralWiki,
	) {
	}

	/** @inheritDoc */
	public static function deserialize( array $data, $default = null ): static {
		$defaults = WikiSpec::deserialize( ( $data['defaults'] ?? [] ) + [
			// Unused
			'subdomain' => '',
		] );

		$wikis = array_map(
			fn ( $wiki ) => WikiSpec::deserialize( $wiki, $default ),
			$data['wikis'] ?? ConfigException::keyRequired( __CLASS__, 'wikis' )
		);

		$centralWiki = $data['centralWiki'] ?? ConfigException::keyRequired( __CLASS__, 'centralWiki' );

		if ( !array_key_exists( $centralWiki, $wikis ) ) {
			throw new ConfigException( 'Central wiki "' . $centralWiki . '" does not exist.' );
		}

		return new self(
			$wikis,
			$defaults,
			$centralWiki,
		);
	}

}
