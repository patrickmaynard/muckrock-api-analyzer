# Analysis of responsiveness of top 50 American cities

This project sets up an analyzer that spits out the least responsive American cities when it comes to public document requests.

Modified from https://github.com/patrickmaynard/symfony-5-docker

Our analyzer is a bit funny. Because the v1 of the MuckRock API doesn't allow us to pull up a jurisdiction directly by slug, we instead cycle through all the jurisdictions, pulling out only the ones that we need.

Still, this should get us some useful information, albeit slowly.

To run the Docker containers:

```
git clone https://github.com/patrickmaynard/symfony-5-docker.git

cd symfony-5-docker

cd docker

docker-compose up
```

#### Database (MariaDB)

...

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

#### Webserver (Nginx)

...

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

* x Add custom exceptions when app:posts:create is run without any cities loaded
* x Test all four commands
* x Figure out why file_get_contents is failing -- see https://stackoverflow.com/questions/65762628
* Create a build script that uses a token (stored in .env) to log into GitHub and push to a private build repository
* Update the post body length limit, then extend the rankings to 50 items
* Allow the `app:posts:create` command to take in a url argument that will be the next url to hit. That way, you can pick up where you left off if an error occurs.
* Add a testing pipeline for GitHub (and add this TODO item to the parent repository)
* Change from using a Post entity to using a Ranking entity, with more specific fields
* Create commands for populating and deleting the Rankings, including an optional date argument for deletion
* Add line charts of the best and worst, ranked over time
* Deploy to muckdata.patrickmaynard.com instead of just running it locally
* Add an emailer to send the latest updates to your inbox
* Maybe add weekly or monthly tweets, eventually?
* Figure out why migrations are broken
* Create a cron job to run the thing every month, adding a how-to in this file
* Maybe add a Sonata admin interface for editing/removing the posts
* If a Sonata admin interface has been created, see what logic can be consolidated in services
