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

## Compose

### Database (MariaDB)

...

### PHP (PHP-FPM)

Composer is included

```
docker-compose run php-fpm composer 
```

To run fixtures

```
docker-compose run php-fpm bin/console doctrine:fixtures:load
```

If you're lazy though and want to get into the bash command line in the php-fpm container

```
docker-compose run php-fpm bash
```

### Webserver (Nginx)

...

### Adding folders to local .git/info/exclude

```
cd ..
echo "docker/logs/" >> .git/info/exclude
echo "docker/database/data/" >> .git/info/exclude
```

### Running the analyzer

```
bin/console app:download-major-city-data
```
(To see results, visit http://localhost/)

### TODOs:

* Create a cron job to run the thing every month, adding a how-to in this file
* Add an emailer to send the latest updates to your inbox
* Add line charts of the best and worst, ranked over time
* Deploy to muckdata.patrickmaynard.com instead of just running it locally
* Maybe add weekly or monthly tweets, eventually?
* Maybe add a Sonata admin interface for editing/removing the posts