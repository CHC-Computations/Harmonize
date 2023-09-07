
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


# Getting help

# License

--- 

![alt_text](https://github.com/CHC-Computations/Harmonize/blob/main/Zrzut%20ekranu%202022-12-19%20o%2017.48.49.png?raw=true)
