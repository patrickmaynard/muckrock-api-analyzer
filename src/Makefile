db:
	/var/www/bin/console doctrine:database:drop --force
	/var/www/bin/console doctrine:database:create
	/var/www/bin/console doctrine:migrations:migrate
	/var/www/bin/console doctrine:fixtures:load --append

clear:
	php bin/console cache:clear
	php bin/console doctrine:cache:clear-metadata
	php bin/console doctrine:cache:clear-query
	php bin/console doctrine:cache:clear-result

test:
	php vendor/bin/phpunit