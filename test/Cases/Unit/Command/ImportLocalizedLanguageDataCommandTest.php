<?php


	namespace MehrItLaraCountriesTest\Cases\Unit\Command;


	use Illuminate\Foundation\Testing\DatabaseMigrations;
	use MehrIt\LaraCountries\Model\LanguageLocalizedData;
	use MehrIt\LaraCountries\Model\LanguageMetaData;
	use MehrItLaraCountriesTest\Cases\Unit\TestCase;

	class ImportLocalizedLanguageDataCommandTest extends TestCase
	{
		use DatabaseMigrations;

		public function testExecute() {

			$this->artisan('languages:import');
			$this->artisan('languages:importLocale de');

			$this->assertDatabaseHas((new LanguageLocalizedData())->getTable(), [
				'iso2' => 'de',
				'data' => json_encode(['name' => 'Deutsch']),
			]);

			$this->assertSame(LanguageMetaData::query()->count(), LanguageLocalizedData::query()->where('locale', 'de')->count());
		}
	}