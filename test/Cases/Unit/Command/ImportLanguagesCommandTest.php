<?php


	namespace MehrItLaraCountriesTest\Cases\Unit\Command;


	use Illuminate\Foundation\Testing\DatabaseMigrations;
	use MehrIt\LaraCountries\Model\LanguageMetaData;
	use MehrItLaraCountriesTest\Cases\Unit\TestCase;

	class ImportLanguagesCommandTest extends TestCase
	{
		use DatabaseMigrations;

		public function testExecute() {

			$this->artisan('languages:import');

			$this->assertDatabaseHas((new LanguageMetaData())->getTable(), [
				'iso2' => 'de',
			]);

			$this->assertSame(609, LanguageMetaData::query()->count());
		}

	}