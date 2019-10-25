<?php


	namespace MehrItLaraCountriesTest\Cases\Unit\Provider;


	use MehrIt\LaraCountries\CountriesManager;
	use MehrItLaraCountriesTest\Cases\Unit\TestCase;

	class CountriesServiceProviderTest extends TestCase
	{

		public function testCountriesManagerRegistered() {

			$resolved = app(CountriesManager::class);
			$this->assertInstanceOf(CountriesManager::class, $resolved);
			$this->assertSame($resolved, app(CountriesManager::class));
		}

	}