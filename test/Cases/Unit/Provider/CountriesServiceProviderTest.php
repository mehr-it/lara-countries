<?php


	namespace MehrItLaraCountriesTest\Cases\Unit\Provider;


	use MehrIt\LaraCountries\CountriesManager;
	use MehrIt\LaraCountries\LanguagesManager;
	use MehrItLaraCountriesTest\Cases\Unit\TestCase;

	class CountriesServiceProviderTest extends TestCase
	{

		public function testCountriesManagerRegistered() {

			$resolved = app(CountriesManager::class);
			$this->assertInstanceOf(CountriesManager::class, $resolved);
			$this->assertSame($resolved, app(CountriesManager::class));
		}

		public function testLanguagesManagerRegistered() {

			$resolved = app(LanguagesManager::class);
			$this->assertInstanceOf(LanguagesManager::class, $resolved);
			$this->assertSame($resolved, app(LanguagesManager::class));
		}

	}