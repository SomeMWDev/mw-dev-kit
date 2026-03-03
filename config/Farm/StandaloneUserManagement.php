<?php

namespace MediaWikiConfig\Farm;

use MediaWikiConfig\MediaWikiConfig;

/**
 * Every wiki has its own standalone user management system.
 */
class StandaloneUserManagement implements IFarmUserManagement {

	/** @inheritDoc */
	public function setup( MWCFarm $farm, MediaWikiConfig $mwc ): void {
		// Don't do anything, this is already the default behavior of MW
	}

	/** @inheritDoc */
	public function overrideWikiExists( MWCFarm $farm, MediaWikiConfig $mwc, string $subdomain ): ?string {
		return null;
	}

}
