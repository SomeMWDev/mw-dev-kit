<?php

namespace MediaWikiConfig\Farm\Config;

use Exception;

class ConfigException extends Exception {

	/**
	 * @throws ConfigException
	 */
	public static function keyRequired( string $class, string $key ): never {
		throw new self( "$key is required for $class entry!" );
	}

}
