# Setting up a Wikifarm with SUL3

1. Create `docker-compose.override.yml` in your mw-dev-kit directory with the following contents:
```yaml
services:
  mediawiki:
    build:
      context: .
      dockerfile: features/dockerfiles/fpm.Dockerfile
  mediawiki-jobrunner:
    build:
      context: .
      dockerfile: features/dockerfiles/jobrunner.Dockerfile
  mediawiki-web:
    build:
      dockerfile: features/dockerfiles/apache.Dockerfile
      context: .
````
2. Run `docker compose --env-file config/.env up -d --force-recreate --build` in the mw-dev-kit directory to build the
   custom images and recreate the containers.
3. Run `mwutil clone extension gerrit AntiSpoof --composer`
4. Run `mwutil clone extension gerrit CentralAuth`
5. Add the following to your LocalSettings.php:
```php
use MediaWikiConfig\Farm\MWCFarm;

$c->setupFarm(
	new MWCFarm(
		[
			'main' => 'mainwiki',
			'alt' => 'altwiki',
		],
		[
			'wgSitename' => [
				'mainwiki' => 'Main Wiki',
				'altwiki' => 'Alt Wiki',
			],
		],
		'mainwiki',
		MWCFarm::USER_MANAGEMENT_CENTRAL_AUTH,
	)
);
```
6. Run `mwutil run update -- -- --doshared --quick`
7. Run `mwutil run CentralAuth:migratePass0.php`
8. Run `mwutil run CentralAuth:migratePass1.php`
9. Run `mwutil sql` and execute the following SQL:
```sql
INSERT INTO global_group_permissions (ggp_group,ggp_permission) VALUES ('steward','globalgrouppermissions'), ('steward','globalgroupmembership');
INSERT IGNORE INTO global_user_groups (gug_user, gug_group) VALUES ((SELECT gu_id FROM globaluser WHERE gu_name='Admin'), 'steward');
```
10. Run `mwutil farm install altwiki`
11. Go to http://localhost:4001/ to get an overview of the wikis.
NOTE: The initial "Admin" account might not work with CentralAuth, so you might have to create a new account.
(TODO: Add a fix for this)
