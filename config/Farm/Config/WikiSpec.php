<?php

namespace MediaWikiConfig\Farm\Config;

class WikiSpec implements ConfigEntity {

	public function __construct(
		public readonly JobrunnerSpec $jobrunnerSpec,
	) {
	}

	/** @inheritDoc */
	public static function deserialize( array $data, $default = null ): static {
		return new self(
			JobrunnerSpec::deserialize( $data['jobrunner'] ?? [], $default?->jobrunnerSpec ),
		);
	}

}
