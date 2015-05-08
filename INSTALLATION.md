# Preface

We would like to make a "for dummies" installation guide. However we're pretty busy, so it's hard for us to find the time to really go into detail. Feel free to ask questions about everything here in the guide. Suggestions for changes and additions are also really welcome :-)

# Installation

This application is a built on Symfony. As such it uses several external bundles to accomplish the overall functionality
which can be found in `composer.json`. 
 
### Tools needed

To get this project up and running yourself you need the following tools

* compass
* composer
* webserver
* capifony

That's it. 

## Install required dependencies

To install the required dependencies you need to run the following command

```
composer install
```

This should prompt you for parameters based on your installation. You will be needed to provide database information, 
smtp server information, API endpoint and ruby and compass binary paths. The rest you can probably leave as default 
values or set it to something that makes sense for you. The default values are set so you should get away with just
pressing enter for all prompts for parameters.

## Database installation

To set up the database, make sure that `app/config/parameters.yml` has the correct database information. Then run the 
following command.

```
php app/console doctrine:schema:update --force
```

Now your database is correctly set up.

For development purposes this project comes with some data fixtures, to enable them run the following command.

```
php app/console doctrine:fixtures:load
```

Then answer yes to populate your database with some test users.

### Production setup

To run this in production you can perform any of the commands mentioned above with the `--env=prod -no-debug` flag. 

This project also takes use of Capifony and Capistrano. As of this writing the settings are written to deploy to the
html24 dev server, using a private / public key pair so you won't be able to. But you can change the settings in 
`app/config/deploy/staging.rb` and then to ignore those changes in your git repository you would use 
[git update-index assume-unchanged](http://git-scm.com/docs/git-update-index). Maybe at a later point we will remove
those files from the repository and add distribution files that are in this repository. 

# Running

Please point your apache web server to the web folder of the repository. You can then run the `app_dev.php` file in
development mode, or without it if you prefer the production setup.

You can also use the symfony built in server if you are so inclined, to do so run the following command.

```
php app/console server:run
```

You will then be presented with the URL to locate your FoosLeader instance.

# Help! It doesn't work.

This is a hobby project and we can't really help everyone install this, since we're always booked with all kinds of other projects. We're however really fond of the system ourselves, so we'll do our best to help anyone who has questions. 

Write [us a line](mailto:info@html24.dk) if you need help. We can't promise anything, since this is just a hobby-project, but we will do our best to help. 

# I'm willing to pay for this

Awesome. Shoot us an e-mail and we'll setup everything for you. Usually it's around a couple of hours of work, which we'll happily charge you for. Please understand that we are a digital agency so we have a lot of clients we have to work for. This means we don't really have much time to make custom installations and help people if it's just for free. That's actually the primary reason we've simply open sourced this so everyone can use it without having to pay a dime (or a danish krone) :-)

## I hate/am afraid of the terminal

Ok fair enough. We can understand that, but we are big fans of it. So if you are nut sure how to run a command even then I am sure you can find some nice manuals out there. You can start 
[here](http://symfony.com/doc/current/book/installation.html) or [even here](https://www.google.com)

## I know how to use the terminal

Make sure you have followed the instructions, check out the 
[Symfony installation manual](http://symfony.com/doc/current/book/installation.html) and make sure you have all the
dependencies. If you are absolutely sure that this is wrong, please submit a pull request to this file and tell
us what we missed.

# Resources

Here are some links to get you on your way if you get stuck.

* [Symfony](http://symfony.com/doc/current/index.html)
* [Composer](https://getcomposer.org/)
* [Compass](http://compass-style.org/)
* [Materialize.css](http://materializecss.com)
* [Capistrano](http://capistranorb.com/)
* [Capifony](http://capifony.org/)
* [Google](https://www.google.com)

