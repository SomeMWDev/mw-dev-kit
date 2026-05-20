<?php

namespace MediaWikiConfig;

use MediaWiki\Api\Hook\ApiMakeParserOptionsHook;

trait MWCHooks {

	public function disableApiParserCache(): self {
		return $this->autoHook( new class implements ApiMakeParserOptionsHook {
			/** @inheritDoc */
			public function onApiMakeParserOptions(
				$options, $title, $params, $module, &$reset, &$suppressCache
			): void {
				$suppressCache = true;
			}
		} );
	}

}
