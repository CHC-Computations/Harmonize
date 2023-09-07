
![alt text](https://github.com/CHC-Computations/Harmonize/blob/main/logo-1.png?raw=true)

---

# What is Harmonize?

**Harmonize** is a joint initiative of **Czech and Polish Literary Bibliography**, both being long-term existing bibliographical infrastructures operating within the National Academies of Sciences. The aim of the service is to collect, merge and provide one interface to present multilingual bibliographical metadata for literary studies stored in MARC21 format. Currently, available collections are the **Czech Literary Bibliography**, the **Polish Literary Bibliography**, and the literary content extracted from the **National Library of Finland**. The Harmonize data are presented within the VuFind discovery system. Within the **Harmonize** project framework, various software updates of VuFind adopted to the presentation of the literary bibliographical data were developed. 


# Use cases

**European Literary Bibliography**

http://literarybibliography.eu

![alt_text](https://github.com/CHC-Computations/Harmonize/blob/main/Zrzut%20ekranu%202022-12-19%20o%2017.45.07.png?raw=true)

# Publications / How to cite 

## Articles

## Presentations

## How to cite?

# Installation guide

## Prerequisites:

- Apache HTTP Server (https://httpd.apache.org/)
- PHP (https://www.php.net/manual/en/install.php)
- PostgreSQL/MariaDB/MySQL database
- SOLR (https://solr.apache.org/guide/solr/latest/getting-started/solr-tutorial.html)

**Download and place all files in the folder to be installed.**


## Preparing the database

After selecting and creating the database, you'll need to create the tables required for the system. 
You can find the relevant SQL commands in the /files/sql/create.sql folder.

## Preparing SOLR

You need to create four Solr cores. The schema.xml file for each core can be found in the respective folders: /files/solr/lite_biblio, /files/solr/lite_wiki, /files/solr/lite_persons, and /files/solr/lite_places.

## Config Files

All configuration files are located in the /config folder.

### db.php

Enter your SQL database access credentials here.

```php
$psqldb = [
  'tablePrefix' => '', // if you want to have
  'host' => '127.0.0.1', // ip or host name of your SQL host
  'dbname' => 'your_db_name',
  'user' => 'your_db_user',
  'password' => 'your_db_password'
];
```

### `settings.json`
The most crucial configuration file. It contains Solr server access information, a list of Solr indexes linked to data import functions, the structure of facet filter menus, and more.  
**Before modifying this file, make a backup copy as a precaution!**

#### Sections in `settings.json`

- **testMode**: Takes values `true/false`. Setting this to `true` will display PHP errors (if any occur) and add timestamps to all CSS and JS files in the HTML `head` section (forcing them to reload on each page visit).

- **www**: Settings for your website.  
  - **host**: The internet address where you're publishing your service.
  - **ignorePath**: The folder where your website files reside (if visible in the public URL). This fragment of the URL will be ignored when determining the `routePath` (more on this in the `modules/routers` section).

- **solr**: Access data for your Solr installation.

- **reserve**: Information for the backup server (if you're using one). This section can be completely removed if you don't have a backup server.

- **coresPrefix**: The prefix you've set for the "cores" in Solr, if you're using one. Set to an empty string if you're not using a prefix.

- **cores**: Mapping your Solr core names with names used by the system. All headers are mandatory and cannot be changed; you set the values according to your Solr core names.

- **errorWebHook**: The server (API gateway) address where a notification will be sent in case of a Solr failure. (Notify admin, automatic Solr restart—actions are at your discretion and according to your software needs).

- **multiLanguage**: Specifies the list of languages used in multilingual data fields.

- **externalHosts**: Addresses of external servers used by the system's API during data search (you can set up your own Wikidata or Wikimedia server if you want wiki data to be searched on your servers).

- **searchEngines**: External search engines to which the system will redirect when an object cannot be found on the Wikidata server.

# Getting help

# License

--- 

![alt_text](https://github.com/CHC-Computations/Harmonize/blob/main/Zrzut%20ekranu%202022-12-19%20o%2017.48.49.png?raw=true)
