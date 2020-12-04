<?php


	namespace MehrIt\LaraCountries\Command;


	use Composer\Autoload\ClassLoader;
	use Illuminate\Console\Command;
	use MehrIt\LaraCountries\LanguagesManager;
	use ReflectionClass;
	use RuntimeException;

	class ImportLocalizedLanguageDataCommand extends Command
	{
		protected $signature = 'languages:importLocale {locale : The locale to import }';

		protected $description = 'Imports language locale data';

		public function handle(LanguagesManager $languagesManager) {

			$locale = $this->argument('locale');

			$fn = $this->getVendorPath() . "umpirsky/language-list/data/{$locale}/language.json";
			if (!file_exists($fn)) {
				$this->error("No locale data found for \"{$locale}\"");
				exit(1);
			}


			$data = json_decode(file_get_contents($fn), true);


			$counter = 0;
			foreach ($languagesManager->allIso2Codes() as $curr) {

				$languagesManager->putLocalizedData($curr, $locale, [
					'name' => ($data[$curr] ?? '') ?: ''
				]);

				++$counter;
			}


			$this->info("Imported {$counter} locale data sets [locale: {$locale}]");

			return 0;
		}


		protected function getVendorPath() {


			$reflector  = new ReflectionClass(ClassLoader::class);
			$vendorPath = preg_replace('/^(.*)\/composer\/ClassLoader\.php$/', '$1', $reflector->getFileName());
			if ($vendorPath && is_dir($vendorPath)) {
				return $vendorPath . '/';
			}
			throw new RuntimeException('Unable to detect vendor path.');
		}
	}