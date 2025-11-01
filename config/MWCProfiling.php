<?php
/**
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 * http://www.gnu.org/copyleft/gpl.html
 *
 * @file
 */

namespace MediaWikiConfig;

use DateTime;
use Exception;

trait MWCProfiling {

	/**
	 * @param bool $separateFiles whether to write the logs to separate files with timestamps or only one file
	 * @param string|null $requiredParameter the parameter that has to be set to 1 to generate a trace log, or null
	 * to always create one
	 * @param float $period the period in milliseconds
	 * @return void
	 * @noinspection PhpDocMissingThrowsInspection
	 */
	public function enableTraceLogging(
		bool $separateFiles = false,
		?string $requiredParameter = 'forceflame',
		float $period = 1,
	) {
		if ( !extension_loaded( 'excimer' ) ) {
			/** @noinspection PhpUnhandledExceptionInspection */
			throw new Exception( 'excimer extension not loaded, but MWCProfiling::enableTraceLogging() was called!' );
		}
		// phpcs:ignore MediaWiki.Usage.SuperGlobalsUsage.SuperGlobals
		if ( $requiredParameter === null || isset( $_GET[$requiredParameter] ) ) {
			$excimer = new \ExcimerProfiler();
			// set the period to 1ms
			$excimer->setPeriod( $period / 1000 );
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
	}

}
