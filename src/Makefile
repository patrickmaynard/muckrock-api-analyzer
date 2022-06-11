db:
	php bin/console doctrine:database:drop --force --if-exists
	php bin/console doctrine:database:create
	php bin/console doctrine:migrations:migrate
	php bin/console doctrine:fixtures:load --append

clear:
	php bin/console cache:clear
	php bin/console doctrine:cache:clear-metadata
	php bin/console doctrine:cache:clear-query
	php bin/console doctrine:cache:clear-result

test:
	php vendor/bin/phpunit