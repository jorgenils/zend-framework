Zend Framework 1.0 - Basic MVC/Database example application

1. Download Zend Framework from http://framework.zend.com/download

2. Add Zend Framework's library directory to the include_path defined
   in Apache's php.ini

3. Configure Apache with an Alias definition to point the "/zfgrid/"
   URL prefix to the "zfgrid/document_root/" directory in this demo package.
   For example:

    Alias /zfgrid/ "C:/zfgrid/document_root/"
    <Directory "C:/zfgrid/document_root">
        Options all
        AllowOverride all
        Order allow,deny
        Allow from all
    </Directory> 

   Make sure your Apache instance has enabled the mod_rewrite module.
   Remember to restart the Apache service after you edit httpd.conf files.

3. Choose an existing database you can query, or install a new database.
   For the webinar, I used the "world" database from http://dev.mysql.com/doc.
   You should be able to use any database, because the example app
   dynamically queries the tables that exist in the database you specify.

4. Edit "zfgrid/app/etc/config.ini"
   Change the database adapter if you don't use MySQL.
   Change dbname, username, password to access the database you chose in step 3.

NOTE: If you don't have the config parameters set to a valid MySQL instance,
database, user, and password, an error will occur and the browser view will
be blank.  For the sake of keeping the example code simple, there is no error
handling to render PHP exceptions in the browser; instead they are output to
your Apache error.log.

