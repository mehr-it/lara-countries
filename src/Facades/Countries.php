<?php


	namespace MehrIt\LaraCountries\Facades;


	use Illuminate\Support\Facades\Facade;
	use MehrIt\LaraCountries\CountriesManager;

	class Countries extends Facade
	{
		/**
		 * Get the registered name of the component.
		 *
		 * @return string
		 */
		protected static function getFacadeAccessor() {
			return CountriesManager::class;
		}


	}