<?php

namespace MediaWikiConfig;

use Closure;
use MediaWiki\Api\Hook\ApiMakeParserOptionsHook;
use MediaWiki\Hook\ParserFirstCallInitHook;
use MediaWiki\MediaWikiServices;

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

	public function defineParserFunction( string $name, callable $callback ): self {
		return $this->autoHook( new readonly class( $callback, $name ) implements ParserFirstCallInitHook {
			public function __construct(
				private Closure $callback,
				private string $name,
			) {
			}

			/** @inheritDoc */
			public function onParserFirstCallInit( $parser ) {
				MediaWikiServices::getInstance()
					->getContentLanguage()->mMagicExtensions[$this->name] = [$this->name, $this->name];

				$parser->setFunctionHook( $this->name, $this->callback );
			}
		} );
	}

}
