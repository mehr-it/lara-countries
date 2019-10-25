<?php


	namespace MehrItLaraCountriesTest\Cases\Unit\Facade;


	use MehrIt\LaraCountries\CountriesManager;
	use MehrIt\LaraCountries\Facades\Countries;
	use MehrItLaraCountriesTest\Cases\Unit\TestCase;

	class CountriesTest extends TestCase
	{
		public function testAncestorCall() {
			// mock ancestor
			$mock = $this->mockAppSingleton(CountriesManager::class, CountriesManager::class);
			$mock->expects($this->once())
				->method('exists')
				->with('DE');

			Countries::exists('DE');
		}
	}