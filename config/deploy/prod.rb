# Set the deployment directory on the target hosts.
set :deploy_to, "/home/revolver/sites/#{application}.com"

# Use the correct branch on github. Uncomment this if you have set up seperate branches for each staging area
set :branch, "prod"

# The hostnames to deploy to.
role :web, "208.43.141.130"

# Specify one of the web servers to use for database backups or updates.
# This server should also be running Wordpress.
role :db, "208.43.141.130", :primary => true

# The path to wp-cli
set :wp, "cd #{current_path}/#{app_root} ; /usr/local/bin/wp"

# The username on the target system, if different from your local username
ssh_options[:user] = 'revolver'
