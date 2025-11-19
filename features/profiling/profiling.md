# Profiling

docker-compose.override.yml:

```yml
services:
  mediawiki:
    build:
      dockerfile: docker/profiling.Dockerfile
      context: .
```

LocalSettings.php:
```php
MediaWikiConfig::getInstance()->enableTraceLogging();
```

Then run the following commands:

```shell
docker compose --env-file config/.env -p main build mediawiki
mwutil recreate
mwutil profiling watch
```
