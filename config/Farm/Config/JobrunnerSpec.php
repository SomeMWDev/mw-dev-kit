<?php

namespace MediaWikiConfig\Farm\Config;

class JobrunnerSpec implements ConfigEntity {

	public function __construct(
		public readonly int $batchSize,
		public readonly int $intervalSeconds,
	) {
	}

	/** @inheritDoc */
	public static function deserialize( array $data, $default = null ): static {
		return new self(
			$data['batchSize'] ?? $default?->batchSize ?? 20,
			$data['intervalSeconds'] ?? $default?->intervalSeconds ?? 10,
		);
	}

}
