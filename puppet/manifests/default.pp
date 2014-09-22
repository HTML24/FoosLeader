Exec { path => [ "/bin/", "/sbin/" , "/usr/bin/", "/usr/sbin/" ] }


class system-update {
exec { 'apt-get update':
  command => "/usr/bin/perl -pi -e 's/us.archive.ubuntu.com/dk.archive.ubuntu.com/' /etc/apt/sources.list && apt-get update",
}
}

class dev-packages {

include gcc
include wget

$devPackages = [ "curl", "nodejs", "npm", "rubygems", "openjdk-7-jdk", "libaugeas-ruby" ]
package { $devPackages:
  ensure => "installed",
  require => Exec['apt-get update'],
}

exec { 'install sass with compass using RubyGems':
  command => 'gem install compass',
  require => Package["rubygems"],
}

}


class { "mysql":
  root_password => 'auto',
}

mysql::grant { 'symfony':
  mysql_privileges => 'ALL',
  mysql_password => 'symfony-vagrant',
  mysql_db => 'symfony',
  mysql_user => 'symfony',
  mysql_host => 'localhost',
}

class apache-setup {
$apache = ["apache2"]

  package { $apache:
    ensure => latest,
  }

  file { '/etc/apache2/sites-available/default':
    owner => root,
    group => root,
    ensure => file,
    mode => 644,
    source => '/vagrant/puppet/files/apache_default.conf',
    require => Package[$apache],
  }

  exec { 'stop apache':
    command => "service apache2 stop",
    require => [Package[$apache]],
  }

  exec { 'apache user set':
    command => "chown vagrant /var/lock/apache2 && /usr/bin/perl -pi -e 's/www-data/vagrant/' /etc/apache2/envvars",
    require => [Package[$apache], Exec['stop apache']],
  }

  file { '/var/www/index.html':
    ensure => absent,
  }

}

class php-setup {

$php = ["libapache2-mod-php5", "php5-cli", "php-apc", "php5-dev", "php5-gd", "php5-curl", "php5-mcrypt", "php5-xdebug", "php5-mysql", "php5-intl"]




package { $php:
  ensure => latest,
}

package { "imagemagick":
  ensure => present,
  require => Package[$php],
}

package { "libmagickwand-dev":
  ensure => present,
  require => Package["imagemagick"],
}

package { "phpmyadmin":
  ensure => present,
  require => Package[$php],
}

file { '/etc/php5/cli/php.ini':
  owner  => root,
  group  => root,
  ensure => file,
  mode   => 644,
  source => '/vagrant/puppet/files/php_cli.ini',
  require => Package[$php],
}

file { '/etc/php5/apache2/php.ini':
  owner  => root,
  group  => root,
  ensure => file,
  mode   => 644,
  source => '/vagrant/puppet/files/php.ini',
  require => [Package[$php], Package['apache2']],
}

exec { 'restart apache':
  command => 'service apache2 restart',
  require => [Package[$php]],
}
}

class composer {
exec { 'install composer php dependency management':
  command => 'curl -s http://getcomposer.org/installer | php -- --install-dir=/usr/bin && mv /usr/bin/composer.phar /usr/bin/composer',
  creates => '/usr/bin/composer',
  require => [Package['php5-cli'], Package['curl']],
}

exec { 'composer self update':
  command => 'composer self-update',
  require => [Package['php5-cli'], Package['curl'], Exec['install composer php dependency management']],
}
}

class { 'apt':
  always_apt_update    => true
}

Exec["apt-get update"] -> Package <| |>


include system-update
include dev-packages
include apache-setup
include php-setup
include composer

file { '/home/vagrant/.bash_profile':
  owner  => vagrant,
  group  => vagrant,
  ensure => file,
  mode   => 600,
  source => '/vagrant/puppet/files/bash_profile',
}