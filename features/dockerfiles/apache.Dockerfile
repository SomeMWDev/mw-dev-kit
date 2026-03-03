# Important: Make sure the version here matches the latest version of the mediawiki-web image in docker-compose.yml
FROM docker-registry.wikimedia.org/dev/bookworm-apache2:1.0.1

# Append SUL3 load.php rewrite first, then generic wiki index rewrite, after "RewriteEngine On"
RUN grep -q "wiki rewrite rules" /etc/apache2/sites-available/000-default.conf || \
    sed -i '/RewriteEngine On/a \
    # wiki rewrite rules\n\
    RewriteRule ^/[^/]+wiki/w/load\.php$ /w/load.php [L]\n\
    RewriteRule ^/[^/]+wiki(/.*)?$ %{DOCUMENT_ROOT}/w/index.php [L]' \
    /etc/apache2/sites-available/000-default.conf
