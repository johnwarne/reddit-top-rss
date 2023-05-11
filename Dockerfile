FROM trafex/alpine-nginx-php7:latest

COPY --chown=nobody dist ./dist
COPY --chown=nobody auth.php cache-clear.php cache.php config-default.php functions.php html.php index.php postlist.php rss.php sort-and-filter.php ./