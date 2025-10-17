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

trait MWCUtils {

	public function extensionFilePath( string $extensionName, string $file ): string {
		return $this->getConf( 'wgExtensionDirectory' ) . "/$extensionName/$file";
	}

	public function coreFilePath( string $path ): string {
		return $this->getConf( 'IP' ) . "/$path";
	}

	public function polyfillClassAliases(): self {
		$removedAliases = [
			// MW 1.44 (https://www.mediawiki.org/wiki/Release_notes/1.44#Breaking_changes_in_1.44)
			'ActorMigration' => 'MediaWiki\\User\\ActorMigration',
			'ActorMigrationBase' => 'MediaWiki\\User\\ActorMigrationBase',
			'AtomFeed' => 'MediaWiki\\Feed\\AtomFeed',
			'CategoriesRdf' => 'MediaWiki\\Category\\CategoriesRdf',
			'Category' => 'MediaWiki\\Category\\Category',
			'CategoryViewer' => 'MediaWiki\\Category\\CategoryViewer',
			'ChannelFeed' => 'MediaWiki\\Feed\\ChannelFeed',
			'CommentStore' => 'MediaWiki\\CommentStoreCommentStore',
			'ContentSecurityPolicy' => 'MediaWiki\\Request\\ContentSecurityPolicy',
			'DeprecatedGlobal' => 'MediaWiki\\StubObject\\DeprecatedGlobal',
			'DerivativeRequest' => 'MediaWiki\\Request\\DerivativeRequest',
			'EditPage' => 'MediaWiki\\EditPage\\EditPage',
			'FauxRequest' => 'MediaWiki\\Request\\FauxRequest',
			'FauxRequestUpload' => 'MediaWiki\\Request\\FauxRequestUpload',
			'FauxResponse' => 'MediaWiki\\Request\\FauxResponse',
			'FeedItem' => 'MediaWiki\\Feed\\FeedItem',
			'FeedUtils' => 'MediaWiki\\Feed\\FeedUtils',
			'FileDeleteForm' => 'MediaWiki\\Page\\File\\FileDeleteForm',
			'ForeignResourceManager' => 'MediaWiki\\ResourceLoader\\ForeignResourceManager',
			'FormOptions' => 'MediaWiki\\Html\\FormOptions',
			'Html' => 'MediaWiki\\Html\\Html',
			'LinkFilter' => 'MediaWiki\\ExternalLinks\\LinkFilter',
			'Linker' => 'MediaWiki\\Linker\\Linker',
			'ListToggle' => 'MediaWiki\\Html\\ListToggle',
			'MagicWord' => 'MediaWiki\\Parser\\MagicWord',
			'MagicWordArray' => 'MediaWiki\\Parser\\MagicWordArray',
			'MagicWordFactory' => 'MediaWiki\\Parser\\MagicWordFactory',
			'MergeHistory' => 'MediaWiki\\Page\\MergeHistory',
			'MovePage' => 'MediaWiki\\Page\\MovePage',
			'PageProps' => 'MediaWiki\\Page\\PageProps',
			'PathRouter' => 'MediaWiki\\Request\\PathRouter',
			'ProtectionForm' => 'MediaWiki\\Page\\ProtectionForm',
			'RSSFeed' => 'MediaWiki\\Feed\\RSSFeed',
			'StubGlobalUser' => 'MediaWiki\\StubObject\\StubGlobalUser',
			'StubObject' => 'MediaWiki\\StubObject\\StubObject',
			'StubUserLang' => 'MediaWiki\\StubObject\\StubUserLang',
			'TemplateParser' => 'MediaWiki\\Html\\TemplateParser',
			'TemplatesOnThisPageFormatter' => 'MediaWiki\\EditPage\\TemplatesOnThisPageFormatter',
			'Title' => 'MediaWiki\\Title\\Title',
			'TrackingCategories' => 'MediaWiki\\Category\\TrackingCategories',
			'WebRequestUpload' => 'MediaWiki\\Request\\WebRequestUpload',
			'WebResponse' => 'MediaWiki\\Request\\WebResponse',
			'WikiMap' => 'MediaWiki\\WikiMap\\WikiMap',
			'WikiReference' => 'MediaWiki\\WikiMap\\WikiReference',
			'MediaWiki\\BadFileLookup' => 'MediaWiki\\Page\\File\\BadFileLookup',
			'MediaWiki\\HeaderCallback' => 'MediaWiki\\Request\\HeaderCallback',
			'MediaWiki\\HtmlHelper' => 'MediaWiki\\Html\\HtmlHelper',
		];

		foreach ( $removedAliases as $name => $class ) {
			if ( !class_exists( $name ) && class_exists( $class ) ) {
				class_alias( $class, $name );
			}
		}

		return $this;
	}

}
