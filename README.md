# Analysis of responsiveness of 50 largest American cities

This project sets up an analyzer that spits out rankings of American metro areas when it comes to transparency via public document requests.

It powers this page of monthly rankings: http://transparency.patrickmaynard.com

It was used in writing this article: https://readsludge.com/2021/02/23/the-least-transparent-big-city-in-america/

It was modified from this repo: https://github.com/patrickmaynard/symfony-5-docker

The analyzer is a bit funny. Because v1 of the MuckRock API doesn't allow us to pull up a jurisdiction directly by slug, we instead cycle through all the jurisdictions, pulling out only the ones that we need.

Still, this should get us some useful information, albeit slowly.

To run the Docker containers:

```
git clone https://github.com/patrickmaynard/muckrock-api-analyzer.git

cd symfony-5-docker

cd docker

docker-compose up
```

#### PHP (PHP-FPM)

Composer is included

```
docker-compose run php-fpm composer 
```

To load fixtures

```
docker-compose run php-fpm bin/console doctrine:fixtures:load
```

If you're lazy though and want to get into the bash command line in the php-fpm container

```
docker-compose run php-fpm bash
```

#### Adding folders to local .git/info/exclude

```
cd ..
echo "docker/logs/" >> .git/info/exclude
echo "docker/database/data/" >> .git/info/exclude
```

#### Recreating the database and running migrations

To recreate the database from inside the php-fpm container:

```
make db
```

#### Importing the major cities from cities.json

```
/var/www/bin/console app:major-cities:load
```

#### Running the analyzer and creating posts

```
/var/www/bin/console app:posts:create
```
(To see results, visit http://localhost/)

#### Deleting things if you want to start fresh

```
/var/www/bin/console app:posts:delete
/var/www/bin/console app:major-cities:delete
```

#### TODOs:

See GitHub issues.