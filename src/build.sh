#!/bin/bash

#This is a script to be run INSIDE the php-fpm container on your local machine.
#It goes outside of the www folder, clones a private build repository and updates it.
#It then pushes to that build repository, so that you can do a simple pull from the build repository on the live server.

#Be sure to set the environment variable OAUTH_TOKEN to your GitHub token before running this! Also set EMAIL and USER_NAME to the values GitHub has.

cd ..
rm -rf muckrock-api-analyzer-build
git clone https://$OAUTH_TOKEN:x-oauth-basic@github.com/patrickmaynard/muckrock-api-analyzer-build.git
cd www
composer install --dev
bin/console cache:clear
rsync -avr --exclude='.git' --exclude='.env.local' --exclude='.gitignore' --exclude='build.sh' ./ ../muckrock-api-analyzer-build
cd ../muckrock-api-analyzer-build
git add *
git commit -m "Update"
git config --global user.email "{$EMAIL}"
git config --global user.name "{$USER_NAME}"
git push
