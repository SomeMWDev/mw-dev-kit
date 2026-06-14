FROM docker-registry.wikimedia.org/dev/bookworm-php83-jobrunner:1.0.0-s1 AS build

# Compile LuaSandbox from source
WORKDIR /src
RUN git clone https://gerrit.wikimedia.org/r/mediawiki/php/luasandbox
RUN apt update && apt install php8.3-dev liblua5.1-0-dev -y
RUN cd luasandbox && phpize && ./configure && make

FROM docker-registry.wikimedia.org/dev/bookworm-php83-jobrunner:1.0.0-s1

# Install LuaSandbox and Excimer
RUN apt update && apt install liblua5.1-0 php8.3-excimer -y
COPY --from=build /src/luasandbox/modules/luasandbox.so /usr/lib/php/20230831/luasandbox.so
RUN echo 'extension=luasandbox.so' > /etc/php/8.3/mods-available/luasandbox.ini && phpenmod luasandbox

# Clean up to reduce the image size
RUN apt clean autoclean && \
    apt autoremove --yes && \
    rm -rf /var/lib/{apt,dpkg,cache,log}/

ENTRYPOINT ["php", "/srv/mediawiki-config/Farm/Scripts/jobrunner.php"]
