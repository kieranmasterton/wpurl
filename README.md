Welcome to wpurl
------------------

wpurl is a command line tool for updating the WordPress site URL.

Version: 0.0.1

Getting Started
---------------

1. Grab yourself a copy of wpurl:

    git clone https://github.com/88mph/wpadmin.git

2. Make wpurl executable:

    chmod u+x /path/to/wpurl/wpurl

3. Make your life easier:

    ln -s /path/to/wpurl/wpurl /usr/local/bin/wpurl OR add the folder that contains wpurl to your PATH.
   
   
Usage
-----

cd /path/to/wordpress/docroot/

Checking the current site URL:

$ wpurl

Change the current site URL:

$ wpurl http://newsiteurl.com


Change the current site URL if you have customised your wp-config.php:

$ wpurl http://newsiteurl.com --dbname="example_db" --dbuser="example_user" --dbpassword="letmein"

License
-------

GNU General Public License

Credits and Thanks
------------------

Developed by Kieran Masterton <kieran@88mph.com> and Jon Reeks at 88MPH - <http://88mph.com>

Credit must go to the developers of drush the Drupal command line tool for their shell wrapper we modified for use with wpurl - <http://drupal.org/project/drush>