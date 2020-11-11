<?php


	namespace MehrItLaraCountriesTest\Cases\Unit\Facade;


	use MehrIt\LaraCountries\Facades\Languages;
	use MehrIt\LaraCountries\LanguagesManager;
	use MehrItLaraCountriesTest\Cases\Unit\TestCase;

	class LanguagesTest extends TestCase
	{
		public function testAncestorCall() {
			// mock ancestor
			$mock = $this->mockAppSingleton(LanguagesManager::class, LanguagesManager::class);
			$mock->expects($this->once())
				->method('exists')
				->with('de');

			Languages::exists('de');
		}
	}