<?php


	namespace MehrIt\LaraCountries\Command;


	use Illuminate\Console\Command;
	use MehrIt\LaraCountries\Language;
	use MehrIt\LaraCountries\LanguagesManager;

	class ImportLanguagesCommand extends Command
	{

		protected $signature = 'languages:import';

		protected $description = 'Imports the language list to the database';

		public function handle(LanguagesManager $languagesManager) {

			$data = json_decode(file_get_contents(__DIR__ . '/../../resources/language_meta.json'), true);

			$counter = 0;
			foreach ($data as $code => $name) {
				$languagesManager->put(new Language(
					$code,
					$name
				), 'en');

				++$counter;
			}

			$this->info("Imported {$counter} languages [locale: en]");
		}

	}