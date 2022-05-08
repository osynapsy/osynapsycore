# What is Osynapsy? #
Osynapsy is a MVC php framework. 

##Installation
It's recommended that you use [Composer](https://getcomposer.org/) to install Osynapsy.

```bash
$ composer require osynapsy.net/osynapsy "^0.7.1"
```

This install osynapsy and all required dependencies. Osynapsy require PHP 7.2.0 or newer.

## Usage
### The webroot directory and index file
Create and enter in webroot directory:

```bash
mkdir webroot

cd webroot
```

Create an index.php file with the following contents:

```php
<?php
require '../vendor/autoload.php';

$kernel = new Osynapsy\Kernel('../etc/site.xml');

echo $kernel->run();
```
### The etc directory and instance configuration file
Create and enter into etc directory:

```bash
mkdir etc

cd etc
```

Create an instance.xml config file with the following contents:

```xml
<?xml version='1.0' standalone='yes'?>
<configuration>
    <app>
        <Test_App> 
            <datasources>
                <db id="dba">mysql:127.0.0.1:osytest:testuser:testpassword</db>
            </datasources>
            <parameters>
                <parameter name="siteName" value="Test app" />
                <parameter name="uploadRoot" value="/upload/" />
            </parameters>    
        </Test_App>
    </app>
</configuration>
```
The configuration file parts are:
- configuration tag and app subtag.
- your own app tag (Test_App in example)
- datasources section in your app tag tell osynapsy db connection to create
- parameters section in your app tag define instance parameters for your app
