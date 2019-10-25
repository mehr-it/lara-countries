<?php


	namespace MehrItLaraCountriesTest\Cases\Unit\Command;


	use Illuminate\Foundation\Testing\DatabaseMigrations;
	use MehrIt\LaraCountries\Model\CountryLocalizedData;
	use MehrIt\LaraCountries\Model\CountryMetaData;
	use MehrItLaraCountriesTest\Cases\Unit\TestCase;

	class ImportLocalizedCountryDataCommandTest extends TestCase
	{
		use DatabaseMigrations;

		public function testExecute() {

			$this->artisan('countries:import');
			$this->artisan('countries:importLocale de');

			$this->assertDatabaseHas((new CountryLocalizedData())->getTable(), [
				'iso2'         => 'DE',
				'data'         => json_encode(['name' => 'Deutschland']),
			]);

			$this->assertSame(CountryMetaData::query()->count(), CountryLocalizedData::query()->where('locale', 'de')->count());
		}
	}