<?php

use MediaWikiConfig\MediaWikiConfig;

// phpcs:ignore MediaWiki.Files.ClassMatchesFilename.NotMatch
enum Constants {
	case CENTRAL_WIKI;
}

// phpcs:ignore Generic.Files.OneObjectStructurePerFile.MultipleFound
class Extension {

	/**
	 * @param Closure(MediaWikiConfig): void|null $callback
	 * @param array<string, mixed> $config
	 * @param list<string> $dependencies
	 * @param Closure(MediaWikiConfig, array<string, mixed>): void|null $optionsCallback
	 */
	public function __construct(
		private readonly ?Closure $callback = null,
		private readonly array $config = [],
		private readonly array $dependencies = [],
		private readonly ?Closure $optionsCallback = null,
	) {
	}

	public function enable( MediaWikiConfig $config ): void {
		foreach ( $this->dependencies as $dependency ) {
			$config->extension( $dependency );
		}
		$farm = $config->getFarm();
		$centralWiki = $farm?->getCentralWiki() ?? $config->getConf( 'wgDBname' );
		foreach ( $this->config as $k => $v ) {
			if ( $v === Constants::CENTRAL_WIKI ) {
				$v = $centralWiki;
			}
			if ( str_starts_with( $k, '+' ) ) {
				$GLOBALS[substr( $k, 1 )][] = $v;
			} elseif ( str_ends_with( $k, ']' ) ) {
				$matches = [];
				preg_match( '/([^[]+)\[([^]]+)]/', $k, $matches );
				$GLOBALS[$matches[1]][$matches[2]] = $v;
			} else {
				$GLOBALS[$k] = $v;
			}
		}
		if ( $this->callback ) {
			( $this->callback )( $config );
		}
	}

	public function applyOptions( MediaWikiConfig $config, array $options ): void {
		if ( $this->optionsCallback ) {
			( $this->optionsCallback )( $config, $options );
		}
	}

}

return [
	'3D' => static fn () => new Extension(
		config: [
			'+wgFileExtensions' => 'stl',
			'+wgTrustedMediaFormats' => 'application/sla',
		],
	),
	'3DAlloy' => null,
	'AbuseFilter' => static fn () => new Extension(
		callback: static function ( MediaWikiConfig $mwc ) {
			if ( $mwc->getConf( 'wgDBname' ) === $mwc->getConf( 'wgAbuseFilterCentralDB' ) ) {
				$mwc->conf( 'wgAbuseFilterIsCentral', true );
			}
		},
		config: [
			'wgAbuseFilterCentralDB' => Constants::CENTRAL_WIKI,
		],
	),
	'AdvancedSearch' => static fn () => new Extension(
		dependencies: [
			'CirrusSearch'
		],
	),
	'AJAXPoll' => null,
	'Analytics' => null,
	'AntiSpoof' => null,
	'ApprovedRevs' => null,
	'Arrays' => null,
	'ArticleFeedbackv5' => static fn () => new Extension(
		optionsCallback: static function ( MediaWikiConfig $mwc, array $options ) {
			$mwc->conf( 'wgArticleFeedbackv5Categories', $options['categories'] ?? [] );
		},
	),
	'ArticleGuidance' => null,
	'GlobalCssJs' => static fn () => new Extension(
		callback: static function ( MediaWikiConfig $mwc ) {
			if ( !$mwc->getFarm() ) {
				return;
			}
			$centralWiki = $mwc->getCentralWiki();
			$mwc->conf( 'wgGlobalCssJsConfig', [
				'wiki' => $centralWiki,
				'source' => $centralWiki,
			] );
			$scriptPath = $mwc->getFarm()->getScriptPath( $centralWiki );
			$mwc->setAssociativeConfArrayValue(
				'wgResourceLoaderSources',
				$centralWiki,
				[
					'loadScript' => "$scriptPath/load.php",
					'apiScript' => "$scriptPath/api.php"
				],
			);
		},
	),
];
