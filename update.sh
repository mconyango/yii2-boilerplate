#! /bin/bash

# Please only run IF YOU ARE SURE YOU SHOULD!
#
# This script will remove PHP and attempt to install the latest
# "Stable" version! To help me with LARAVEL installations, it'll
# also install composer and turn on mod_rewrite as well if it cannot
# be sure it is installed
#
# @author @JakeLPrice
# @created 25 July 2018

read -p "Press enter to continue - otherwise press CONTROL+C to stop this from executing"

# 1. Add Ondrejs PPA Repo and update
# echo "$(tput setaf 2)1. Add Ondrejs PPA Repo and update...$(tput sgr 0)"
# sudo add-apt-repository ppa:ondrej/php -y -u > /dev/null 2>&1

# 2. Remove default PHP 7.0
#echo "$(tput setaf 2)2. Update PHP to latest$(tput sgr 0)"
#sudo apt-get purge php7.0 php7.0-common -y > /dev/null 2>&1

# 3. Add other PHP Packages for Laravel
#echo "$(tput setaf 2)3. Adding PHP packages$(tput sgr 0)"
#sudo apt-get install php7.2-curl php7.2-xml php7.2-zip php7.2-gd php7.2-mysql php7.2-mbstring -y > /dev/null 2>&1

# 4. OPTIONAL - add composer if not installed
#command -v composer >/dev/null 2>&1 || {
#	echo "$(tput setaf 2)OPTIONAL. Composer not installed. Installing...$(tput sgr 0)"
#	php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
#	php composer-setup.php > /dev/null 2>&1
#	php -r "unlink('composer-setup.php');"
#	mv composer.phar /usr/local/bin/composer
#}

# 5. OPTIONAL - turn on mod_rewrite
sudo a2enmod rewrite > /dev/null 2>&1
sudo service apache2 restart

# 6. Done
echo "$(tput setaf 2)Completed! - PHP version is reporting it is version:$(tput sgr 0)"
php -v
echo "$(tput setaf 2)<3 from wildrocket.io$(tput sgr 0)"
