<?php

namespace MediaWikiConfig\Farm;

use MediaWikiConfig\MediaWikiConfig;

/**
 * Use CentralAuth to manage shared user accounts across wikis.
 */
class CentralAuthUserManagement implements IFarmUserManagement {

	/** @inheritDoc */
	function setup( MWCFarm $farm, MediaWikiConfig $mwc ): void {
		$mwc->CentralAuth(
			$farm->getCentralWiki()
		);
	}

}
