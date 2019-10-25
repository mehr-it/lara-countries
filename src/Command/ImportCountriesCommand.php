<?php


	namespace MehrIt\LaraCountries\Command;


	use Illuminate\Console\Command;
	use MehrIt\LaraCountries\CountriesManager;
	use MehrIt\LaraCountries\Country;

	class ImportCountriesCommand extends Command
	{

		protected $signature = 'countries:import';

		protected $description = 'Imports the country list to the database';

		public function handle(CountriesManager $countriesManager) {

			$data = json_decode(file_get_contents(__DIR__ . '/../../resources/country_meta.json'), true);

			$counter = 0;
			foreach($data as $curr) {
				$countriesManager->put(new Country(
					$curr['iso2'],
					$curr['iso3'],
					$curr['name'],
					$curr['dialing_code']
				), 'en');

				++$counter;
			}

			$this->info("Imported {$counter} countries [locale: en]");
		}

	}