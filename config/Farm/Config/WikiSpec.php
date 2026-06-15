<?php

namespace MediaWikiConfig\Farm\Config;

class WikiSpec implements ConfigEntity {

	public function __construct(
		public readonly JobrunnerSpec $jobrunnerSpec,
		public readonly string $language,
		public readonly ?string $name,
		public readonly bool $standalone,
		public readonly string $subdomain,
	) {
	}

	/** @inheritDoc */
	public static function deserialize( array $data, $default = null ): static {
		return new self(
			jobrunnerSpec: JobrunnerSpec::deserialize( $data['jobrunner'] ?? [], $default?->jobrunnerSpec ),
			language: $data['language'] ?? $default?->language ?? 'en',
			name: $data['name'] ?? $default?->name ?? null,
			standalone: $data['standalone'] ?? $default?->standalone ?? false,
			subdomain: $data['subdomain'] ?? ConfigException::keyRequired( __CLASS__, 'subdomain' ),
		);
	}

}
