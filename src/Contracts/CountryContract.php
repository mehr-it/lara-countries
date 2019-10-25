<?php


	namespace MehrIt\LaraCountries\Contracts;


	interface CountryContract
	{

		/**
		 * Gets the ISO2 code
		 * @return string The iso2 code
		 */
		public function getIso2Code(): string;

		/**
		 * Gets the ISO3 code
		 * @return string The ISO3 code
		 */
		public function getIso3Code(): string;

		/**
		 * Gets the country name
		 * @return string The country name
		 */
		public function getName(): string;

		/**
		 * Gets the dialing code
		 * @return string The dialing code
		 */
		public function getDialingCode(): string;

	}