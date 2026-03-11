<?php

namespace MediaWikiConfig\Farm;

use MediaWiki\Request\WebRequest;
use MediaWikiConfig\MediaWikiConfig;

/**
 * Use CentralAuth to manage shared user accounts across wikis.
 *
 * ## Setup after enabling
 *
 * 1. `mwutil run CentralAuth:migratePass0.php`
 * 2. `mwutil run CentralAuth:migratePass1.php`
 * 3. `INSERT INTO global_group_permissions (ggp_group,ggp_permission) VALUES ('steward','globalgrouppermissions'),
 * ('steward','globalgroupmembership');`
 * 4. `INSERT IGNORE INTO global_user_groups (gug_user, gug_group) VALUES ((SELECT gu_id FROM globaluser WHERE
 * gu_name='Admin'), 'steward');`
 */
class CentralAuthUserManagement implements IFarmUserManagement {

	/** @inheritDoc */
	public function setup( MWCFarm $farm, MediaWikiConfig $mwc ): void {
		$mwc->CentralAuth(
			$farm->getCentralWiki()
		);
		$mwc->conf( 'wgCentralAuthLoginWiki', $farm->getCentralWiki() );
		$this->enableSUL3( $farm, $mwc );
	}

	private function enableSUL3( MWCFarm $farm, MediaWikiConfig $mwc ) {
		$mwc
			->conf( 'wgCentralAuthSharedDomainCallback', fn ( $dbname ) => $this->getAuthDomain( $mwc, $dbname ) )
			->conf( 'wgCentralAuthEnableSul3', true )
			->conf( 'wgCentralAuthRestrictSharedDomain', true )
			->conf( 'wgServer', WebRequest::detectServer( true ) )
			->cloneConf( 'wgCanonicalServer', 'wgServer' );

		if ( $mwc->getConf( 'wgServer' ) === $this->getAuthDomain( $mwc, null ) ) {
			$dbName = $mwc->getConf( 'wgDBname' );
			// TODO disable wgUseSiteCss/wgUseSiteJs?
			$mwc
				->conf( 'wgEnableSidebarCache', false )
				->conf( 'wgCentralAuthCookieDomain', '' )
				->conf( 'wgCookiePrefix', 'auth' )
				->conf( 'wgSessionName', 'authSession' )
				->conf( 'wgScriptPath', "/$dbName/w" )
				->conf( 'wgLoadScript', $mwc->getConf( 'wgScriptPath' ) . '/load.php' )
				->conf( 'wgCanonicalServer', $this->getAuthDomain( $mwc, null ) )
				->conf( 'wgScript', $mwc->getConf( 'wgScriptPath' ) . '/index.php' )
				->conf( 'wgResourceBasePath', "/$dbName/w" )
				->conf( 'wgExtensionAssetsPath', $mwc->getConf( 'wgResourceBasePath' ) . '/extensions' )
				->conf( 'wgStylePath', $mwc->getConf( 'wgResourceBasePath' ) . '/skins' )
				->cloneConf( 'wgLocalStylePath', 'wgStylePath' )
				->conf( 'wgArticlePath', "/$dbName/wiki/\$1" )
				->conf( 'wgServer', $this->getAuthDomain( $mwc, null ) )
				->modConf( 'wgCentralAuthSul3SharedDomainRestrictions',
					static fn ( &$c ) => $c['allowedEntryPoints'] = [ 'load' ] );
		}
	}

	/** @inheritDoc */
	public function overrideWikiExists( MWCFarm $farm, MediaWikiConfig $mwc, string $subdomain ): ?string {
		if ( $subdomain !== 'auth' ) {
			return null;
		}
		// Taken from https://github.com/miraheze/mw-config/blob/main/initialise/MirahezeFunctions.php#L287-L298
		$requestUri = $_SERVER['REQUEST_URI'];
		$pathBits = explode( '/', $requestUri, 3 );
		if ( count( $pathBits ) < 3 ) {
			trigger_error(
				"Invalid request URI (requestUri=$requestUri), can't determine wiki.\n",
				E_USER_ERROR
			);
		}
		return $pathBits[1];
	}

	private function getAuthDomain( MediaWikiConfig $mwc, ?string $dbName ): string {
		$port = $mwc->env( 'MW_DOCKER_PORT' );
		return "http://auth.localhost:$port" . ( $dbName ? "/$dbName" : '' );
	}

}
