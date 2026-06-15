<?php

namespace MediaWikiConfig\Farm\Config;

class WikiSpec implements ConfigEntity {

	public function __construct(
		public readonly JobrunnerSpec $jobrunnerSpec,
		public readonly string $language,
		public readonly ?string $name,
		public readonly string $subdomain,
	) {
	}

	/** @inheritDoc */
	public static function deserialize( array $data, $default = null ): static {
		return new self(
			jobrunnerSpec: JobrunnerSpec::deserialize( $data['jobrunner'] ?? [], $default?->jobrunnerSpec ),
			language: $data['language'] ?? 'en',
			name: $data['name'] ?? null,
			subdomain: $data['subdomain'] ?? ConfigException::keyRequired( __CLASS__, 'subdomain' ),
		);
	}

}
