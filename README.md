Welcome to wpadmin
------------------

wpadmin is a command line tool for administering Wordpress.

Version: 0.0.1

Getting Started
---------------

1. Grab yourself a copy of wpadmin:

    git clone git@github.com:88mph/wpadmin.git

2. Make wpadmin executable:

    chmod u+x /path/to/wpadmin/wpadmin

3. Make your life easier:

   ln -s /path/to/wpadmin/wpadmin /usr/local/bin/wpadmin OR add the folder that contains wpadmin to your PATH.
   
   
Usage
-----

Adding a new user:

$ wpadmin user add steve --password=password --email=steve@example.com

Update a user role:

$ wpadmin user role steve --role=editor

Updating the site title:

$ wpadmin option update --title="New Site Title"

Contributing
------------

We encourage you to contribute new features and patches to wpadmin, feel free to fork the project and send pull requests.

License
-------

GNU General Public License