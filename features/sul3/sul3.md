# SUL3

Custom image to add a rewrite to apache that rewrites /.* to index.php

docker-compose.override.yml:

```yml
services:
  mediawiki-web:
    build:
      dockerfile: features/sul3/sul3-apache.Dockerfile
      context: .
```

Then run the following commands:

```shell
docker compose --env-file config/.env -p main build mediawiki-web
mwutil recreate mediawiki-web
```
