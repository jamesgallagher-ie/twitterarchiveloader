twitterarchiveloader
====================

ThinkUp Plugin Twitter Archive Loader

This is a novice PHP programmer's effort to create a ThinkUp plugin which will load tweets not currently loaded into ThinkUp (by virtue of Twitter API limitations) from a Twitter Archive file.

The plugin is alpha quality at the moment. It loads data, correctly as I understand things and supports the following usage
* Upload of a ZIP tweet archive through Plugin settings
* Upload of a ZIP tweet archive directly to the ThinkUp installation.

It works as follows:
* Uploading a ZIP archive for a specific twitter user  creates, under the <ThinkUp dir>/data directory, a 'twitterarchiveloader' directory and below that a directory for the twitter username
* Alternatively you can create this directory structure yourself (the twitter username is treated as case sensitive) if you have large ZIP archives
* When a crawl is run the plugin goes through the directories under <ThinkUp dir>/data/twitterarchiveloader/ looking for ZIP archives
    * Each zip archive is searched for files which match YYYY_MM.js - the Twitter export creates JSON files containing a month of tweets e.g. 2007_03.js
    * All matching files are extracted to <ThinkUp dir>/data/twitterarchiveloader/<twitter username>/tweets/
    * Each file is loaded and and the JSON array extracted, parsed as JSON and converted to a tweet
    * These tweets are then loaded into the posts table.

The ZIP archives uploaded can be the ZIP as downloaded from Twitter or you can create a ZIP archive yourself with a subset of the data (YYYY_MM.js files). 
