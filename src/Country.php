<?php


	namespace MehrIt\LaraCountries;


	use ArrayAccess;
	use Illuminate\Support\Str;
	use MehrIt\LaraCountries\Contracts\CountryContract;

	class Country implements CountryContract, ArrayAccess
	{
		/**
		 * @var string
		 */
		protected $iso2Code;

		/**
		 * @var string
		 */
		protected $iso3Code;

		/**
		 * @var string
		 */
		protected $name;

		/**
		 * @var string
		 */
		protected $dialingCode;

		/**
		 * Creates a new instance
		 * @param string $iso2Code The ISO2 code
		 * @param string $iso3Code The ISO3 code
		 * @param string $name The name
		 * @param string $dialingCode The dialing code
		 */
		public function __construct(string $iso2Code, string $iso3Code, string $name, string $dialingCode) {
			$this->iso2Code    = $iso2Code;
			$this->iso3Code    = $iso3Code;
			$this->name        = $name;
			$this->dialingCode = $dialingCode;
		}


		/**
		 * @inheritDoc
		 */
		public function getIso2Code(): string {
			return $this->iso2Code;
		}

		/**
		 * @inheritDoc
		 */
		public function getIso3Code(): string {
			return $this->iso3Code;
		}

		/**
		 * @inheritDoc
		 */
		public function getName(): string {
			return $this->name;
		}

		/**
		 * @inheritDoc
		 */
		public function getDialingCode(): string {
			return $this->dialingCode;
		}

		/**
		 * Sets the ISO2 code
		 * @param string $iso2Code The ISO2 code
		 * @return Country
		 */
		public function setIso2Code(string $iso2Code): Country {
			$this->iso2Code = $iso2Code;

			return $this;
		}

		/**
		 * Sets the ISO3 code
		 * @param string $iso3Code The ISO3 code
		 * @return Country
		 */
		public function setIso3Code(string $iso3Code): Country {
			$this->iso3Code = $iso3Code;

			return $this;
		}

		/**
		 * Sets the name
		 * @param string $name The name
		 * @return Country
		 */
		public function setName(string $name): Country {
			$this->name = $name;

			return $this;
		}

		/**
		 * Sets the dialing code
		 * @param string $dialingCode The dialing code
		 * @return Country
		 */
		public function setDialingCode(string $dialingCode): Country {
			$this->dialingCode = $dialingCode;

			return $this;
		}

		public function offsetExists($offset) {
			return method_exists($this, 'get' . Str::ucfirst($offset));
		}

		public function offsetGet($offset) {
			$methodName = 'get' . Str::ucfirst($offset);

			if (!method_exists($this, $methodName))
				return null;

			return $this->{$methodName}();
		}

		public function offsetSet($offset, $value) {
			$methodName = 'set' . Str::ucfirst($offset);

			if ($value !== null && method_exists($this, $methodName))
				$this->{$methodName}((string)$value);
		}

		public function offsetUnset($offset) {
			// do nothing here
		}


	}