<?php
/**
 * IMPORTANT: Make a copy of this file to '/config/freezer.yourdb.php'. The application uses it to show your connections on the frontend.
 *
 * Configure your connection in 'db' section.
 * The 'tables' section is optional. Use it to configure which field should be used to identify the last record if the table doesn't have an auto_increment field.
 * The 'encoding' is optional. Set the encoding to ISO-8859-1 only if that is your database encoding.
 *
 * After that, Freezer is ready to use. Put all files in your local webserver and access the index.htm
 */
return array(
    'db' => array(
        'statement' => 'mysql:host=127.0.0.1;port=3306;dbname=mydb',
        'username' => 'myusername',
        'password' => '123456'
    ),
    /* All entries below are optional. Remove it if not needed. */
    'tables' => array(
        'table_without_id' => array(
            'max_field' => 'datetimecreated'
        )
    ),
    'encoding' => 'ISO-8859-1' /* default is UTF-8 */
);
