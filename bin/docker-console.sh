PHP_CONTAINER=`docker ps --format "{{.Names}}" | grep -E '^.*-php-[0-9]+$'`
echo $PHP_CONTAINER;
docker exec -it $PHP_CONTAINER php bin/console "$@"