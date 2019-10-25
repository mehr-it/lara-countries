<?php


	namespace MehrIt\LaraCountries\Command;


	use Composer\Autoload\ClassLoader;
	use Illuminate\Console\Command;
	use MehrIt\LaraCountries\CountriesManager;
	use ReflectionClass;

	class ImportLocalizedCountryDataCommand extends Command
	{
		protected $signature = 'countries:importLocale {locale : The locale to import }';

		protected $description = 'Imports country locale data';

		public function handle(CountriesManager $countriesManager) {

			$locale = $this->argument('locale');

			$fn = $this->getVendorPath() . "umpirsky/country-list/data/{$locale}/country.json";
			if (!file_exists($fn)) {
				$this->error("No locale data found for \"{$locale}\"");
				exit(1);
			}


			$data = json_decode(file_get_contents($fn), true);


			$counter = 0;
			foreach($countriesManager->allIso2Codes() as $curr) {

				$countriesManager->putLocalizedData($curr, $locale, [
					'name' => ($data[$curr] ?? '') ?: ''
				]);

				++$counter;
			}


			$this->info("Imported {$counter} locale data sets [locale: {$locale}]");
		}


		protected function getVendorPath() {


			$reflector  = new ReflectionClass(ClassLoader::class);
			$vendorPath = preg_replace('/^(.*)\/composer\/ClassLoader\.php$/', '$1', $reflector->getFileName());
			if ($vendorPath && is_dir($vendorPath)) {
				return $vendorPath . '/';
			}
			throw new \RuntimeException('Unable to detect vendor path.');
		}
	}