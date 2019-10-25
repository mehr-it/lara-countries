<?php


	namespace MehrItLaraCountriesTest\Cases\Unit\Command;


	use Illuminate\Foundation\Testing\DatabaseMigrations;
	use MehrIt\LaraCountries\Model\CountryMetaData;
	use MehrItLaraCountriesTest\Cases\Unit\TestCase;

	class ImportCountriesCommandTest extends TestCase
	{
		use DatabaseMigrations;

		public function testExecute() {

			$this->artisan('countries:import');

			$this->assertDatabaseHas((new CountryMetaData)->getTable(), [
				'iso2'         => 'DE',
				'iso3'         => 'DEU',
				'dialing_code' => '49',
			]);

			$this->assertSame(249, CountryMetaData::query()->count());
		}

	}