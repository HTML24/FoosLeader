set :stages,    %w(staging)
set :default_stage, "staging"
set :stage_dir,     "app/config/deploy"
require 'capistrano/ext/multistage'

# Set basic settings for the project
set :application, "FoosLeader"
set :app_path,    "app"
set :web_path,    "web"
set :shared_files, ["app/config/parameters.yml"]
set :shared_children,     [app_path + "/logs", web_path + "/uploads"]
set :copy_vendors, true
set :use_composer, true
set :model_manager, "doctrine"
set :dump_assetic_assets, true

# Connection & SSH information
ssh_options[:port] = "42"
ssh_options[:user] = "htmldevd"
set :use_sudo, false
set  :keep_releases,  3

# Meebox specific edits, you may need to install composer manually first...
set :composer_bin, "php -d detect_unicode=off ~/composer.phar"
set :repository,  "git@bitbucket.org:tomgud/foosleader.git"
set :scm,         :git
set :scm_command, "/usr/local/cpanel/3rdparty/bin/git"
set :local_scm_command, "git"

# Tasks

# Update doctrine schema, should never be used for production !!!
task :load_fixtures do
    run "#{latest_release}/app/console doctrine:fixtures:load --no-interaction --env=prod"
end



