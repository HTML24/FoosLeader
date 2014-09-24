server 'foosleader.html24-dev.dk', :app, :web, :primary => true
set :deploy_to,   "/home/htmldevd/sites/foosleader.html24-dev.dk"

=begin
before "symfony:cache:warmup", "symfony:doctrine:migrations:migrate"
before "symfony:cache:warmup" do
    load_fixtures
end
=end

