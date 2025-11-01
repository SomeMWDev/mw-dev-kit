<?php

namespace MediaWikiConfig;

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
// phpcs:disable MediaWiki.NamingConventions.LowerCamelFunctionsName.FunctionName

trait MWCSkins {

	public function Anisa( bool $default = false ): self {
		return $this->skin( 'Anisa', $default, 'anisa' );
	}

	public function BlueSky( bool $default = false ): self {
		return $this->skin( 'BlueSky', $default, 'bluesky' );
	}

	public function chameleon( bool $default = false ): self {
		return $this
			// TODO pull this dependency automatically from skin.json
			->Bootstrap()
			->skin( 'chameleon', $default );
	}

	public function Citizen( bool $default = false ): self {
		return $this->skin( 'Citizen', $default, 'citizen' );
	}

	public function Cosmos( bool $default = false ): self {
		return $this->skin( 'Cosmos', $default, 'cosmos' );
	}

	public function Lakeus( bool $default = false ): self {
		return $this->skin( 'Lakeus', $default, 'lakeus' );
	}

	public function MinervaNeue( bool $default = false ): self {
		return $this->skin( 'MinervaNeue', $default, 'minerva' );
	}

	public function Mirage( bool $default = false ): self {
		return $this->skin( 'Mirage', $default, 'mirage' );
	}

	public function Monaco( bool $default = false ): self {
		return $this->skin( 'Monaco', $default, 'monaco' );
	}

	public function MonoBook( bool $default = false ): self {
		return $this->skin( 'MonoBook', $default, 'monobook' );
	}

	public function Nimbus( bool $default = false ): self {
		return $this->skin( 'Nimbus', $default, 'nimbus' );
	}

	public function Nostalgia( bool $default = false ): self {
		return $this->skin( 'Nostalgia', $default, 'nostalgia' );
	}

	public function Refreshed( bool $default = false ): self {
		return $this->skin( 'Refreshed', $default, 'refreshed' );
	}

	public function SimpleText( bool $default = false ): self {
		return $this->skin( 'SimpleText', $default, 'simpletext' );
	}

	public function Swift( bool $default = false ): self {
		return $this->skin( 'Swift', $default, 'swift' );
	}

	public function Tweeki( bool $default = false ): self {
		return $this->skin( 'Tweeki', $default, 'tweeki' );
	}

	public function Vector( bool $default = false, bool $legacy = false ): self {
		return $this->skin( 'Vector', $default, $legacy ? 'vector' : 'vector-2022' );
	}

	// BLUESPICE SKINS

	public function BlueSpiceCalumma( bool $default = false ): self {
		return $this
			->chameleon()
			->ExtJSBase()
			->BlueSpiceFoundation()
			->skin( 'BlueSpiceCalumma', $default, 'bluespicecalumma' );
	}

}
