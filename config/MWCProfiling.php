<?php

namespace MediaWikiConfig;

use DateTime;
use Exception;
use ExcimerProfiler;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use MediaWiki\Context\RequestContext;
use MediaWiki\Deferred\DeferredUpdates;
use MediaWiki\Exception\MWExceptionHandler;
use MediaWiki\Html\Html;
use MediaWiki\Output\OutputPage;
use Throwable;

trait MWCProfiling {

	/**
	 * @param bool $separateFiles whether to write the logs to separate files with timestamps or only one file
	 * @param string|null $requiredParameter the parameter that has to be set to 1 to generate a trace log, or null
	 * to always create one
	 * @param float $period the period in milliseconds
	 * @noinspection PhpDocMissingThrowsInspection
	 */
	public function enableTraceLogging(
		bool $separateFiles = false,
		?string $requiredParameter = 'forceflame',
		float $period = 1,
	): self {
		if ( !extension_loaded( 'excimer' ) ) {
			/** @noinspection PhpUnhandledExceptionInspection */
			throw new Exception( 'excimer extension not loaded, but MWCProfiling::enableTraceLogging() was called!' );
		}
		// phpcs:ignore MediaWiki.Usage.SuperGlobalsUsage.SuperGlobals
		if ( $requiredParameter === null || isset( $_GET[$requiredParameter] ) ) {
			$excimer = new ExcimerProfiler();
			// set the period to 1ms
			$excimer->setPeriod( $period / 1000 );
			/** @noinspection PhpUndefinedConstantInspection Not found if excimer is not available on the host */
			$excimer->setEventType( EXCIMER_REAL );
			$excimer->start();
			register_shutdown_function( static function () use ( $excimer, $separateFiles ) {
				$excimer->stop();
				$data = $excimer->getLog()->getSpeedscopeData();
				$data['profiles'][0]['name'] = $_SERVER['REQUEST_URI'];
				$filename = $separateFiles
					? 'speedscope-' . ( new DateTime )->format( 'Y-m-d_His_v' ) . '-' . MW_ENTRY_POINT . '.json'
					: 'speedscope.json';
				file_put_contents( MW_INSTALL_PATH . '/cache/' . $filename,
					json_encode( $data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) );
			} );
		}
		return $this;
	}

	/**
	 * @param string $endpoint The endpoint without a trailing / (e.g. `http://localhost:3000`)
	 * @param string $token
	 * @param string $environment Usually 'prod' or 'dev'. Random requests will only be sampled on 'prod'
	 * @param float $sampleRate
	 * @param string|null $requiredParameter
	 * @param string|null $publicEndpoint The endpoint used for generating the profile URLs, or null to default to
	 * $endpoint
	 */
	public function logToSpeedscopeService(
		string $endpoint,
		string $token,
		string $environment,
		float $sampleRate = 0.01,
		?string $requiredParameter = 'forceflame',
		?string $publicEndpoint = null
	): self {
		// phpcs:ignore MediaWiki.Usage.SuperGlobalsUsage.SuperGlobals
		$forced = $requiredParameter !== null && isset( $_GET[$requiredParameter] );
		if ( extension_loaded( 'excimer' ) && (
				$forced ||
				( $environment === 'prod' && MW_ENTRY_POINT !== 'cli' && mt_rand( 1, ( 1 / $sampleRate ) ) <= 1 )
			) ) {
			$this->doLogToSpeedscopeService(
				$endpoint,
				$publicEndpoint ?? $endpoint,
				$token,
				$environment,
				$forced,
			);
		}
		return $this;
	}

	private function doLogToSpeedscopeService(
		string $endpoint,
		string $publicEndpoint,
		string $token,
		string $environment,
		bool $forced,
	): void {
		$id = bin2hex( random_bytes( 16 ) );

		$pageViewCausedParse = false;
		$parserReport = null;

		$excimer = new ExcimerProfiler();
		$excimer->setPeriod( 0.001 );
		$excimer->setEventType( EXCIMER_REAL );
		$excimer->start();
		$callable = static function () use ( $endpoint, $environment, $token, $excimer, $id, $forced, &$parserReport ) {
			$excimer->stop();
			$data = $excimer->getLog()->getSpeedscopeData();

			$requestContext = RequestContext::getMain();
			$requestUri = $_SERVER['REQUEST_URI'] ?? MW_ENTRY_POINT;
			global $wgDBname;

			$data['profiles'][0]['name'] = $requestUri;
			$data['cpuinfo'] = file_get_contents( '/proc/stat' );
			$data['microtime'] = microtime( true );
			$data['hostname'] = wfHostname();
			$data['memory_peak_allocated_bytes'] = memory_get_peak_usage( true );

			$client = new Client();
			$options = [
				RequestOptions::JSON => [
					'id' => $id,
					'wiki' => $wgDBname ?? 'unknown',
					'url' => $requestUri,
					'cfRay' => $requestContext->getRequest()->getHeader( 'Cf-Ray' ) ?: 'unknown',
					'forced' => $forced,
					'speedscopeData' => json_encode( $data ),
					'parserReport' => $parserReport ? json_encode( $parserReport ) : null,
					'environment' => $environment,
				],
				RequestOptions::HEADERS => [
					'Authorization' => 'Bearer ' . $token,
				],
			];
			try {
				$client->post( "$endpoint/log", $options );
			} catch ( Throwable $e ) {
				MWExceptionHandler::logException( $e );
			}
		};

		if ( MW_ENTRY_POINT === 'cli' ) {
			register_shutdown_function( $callable );
		} else {
			global $wgHooks;
			if ( $forced ) {
				global $wgExtensionFunctions;
				$wgExtensionFunctions[] = static function () use ( $id ) {
					RequestContext::getMain()->getRequest()->response()->header( "Profile-Id: $id" );
				};
				$wgHooks['BeforePageDisplay'][] = static function ( $out, $skin ) use ( $id, $publicEndpoint ) {
					/** @var OutputPage $out */
					$out->addJsConfigVars( [ 'speedscopeProfileId' => $id ] );
					$endpointJs = Html::encodeJsVar( $publicEndpoint );
					$profileIdJs = Html::encodeJsVar( $id );
					$out->addHTML( Html::rawElement(
						'script',
						[],
						<<<JS
(function() {
    'use strict';

    setTimeout(() => {
        const profileId = $profileIdJs;
        const endpoint = $endpointJs;
        const speedscopeUrl = new URL( 'https://speedscope.app' );
        speedscopeUrl.hash = `profileURL=\${endpoint}/profile/\${profileId}`;
        const speedscopeLink = $( '<a>' ).attr( 'href', speedscopeUrl.toString() ).attr( 'target', '_blank' ).text( 'Speedscope' );
        const jsonLink = $( '<a>' ).attr( 'href', `\${endpoint}/profile/\${profileId}` ).attr( 'target', '_blank' ).text( 'JSON' );
        mw.notify(
            $( '<div>' ).append( speedscopeLink, ' (', jsonLink, ')' ),
            {
                autoHide: false,
                title: 'Profile recorded successfully',
                type: 'success'
            }
        );
    }, 1000);

})();
JS
					) );
				};
			}
			$wgHooks['ParserBeforeInternalParse'][] = static function ( $parser, &$text, $stripState ) use ( &$pageViewCausedParse )  {
				if ( str_starts_with( $parser->getOptions()?->getRenderReason(), 'page_view' ) ) {
					$pageViewCausedParse = true;
				}
			};
			$wgHooks['OutputPageParserOutput'][] = static function ( $outputPage, $parserOutput ) use ( &$pageViewCausedParse, &$parserReport ) {
				if ( !$pageViewCausedParse ) {
					return;
				}
				$parserReport = $parserOutput->getLimitReportData();
			};
			DeferredUpdates::addCallableUpdate( $callable );
		}
	}

}
