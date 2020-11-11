<?php


	namespace MehrIt\LaraCountries\Contracts;


	interface LanguageContract
	{
		/**
		 * Gets the ISO2 code
		 * @return string The iso2 code
		 */
		public function getIso2Code(): string;

		/**
		 * Gets the language name
		 * @return string The language name
		 */
		public function getName(): string;
	}