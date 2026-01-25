# LuaSandbox

docker-compose.override.yml:

```yml
services:
  mediawiki:
    build:
      dockerfile: features/luasandbox/luasandbox.Dockerfile
      context: .
```

LocalSettings.php:
```php
$c->Scribunto( $c::SCRIBUNTO_ENGINE_LUASANDBOX );
```

Then run the following commands:

```shell
docker compose --env-file config/.env -p main build mediawiki
mwutil recreate
```
