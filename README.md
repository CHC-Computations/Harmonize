
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
- **pretest**: A list of Solr indexes that must be checked during pre-indexing (functions that populate these indexes will be described below in the `facets/solrIndexes` section of this same file).

#### Facets

- **solrIndexes**: A list of all Solr indexes in the `biblio` core. If you want to change something about the indexing, this is where you'll find the method names from `class.import.php` used during data population. If the data comes directly from a specific field in MRK files, you'll find an `importField` here, indicating the data address in the MRK file.  
Each index is also linked to its display name in the system. Information on which formatter (from `class.helper.php`) should be used for formatting data display on the page is included, along with whether the displayed data should be translated.

- **defaults**: Contains default settings for the facet menu of the `biblio` section.

- **facetsMenu**: Defines the facet filter menu.

#### Export

- **max**: The maximum number of records in exported files (important for your server's performance. Exporting consumes a lot of Solr resources, disk space, and CPU and memory during export compression). A full copy of the exported files is stored on disk during each export. How to delete old export files is described in the administration section. Leftover files (files prepared for earlier exports) are automatically removed during each export if they were created a day before the current operation.

> **Note**: Some export parameters are set in the `exports.ini` file (described below).

### persons.json
File with a structure similar to `settings.json`, defining the menu and some settings for the list of persons.  
- **sorting**: New section that defines available sorting methods by linking the sorting code (visible in the URL) with the displayed name and the corresponding Solr command.

### author-classification.ini
Configuration file prepared by VuFind creators. It associates creative role codes in works with their names. You can extend it with your unique codes if they appear in your bibliographic records.

### cookies.ini
Defines cookie messages in all interface languages. If a message is missing in the selected language, the first line of the file will be displayed.

### export.ini
Defines available (displayed) data export methods in the system. You can delete/comment out any line if you want to hide a particular sorting method or add a new one (this will require you to program a new method). In future versions, these settings will be moved to `settings.json`.

### institutions.ini
Defines indexes and their headers displayed in "Bibliographical statistics" and "Comparison of roles in bibliography" on the template card of corporate authors.

### persons.ini
Defines indexes and their headers displayed in "Bibliographical statistics" and "Comparison of roles in bibliography" on the template card of persons.

### places.ini
Defines indexes and their headers displayed in "Bibliographical statistics" and "Comparison of roles in bibliography" on the template card of places.

### search.ini
Defines some settings for searching bibliographic records.

### analytics.js
If you want to include additional code at the end of the displayed page within a `<script>` tag, place it in this file. Useful for page view analytics codes (e.g., Matomo or Google Analytics).

---

**Additional Settings**:  
Additional settings are located in the `/config/import` and `/config/properties` folders. Explanations for these files can be found in the comments within the files.


## Data Indexing

### Viaf to Wikidata ID

To convert VIAF identifiers to Wikidata IDs, you'll need a dump file downloaded from VIAF [here](https://viaf.org/viaf/data/viaf-{currentDate}-links.txt.gz). Save and unzip this file in the `/import` folder.

Then run the following command from the Harmonize system installation folder:

```bash
php /import/viaf2wiki.php
```
This script will extract identifier pairs from the downloaded file and feed them into the `viaf2wiki` function used during pre-indexing and indexing.

### Pre-Indexing

In the `/import/config.php` folder, there's an additional import configuration file, which includes the path to the `.mrk` files you want to import.

To initiate pre-indexing, execute the following command:

```bash
php /import/pre.read.php
```

## Main Indexing

This process can be preceded by pre-indexing. During main indexing, the `lite_biblio` collection will be filled with data.

## Cleaning and Reindexing

To clean and reindex, execute the following commands:

```bash
sudo rm -rf {{hamonize_home}}/files/solr/lite_biblio/data/index {{hamonize_home}}/files/solr/lite_biblio/data/spell*
sudo rm -rf {{hamonize_home}}/files/solr/lite_wikidata/data/index {{hamonize_home}}/files/solr/lite_wikidata/data/spell*
```









# Getting help

# License

--- 

![alt_text](https://github.com/CHC-Computations/Harmonize/blob/main/Zrzut%20ekranu%202022-12-19%20o%2017.48.49.png?raw=true)
