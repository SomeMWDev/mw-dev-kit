<?php

namespace MediaWikiConfig\Farm;

use MediaWikiConfig\MediaWikiConfig;

interface IFarmUserManagement {

	/**
	 * Initialize the user management and set all relevant config options.
	 */
	function setup( MWCFarm $farm, MediaWikiConfig $mwc ): void;

}
