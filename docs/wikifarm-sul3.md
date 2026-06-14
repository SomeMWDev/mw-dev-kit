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
     # An image name is somehow required here so build caching works
    image: mediawiki-jobrunner-farm:latest
  mediawiki-web:
    build:
      dockerfile: features/dockerfiles/apache.Dockerfile
      context: .
````
2. In the `config/` directory, create `farm-config.json` to configure your farm. Example:
```
{
	"wikis": {
		"mainwiki": {},
		"altwiki": {}
	},
	"defaults": {},
	"centralWiki": "mainwiki"
}
```
3. Run `docker compose --env-file config/.env up -d --force-recreate --build` in the mw-dev-kit directory to build the
   custom images and recreate the containers.
4. Run `mwutil clone extension gerrit AntiSpoof --composer`
5. Run `mwutil clone extension gerrit CentralAuth`
6. Add the following to your LocalSettings.php:
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
7. Run `mwutil run update -- -- --doshared --quick`
8. Run `mwutil run CentralAuth:migratePass0.php`
9. Run `mwutil run CentralAuth:migratePass1.php`
10. Run `mwutil sql` and execute the following SQL:
```sql
INSERT INTO global_group_permissions (ggp_group,ggp_permission) VALUES ('steward','globalgrouppermissions'), ('steward','globalgroupmembership');
INSERT IGNORE INTO global_user_groups (gug_user, gug_group) VALUES ((SELECT gu_id FROM globaluser WHERE gu_name='Admin'), 'steward');
```
11. Run `mwutil farm install altwiki`
12. Go to http://localhost:4001/ to get an overview of the wikis.
NOTE: The initial "Admin" account might not work with CentralAuth, so you might have to create a new account.
(TODO: Add a fix for this)
