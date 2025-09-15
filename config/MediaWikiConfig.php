<?php

namespace MediaWikiConfig;

use LogicException;

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

class MediaWikiConfig {
	use MWCConfig;
	use MWCExtensions;
	use MWCFunctions;
	use MWCHooks;
	use MWCSkins;
	use MWCUtils;
	use MWCMocks;
	use MWCPrivate;

	private static ?MediaWikiConfig $instance = null;

	private array $loadedExtensions = [];
	private array $loadedSkins = [];

	protected function __construct() {
		if ( self::$instance !== null ) {
			throw new LogicException( 'There can only be one instance of MediaWikiConfig!' );
		}
		self::$instance = $this;
	}

	public static function getInstance(): MediaWikiConfig {
		self::$instance ??= new self;

		return self::$instance;
	}

}
