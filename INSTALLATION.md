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

Please! Do not create issues for about that you can't get this application to run. This is a hobby project and we can't
really handhold people to help them install this. Write [us a line](mailto:info@html24.dk) if you are so inclined and
perhaps you will get lucky and we can help you.

## I hate/am afraid of the terminal

Ok fair enough. We can understand that, but we are big fans of it. So if you are nut sure how to run a command even
then I am sure you can find some nice manuals out there. You can start 
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

