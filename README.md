
![alt text](https://github.com/CHC-Computations/Harmonize/blob/main/logo-1.png?raw=true)

---

![alt text](https://github.com/CHC-Computations/Harmonize/blob/main/harmonize2.png?raw=true)
# What is Harmonize?

**Harmonize** is a joint initiative of **Czech and Polish Literary Bibliography**, both being long-term existing bibliographical infrastructures operating within the National Academies of Sciences. The aim of the service is to collect, merge and provide one interface to present multilingual bibliographical metadata for literary studies stored in MARC21 format. Currently, available collections are the **Czech Literary Bibliography**, the **Polish Literary Bibliography**, and the literary content extracted from the **National Library of Finland**. The Harmonize data are presented within the VuFind discovery system. Within the **Harmonize** project framework, various software updates of VuFind adopted to the presentation of the literary bibliographical data were developed. 


# Use cases

**European Literary Bibliography**

http://literarybibliography.eu

![alt_text](https://github.com/CHC-Computations/Harmonize/blob/main/Zrzut%20ekranu%202022-12-19%20o%2017.45.07.png?raw=true)

# Publications / How to cite 

## Articles

- Umerle, T., & Malínek, V. (2022). Literarybibliography. eu—modelový příklad pro tvorbu mezinárodní oborové bibliografie. Ceska Literatura, (5).

## Presentations


- Hubar, P.; Malinek, V.; Peter, R. (2022). Literarybibliography.eu: harmonizing European bibliographical data on literature. DH_BUDAPEST_2022 & DARIAH DAYS 3rd International Digital Humanities Conference.
- Hubar, P.; Giersz, M. (2023). European Literary Bibliography: aggregation and harmonization of literary bibliographical data. TRIPLE 2023 Conference.
- Hubar, P.; Giersz, M. (2022). Literarybibliography.eu: harmonizing European bibliographical data on literature. Konferencja DARIAH-PL, Poznań 2022.
## How to cite?

Instytut Badań Literackich PAN. (2023). *Harmonize (Version 1.0)* [Software]. 


```
@software{harmonize2023,
    author = {Instytut Badań Literackich PAN},
    title = {Harmonize},
    year = {2023},
    version = {1.0},
}
 ```


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

There is an additional import configuration file in the `/import/config.php` folder. Among other things, it contains the path to the mrk files you want to import.
To run the pre-indexation, you type the php script /import/pre.read.php in the terminal in the system installation folder (call php /import/pre.read.php in the terminal). The script will look through all the attached mrk files and, based on these, create a list of people, places, corporations and events. The data in these lists will be enriched (if they do not have) with wikidata identifiers. In the browsing process, solr core: wikidata will populate with data taken from wikidata for each of the objects found.
Then, the selected data for found persons, places, etc will be written to the persons, places, etc collections, and as a side effect, the wikidata collection will be populated with all objects describing the processed data (gender records, occupations, species, etc).


## Main Indexing

The process may (not necessarily) be preceded by a pre-indexation. If we precede the main indexation with a pre-indexation, the people and places appearing in the bibliographic records will be 'linked' to the corresponding wikidata records.
During indexation, the lite_biblio collection will be populated with data.


## Cleaning and Reindexing

1. stop solr

2. ```bash sudo rm -rf {{hamonize_home}}/files/solr/lite_biblio/data/index {{hamonize_home}}/files/solr/lite_biblio/data/spell* ```
3. ``` bashsudo rm -rf {{hamonize_home}}/files/solr/lite_wikidata/data/index {{hamonize_home}}/files/solr/lite_wikidata/data/spell* ```
 note: clearing this index extends the reindexing considerably. The collection contains only clean wikidata. If you keep this collection during subsequent indexations, the importer will not need to query external servers. Other methods update selected wikidata records (based on their frequency of use and estimated probability of change, e.g. records of living people are updated more frequently because they are more likely to have a new date and place).
4. start solr


To clean and reindex, execute the following commands:

```bash
sudo rm -rf {{hamonize_home}}/files/solr/lite_biblio/data/index {{hamonize_home}}/files/solr/lite_biblio/data/spell*
sudo rm -rf {{hamonize_home}}/files/solr/lite_wikidata/data/index {{hamonize_home}}/files/solr/lite_wikidata/data/spell*
```

## Templates

In the `/themes/{your_template}/templates` directory, you can place your template files. The lite version has limited template support, so it's recommended to copy and modify the default template. You can use your own name and specify it in `settings.json`, although this hasn't been extensively tested.

- `/themes/{your_template}/css`: Place for storing CSS files. All CSS files in this directory will be added to the `<head>` section of the generated webpage.
- `/themes/{your_template}/js`: Place for storing JavaScript files. All JavaScript files in this directory will be added to the `<head>` section of the generated webpage.
- `/themes/{your_template}/images`: A place for your graphics.
- `/themes/{your_template}/templates`: A place for PHP visualization files. Access these templates in executable files via `echo $this->render(pathToFile)`.

For example, `echo $this->render('persons/core.php', $params)` will execute the file `/themes/{your_template}/templates/persons/core.php`, and all variables passed as `$params` will be available to the template file. You can also refer to global and system variables via `$this->variable_name`.

## Modules

You can freely extend the system by adding your own classes (groups of functions) and individual sub-addresses within the system's domain.

### Classes

In the `/functions` folder, you will find function classes. If you want to add a new class and have its methods available throughout the program, place your class in this folder.

For instance, if you include a `public function register($key, $value)` method, you can register other active classes under the `$this->cms` variable in your class, giving methods of that class access to the system's resources.

### Default classes
#### class.cms.php
It's a content management system class. It contains basic functions that’s allowed to control content of the webpage.
#### class.importer.php
Contains all important import methods. This class methods are working only in terminal mode (doesn’t take a part in web creating process). Methods of this class prepare data for solr by converting mrk fields and subfields into solr indexes. 
#### class.buffer.php
Contains all buffering methods. All connections with external api services should be realized by using methods of this class. 
#### class.helper.php
contains less important methods f.e. visualization, acceleration of code creations or other helpful methods which are universal for template content.  


## Routers
The /routers folder contains executable files.
For example, the file: /routers/tests/test.php will be executed after calling the address of http://yourDomain/en/tests/test
If you call the address
http://yourDomain/en/tests/test/word1/word2?a=b etc
subsequent phrases placed in the called address will be available in your executable under the array variable $this->routeParams, the array index (from zero) contains subsequent phrases. In the above case, $this->routeParams[0] is "word1" and $this->routeParams[1] will be "word2".
$this->GET keeps the values of the $_GET variable converted by the urldecode function.
In our example $this->GET[‘a’] == ‘b’ 
### Default routers
#### home
Handles the presentation of the permanent content of the website: homepage, instructions, about us page
#### search 
Responsible for the presentation and search of bibliographic resources
#### persons
Responsible for presenting and searching indexed persons (authors, co-authors, subject persons)
#### places
Responsible for presenting and searching indexed places and geographic information.


## How to Administer

### Solr

For stable system operation, it is good practice to regularly restart Solr. This clears its temporary files and frees up memory.

### Temporary Files

During its operation, Harmonize may generate and save various temporary files.

- `/files/downloaded/`: Buffer files downloaded from other services are saved here. You can freely delete the contents to free up disk space.
- `/files/exports/`: Contains temporary export files and files available for user download during exports. You can freely delete older files and folders.


# Getting help

**Get in touch**: computations@ibl.waw.pl

# License

GNU General Public License v3.0	gpl-3.0

--- 

![alt_text](https://github.com/CHC-Computations/Harmonize/blob/main/Zrzut%20ekranu%202022-12-19%20o%2017.48.49.png?raw=true)
