FROM docker-registry.wikimedia.org/dev/trixie-php85-jobrunner:1.0.0 AS build

# Compile LuaSandbox from source
WORKDIR /src
RUN git clone https://gerrit.wikimedia.org/r/mediawiki/php/luasandbox
RUN apt update && apt install php8.5-dev liblua5.1-0-dev -y
RUN cd luasandbox && phpize && ./configure && make

FROM docker-registry.wikimedia.org/dev/trixie-php85-jobrunner:1.0.0

# Install LuaSandbox
RUN apt update && apt install liblua5.1-0 -y
COPY --from=build /src/luasandbox/modules/luasandbox.so /usr/lib/php/20230831/luasandbox.so
RUN echo 'extension=luasandbox.so' > /etc/php/8.5/mods-available/luasandbox.ini && phpenmod luasandbox

# Clean up to reduce the image size
RUN apt clean autoclean && \
    apt autoremove --yes && \
    rm -rf /var/lib/{apt,dpkg,cache,log}/

ENTRYPOINT ["php", "/srv/mediawiki-config/Farm/Scripts/jobrunner.php"]
