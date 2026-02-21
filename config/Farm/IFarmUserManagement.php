<?php

namespace MediaWikiConfig\Farm;

use MediaWikiConfig\MediaWikiConfig;

interface IFarmUserManagement {

	/**
	 * Initialize the user management and set all relevant config options.
	 */
	public function setup( MWCFarm $farm, MediaWikiConfig $mwc ): void;

	/**
	 * Override the check for whether a wiki exists.
	 * Used for CentralAuth/SUL3.
	 */
	public function overrideWikiExists( MWCFarm $farm, MediaWikiConfig $mwc, string $subdomain ): ?string;

}
