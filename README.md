# Lara Countries
[![Latest Version on Packagist](https://img.shields.io/packagist/v/mehr-it/lara-countries.svg?style=flat-square)](https://packagist.org/packages/mehr-it/lara-countries)
[![Build Status](https://travis-ci.org/mehr-it/lara-countries.svg?branch=master)](https://travis-ci.org/mehr-it/lara-countries)


Localized country and language list for Laravel with all ISO codes. 

Country list contains following data:
* alpha-2 country code (ISO 3166-1)
* alpha-3 country code (ISO 3166-1)
* Country name (in all locales)
* Dialing code (eg. 49 for Germany)


Language list contaings following data:
* alpha-2 language code (ISO 639-1)
* Language name (in all locales)


Unlike many other packages, it does not use a hardcoded list but imports the selected data to 
the database. For faster performance the data is cached locally in PHP files which are
synchronized via the central application cache.

This way you get best performance but are still free to edit data.


## Installation
Install the package using composer. Laravel's package auto discovery will do the rest for you:

    composer require  mehr-it/lara-countries
    
## Import country data
After installation you have to import data to the database: To import the country/language
list, run following command:
    
    artisan countries:import
    artisan languages:import
    
This imports the country/language list. If you need any other locale than "en", you can import them using
following command:

    artisan countries:importLocale de
    artisan languages:importLocale de
    
## Usage

You can access country information using the `Countries` facade:

    // get country information
    Countries::get('US');
    
    // get country information with other locale than the application locale
    Countries::get('US', 'de');
    
    // check if country code exists
    Countries::exists('US');
    
    // list all countries
    Countries::all();
    
    // list all countries with other locale than the application locale
    Countries::all('de');
    
    // list all iso2 codes
    Countries::allIso2Codes();
    
You can access language information using the `Languages` facade:

    // get language information
    Languages::get('en');
    
    // get language information with other locale than the application locale
    Languages::get('en', 'de');
    
    // check if language code exists
    Languages::exists('US');
    
    // list all languages
    Languages::all();
    
    // list all languages with other locale than the application locale
    Languages::all('de');
    
    // list all iso2 codes
    Languages::allIso2Codes();
