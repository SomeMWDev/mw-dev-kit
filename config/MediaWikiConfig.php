<?php

namespace MediaWikiConfig;

use LogicException;
class MediaWikiConfig {
	use MWCConfig;
	use MWCExtensions;
	use MWCFunctions;
	use MWCHooks;
	use MWCSkins;
	use MWCUtils;
	use MWCMocks;
	use MWCServices;
	use MWCProfiling;
	use MWCPrivate;

	private static ?MediaWikiConfig $instance = null;

	private array $loadedExtensions = [];
	private array $loadedSkins = [];

	protected function __construct() {
		if ( self::$instance !== null ) {
			throw new LogicException( 'There can only be one instance of MediaWikiConfig!' );
		}
		self::$instance = $this;
	}

	public static function getInstance(): MediaWikiConfig {
		self::$instance ??= new self;

		return self::$instance;
	}

}
