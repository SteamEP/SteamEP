#! /bin/bash

# Requirements
sudo rpm -Uvh http://mirror.webtatic.com/yum/el6/latest.rpm
sudo rpm -Uvh http://download.fedoraproject.org/pub/epel/6/i386/epel-release-6-8.noarch.rpm
sudo rpm -Uvh http://rpms.famillecollet.com/enterprise/remi-release-6.rpm
sudo yum -y install vim httpd mysql mysql-server php55w php55w-opcache php55w-mysql php55w-devel git wget mysql-devel.x86_64 php55w-mcrypt.x86_64 php55w-pear.noarch httpd-devel.x86_64 pcre-devel gcc make redis.x86_64

sed -i 's/AllowOverride None/AllowOverride All/g' /etc/httpd/conf/httpd.conf

# Composer
cd /var/www/html/
curl -sS https://getcomposer.org/installer | php
php composer.phar install

# Copy the setting files.
cp app/config/app.default.php app/config/app.php
cp app/config/database.default.php app/config/database.php
sed -i 's/#EnableSendfile off/EnableSendfile off/g' /etc/httpd/conf/httpd.conf


# Start stuff
sudo chkconfig httpd on
sudo chkconfig mysqld on
sudo chkconfig redis on
sudo service mysqld start
sudo service httpd start
sudo service redis start

# Import MySQL dump.
cd app/storage/
gunzip -c steamep.sql.gz > steamep.sql
sudo mysql -u root test < steamep.sql