<?php


	namespace MehrIt\LaraCountries;


	use JsonSerializable;
	use MehrIt\LaraCountries\Contracts\LanguageContract;

	class Language extends AbstractEntity implements LanguageContract, JsonSerializable
	{
		/**
		 * @var string
		 */
		protected $iso2Code;

		/**
		 * @var string
		 */
		protected $name;


		/**
		 * Creates a new instance
		 * @param string $iso2Code The ISO2 code
		 * @param string $name The name
		 */
		public function __construct(string $iso2Code, string $name) {
			$this->iso2Code    = $iso2Code;
			$this->name        = $name;
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
		public function getName(): string {
			return $this->name;
		}

		/**
		 * Sets the ISO2 code
		 * @param string $iso2Code The ISO2 code
		 * @return Language
		 */
		public function setIso2Code(string $iso2Code): Language {
			$this->iso2Code = $iso2Code;

			return $this;
		}

		/**
		 * Sets the name
		 * @param string $name The name
		 * @return Language
		 */
		public function setName(string $name): Language {
			$this->name = $name;

			return $this;
		}



		/**
		 * @inheritDoc
		 */
		public function jsonSerialize() {
			return [
				'iso2Code' => $this->getIso2Code(),
				'name'     => $this->getName(),
			];
		}


	}