<?php


	namespace MehrItLaraCountriesTest\Cases\Unit;


	use MehrIt\LaraCountries\Country;

	class CountryTest extends TestCase
	{

		public function testConstructorGetters() {

			$country = new Country(
				'DE',
				'DEU',
				'Deutschland',
				'49'
			);

			$this->assertSame('DE', $country->getIso2Code());
			$this->assertSame('DEU', $country->getIso3Code());
			$this->assertSame('Deutschland', $country->getName());
			$this->assertSame('49', $country->getDialingCode());

		}

		public function testSetters() {

			$country = new Country(
				'DE',
				'DEU',
				'Deutschland',
				'49'
			);

			$this->assertSame($country, $country->setIso2Code('US'));
			$this->assertSame($country, $country->setIso3Code('USA'));
			$this->assertSame($country, $country->setName('United states of Amerika'));
			$this->assertSame($country, $country->setDialingCode('1'));

			$this->assertSame('US', $country->getIso2Code());
			$this->assertSame('USA', $country->getIso3Code());
			$this->assertSame('United states of Amerika', $country->getName());
			$this->assertSame('1', $country->getDialingCode());

		}

		public function testArrayAccess_read() {

			$country = new Country(
				'DE',
				'DEU',
				'Deutschland',
				'49'
			);

			$this->assertSame('DE', $country['iso2Code']);
			$this->assertSame('DEU', $country['iso3Code']);
			$this->assertSame('Deutschland', $country['name']);
			$this->assertSame('49', $country['dialingCode']);

		}

		public function testArrayAccess_write() {

			$country = new Country(
				'DE',
				'DEU',
				'Deutschland',
				'49'
			);

			$country['iso2Code']    = 'US';
			$country['iso3Code']    = 'USA';
			$country['name']        = 'United states of Amerika';
			$country['dialingCode'] = '1';

			$this->assertSame('US', $country->getIso2Code());
			$this->assertSame('USA', $country->getIso3Code());
			$this->assertSame('United states of Amerika', $country->getName());
			$this->assertSame('1', $country->getDialingCode());

		}

		public function testJsonSerialize() {
			$country = new Country(
				'DE',
				'DEU',
				'Deutschland',
				'49'
			);

			$this->assertSame([
				'iso2Code'    => 'DE',
				'iso3Code'    => 'DEU',
				'name'        => 'Deutschland',
				'dialingCode' => '49',
			], $country->jsonSerialize());
		}

//		public function testConvert() {
//			$data = json_decode(file_get_contents(__DIR__ . '/../../../resource/countries.json'), true);
//
//			$out = [];
//			foreach($data as $currData) {
//				$out[] = [
//					'iso2' => $currData['iso_3166_2'],
//					'iso3' => $currData['iso_3166_3'],
//					'name' => $currData['name'],
//					'dialing_code' => $currData['calling_code'],
//				];
//			}
//
//			file_put_contents(__DIR__ . '/../../../resource/country_meta.json', json_encode($out));
//		}

	}