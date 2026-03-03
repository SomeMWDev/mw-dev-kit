FROM docker-registry.wikimedia.org/dev/bookworm-php83-jobrunner:1.0.0-s1 as build

# Compile LuaSandbox from source
WORKDIR /src
RUN git clone https://gerrit.wikimedia.org/r/mediawiki/php/luasandbox
RUN apt update && apt install php8.3-dev liblua5.1-0-dev -y
RUN cd luasandbox && phpize && ./configure && make

FROM docker-registry.wikimedia.org/dev/bookworm-php83-jobrunner:1.0.0-s1

# Install LuaSandbox
RUN apt update && apt install liblua5.1-0 -y
COPY --from=build /src/luasandbox/modules/luasandbox.so /usr/lib/php/20230831/luasandbox.so
RUN echo 'extension=luasandbox.so' > /etc/php/8.3/mods-available/luasandbox.ini && phpenmod luasandbox

# Install Excimer
RUN apt-get update && \
    apt-get install php8.3-excimer
