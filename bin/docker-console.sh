PHP_CONTAINER=`docker ps --format "{{.Names}}" | grep -E '^.*-php-[0-9]+$'`
docker exec -it $PHP_CONTAINER php bin/console "$@"