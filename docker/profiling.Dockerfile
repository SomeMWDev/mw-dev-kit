FROM docker-registry.wikimedia.org/dev/bookworm-php83-fpm:1.0.0

RUN apt-get update && \
    apt-get install php8.3-excimer
