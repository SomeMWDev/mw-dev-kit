<?php

namespace MediaWikiConfig\Farm\Config;

// This can extend JsonSerializable at some point if we want to modify configs

interface ConfigEntity {

	/**
	 * @param array<string, mixed> $data
	 * @param static|null $default
	 * @throws ConfigException
	 */
	public static function deserialize( array $data, $default = null ): static;

}
