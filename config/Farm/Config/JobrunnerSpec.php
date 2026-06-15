<?php

namespace MediaWikiConfig\Farm\Config;

class JobrunnerSpec implements ConfigEntity {

	public function __construct(
		public readonly bool $active,
		public readonly int $batchSize,
		public readonly int $intervalSeconds,
	) {
	}

	/** @inheritDoc */
	public static function deserialize( array $data, $default = null ): static {
		return new self(
			$data['active'] ?? $default?->active ?? true,
			$data['batchSize'] ?? $default?->batchSize ?? 20,
			$data['intervalSeconds'] ?? $default?->intervalSeconds ?? 10,
		);
	}

}
