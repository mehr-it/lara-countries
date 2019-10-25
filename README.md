# Lara Countries
[![Latest Version on Packagist](https://img.shields.io/packagist/v/mehr-it/lara-countries.svg?style=flat-square)](https://packagist.org/packages/mehr-it/lara-countries)
[![Build Status](https://travis-ci.org/mehr-it/lara-countries.svg?branch=master)](https://travis-ci.org/mehr-it/lara-countries)


Localized country list for Laravel with all ISO 3166-1 codes. Contains following data:

* alpha-2 country code (ISO 3166-1)
* alpha-3 country code (ISO 3166-1)
* Country name (in all locales)
* Dialing code (eg. 49 for Germany)

Unlike many other packages, it does not use a hardcoded list but imports the selected data to 
the database. For faster performance the data is cached locally in PHP files which are
synchronized via the central application cache.

This way you get best performance but are still free to edit data.


## Installation
Install the package using composer. Laravel's package auto discovery will do the rest for you:

    composer require  mehr-it/lara-countries
    
## Import country data
After installation you have to import the country data to the database: To import the country list,
run following command:
    
    artisan countries:import
    
This imports the country list. If you need any other locale then "en", you can import them using
following command:

    artisan countries:importLocale de
    
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
