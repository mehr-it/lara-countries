<?php


	namespace MehrIt\LaraCountries\Facades;


	use Illuminate\Support\Facades\Facade;
	use MehrIt\LaraCountries\LanguagesManager;

	class Languages extends Facade
	{
		/**
		 * Get the registered name of the component.
		 *
		 * @return string
		 */
		protected static function getFacadeAccessor() {
			return LanguagesManager::class;
		}


	}