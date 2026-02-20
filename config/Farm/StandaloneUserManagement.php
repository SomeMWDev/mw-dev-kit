<?php

namespace MediaWikiConfig\Farm;

use MediaWikiConfig\MediaWikiConfig;

/**
 * Every wiki has its own standalone user management system.
 */
class StandaloneUserManagement implements IFarmUserManagement {

	/** @inheritDoc */
	function setup( MWCFarm $farm, MediaWikiConfig $mwc ): void {
		// Don't do anything, this is already the default behaviour of MW
	}

}
