<?php


	namespace MehrItLaraCountriesTest\Cases\Unit;


	use MehrIt\LaraCountries\Language;

	class LanguageTest extends TestCase
	{

		public function testConstructorGetters() {

			$lang = new Language(
				'de',
				'German'
			);

			$this->assertSame('German', $lang->getName());


		}

		public function testSetters() {

			$lang = new Language(
				'de',
				'German'
			);

			$this->assertSame($lang, $lang->setIso2Code('en'));
			$this->assertSame($lang, $lang->setName('English'));

			$this->assertSame('en', $lang->getIso2Code());
			$this->assertSame('English', $lang->getName());

		}

		public function testArrayAccess_read() {

			$lang = new Language(
				'de',
				'German'
			);

			$this->assertSame('de', $lang['iso2Code']);
			$this->assertSame('German', $lang['name']);
		}

		public function testArrayAccess_write() {

			$lang = new Language(
				'de',
				'German'
			);

			$lang['iso2Code'] = 'en';
			$lang['name']     = 'English';

			$this->assertSame('en', $lang->getIso2Code());
			$this->assertSame('English', $lang->getName());
			
		}

		public function testJsonSerialize() {
			$lang = new Language(
				'de',
				'German'
			);

			$this->assertSame([
				'iso2Code' => 'de',
				'name'     => 'German',
			], $lang->jsonSerialize());
		}
	}