FROM docker-registry.wikimedia.org/dev/bookworm-php83-fpm:1.0.0 AS build

WORKDIR /src
RUN git clone https://gerrit.wikimedia.org/r/mediawiki/php/luasandbox
RUN apt update && apt install php8.3-dev liblua5.1-0-dev -y
RUN cd luasandbox && phpize && ./configure && make

FROM docker-registry.wikimedia.org/dev/bookworm-php83-fpm:1.0.0

COPY --from=build /src/luasandbox/modules/luasandbox.so /usr/lib/php/20230831/luasandbox.so
RUN echo 'extension=luasandbox.so' > /etc/php/8.3/mods-available/luasandbox.ini && phpenmod luasandbox
