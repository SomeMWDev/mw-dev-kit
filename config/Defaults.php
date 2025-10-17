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

# Protect against web entry
if ( !defined( 'MEDIAWIKI' ) ) {
	exit;
}

# MW Defaults

## Include platform/distribution defaults
require_once "$IP/includes/PlatformSettings.php";

## Uncomment this to disable output compression
# $wgDisableOutputCompression = true;

## UPO means: this is also a user preference option

$wgEnableEmail = true;
# UPO
$wgEnableUserEmail = true;

$wgEmergencyContact = "";
$wgPasswordSender = "";

# UPO
$wgEnotifUserTalk = false;
# UPO
$wgEnotifWatchlist = false;
$wgEmailAuthentication = true;

# MySQL specific settings
$wgDBprefix = "";
$wgDBssl = false;

# MySQL table options to use during installation or update
$wgDBTableOptions = "ENGINE=InnoDB, DEFAULT CHARSET=binary";

# Shared database table
# This has no effect unless $wgSharedDB is also set.
$wgSharedTables[] = "actor";

## Shared memory settings
$wgMainCacheType = CACHE_ACCEL;
$wgMemCachedServers = [];

$wgUseImageMagick = true;
$wgImageMagickConvertCommand = "/usr/bin/convert";

# InstantCommons allows wiki to use images from https://commons.wikimedia.org
$wgUseInstantCommons = false;

# Periodically send a pingback to https://www.mediawiki.org/ with basic data
# about this MediaWiki instance. The Wikimedia Foundation shares this data
# with MediaWiki developers to help guide future development efforts.
$wgPingback = false;

# Time zone
$wgLocaltimezone = "UTC";

## Set $wgCacheDirectory to a writable directory on the web server
## to make your wiki go slightly faster. The directory should not
## be publicly accessible from the web.
#$wgCacheDirectory = "$IP/cache";

# Changing this will log out all existing sessions.
$wgAuthenticationTokenVersion = "1";

## For attaching licensing metadata to pages, and displaying an
## appropriate copyright notice / icon. GNU Free Documentation
## License and Creative Commons licenses are supported so far.

# Set to the title of a wiki page that describes your license/copyright
$wgRightsPage = "";
$wgRightsUrl = "";
$wgRightsText = "";
$wgRightsIcon = "";

# Path to the GNU diff3 utility. Used for conflict resolution.
$wgDiff3 = "/usr/bin/diff3";

# Environment variables

$wgMwcEnv = parse_ini_file( '/srv/mediawiki-config/.env' );

$wgSecretKey = $wgMwcEnv['MW_SECRET_KEY'];
$wgUpgradeKey = $wgMwcEnv['MW_UPGRADE_KEY'];

$wgScriptPath = $wgMwcEnv['MW_SCRIPT_PATH'];
$wgResourceBasePath = $wgScriptPath;
$wgServer = $wgMwcEnv['MW_SERVER'];

$wgLogos = [
	'1x' => "$wgResourceBasePath/resources/assets/change-your-logo.svg",
	'icon' => "$wgResourceBasePath/resources/assets/change-your-logo-icon.svg",
];

$wgLanguageCode = $wgMwcEnv['MW_LANG'];
$wgSitename = $wgMwcEnv['MW_SITENAME'];
$wgMetaNamespace = $wgMwcEnv['MW_META_NAMESPACE'];

## Database settings
$wgDBtype = "mysql";
$wgDBserver = $wgMwcEnv['MWC_DB_HOST'];
$wgDBname = $wgMwcEnv['MWC_DB_DATABASE'];
$wgDBuser = $wgMwcEnv['MWC_DB_USER'];
$wgDBpassword = $wgMwcEnv['MWC_DB_PASSWORD'];

# Load other configuration

require_once 'MWCConfig.php';
require_once 'MWCExtensions.php';
require_once 'MWCFunctions.php';
require_once 'MWCHooks.php';
require_once 'MWCSkins.php';
require_once 'MWCUtils.php';
require_once 'MWCMocks.php';
require_once 'MWCServices.php';

// load MWCPrivate.php if available
if ( file_exists( stream_resolve_include_path( 'MWCPrivate.php' ) ) ) {
	require_once 'MWCPrivate.php';
} else {
	// create an empty trait so we don't get an error
	// phpcs:ignore MediaWiki.Files.ClassMatchesFilename.NotMatch
	trait MWCPrivate {
	}
}

require_once 'MediaWikiConfig.php';
