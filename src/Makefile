db:
	/var/www/bin/console doctrine:database:drop --force
	/var/www/bin/console doctrine:database:create
	#/var/www/bin/console doctrine:database:create
	#/var/www/bin/console doctrine:migrations:migrate
	# Migrations are broken at the moment. See https://stackoverflow.com/questions/65539247.
	# TODO: Fix them. Meanwhile ...
	bin/console doctrine:schema:update --force
