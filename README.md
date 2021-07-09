# how to set-up & run the project
The following will guide you through setting up the project on your local PC. If you already have the project on your PC, check the #Other setup section below
# pre-requisites
+ php version 7.0+
+ Mysql version 8.0+
+ composer. (https://getcomposer.org/download/)
+ bower. (bower requires nodejs, so ensure that you have it first. Follow the steps below to install both)
```sh
curl -sL https://deb.nodesource.com/setup_5.x | sudo -E bash -
sudo apt-get install -y nodejs
sudo npm install bower -g
```
+ An empty mysql database. Make sure that the mysql user configured has access to it

## Perform the following in your terminal
```sh
# clone from online repo.
git clone https://gitlab.com/competamillman/saccohub.git

# cd into the folder created. Defaults to saccohub
cd saccohub

# Create db.php file by copying the contents of db.sample.php into db.php
cd _protected/common/config/
sudo cp db.sample.php db.php

# verify your database credentials, by editing the db config file
vi _protected/common/config/db.php

# import the mysql database dump. Ensure that you have the database named saccohub first
mysql -u root saccohub -p < _protected/data/saccohub.sql

# install composer dependencies
composer install

# install bower dependencies
bower install --allow-root

# If bower throws some strange permission denied errors please run the following command to fix it.

sudo chown -R $USER:$GROUP ~/.npm
sudo chown -R $USER:$GROUP ~/.config

# change permission of these folders
sudo chmod -R 777 uploads/
sudo chmod -R 777 assets/
sudo chmod -R 777 _protected/backend/runtime/
sudo chmod -R 777 _protected/console/runtime/
sudo chmod -R 777 _protected/api/runtime/

# Create env.php by copying the contents of env.sample.php into env.php
sudo cp env.sample.php env.php

# Edit the contents of env.php to match your environment

# you're done
```

visit http://localhost/gelf, to view the system. login using the following:
> username => admin | password => Admin12345

# Other setup
Assumes that you already have a copy of this project in your pc, and you just want to get code updates
## pull all branches
```sh
git pull
```
## checkout to develop branch (for development)
```sh
git checkout develop
```

### install/update dependencies
```sh
composer update
bower update
```

### update the database data
Run the Yii migrations, so that you get the latest modifications
```sh
cd _protected
./yii migrate
```

### your done!!!