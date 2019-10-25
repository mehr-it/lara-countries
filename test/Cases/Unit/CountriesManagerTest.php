<?php


	namespace MehrItLaraCountriesTest\Cases\Unit;


	use Carbon\Carbon;
	use Illuminate\Database\Query\Expression;
	use Illuminate\Foundation\Testing\DatabaseMigrations;
	use Illuminate\Support\Facades\Cache;
	use MehrIt\LaraCountries\CountriesManager;
	use MehrIt\LaraCountries\Country;
	use MehrIt\LaraCountries\Model\CountryLocalizedData;
	use MehrIt\LaraCountries\Model\CountryMetaData;
	use MehrIt\LaraCountries\Util\PhpArrayCache;
	use PHPUnit\Framework\MockObject\MockObject;

	class CountriesManagerTest extends TestCase
	{
		use DatabaseMigrations;


		/**
		 * Mocks the array cache
		 * @return PhpArrayCache|MockObject
		 */
		protected function mockArrayCache() {

			$data = [];

			/** @var PhpArrayCache|MockObject $mock */
			$mock = $this->getMockBuilder(PhpArrayCache::class)->disableOriginalConstructor()->getMock();
			$mock
				->method('get')
				->willReturnCallback(function(string $key) use (&$data) {
					$ret = $data[$key] ?? [];

					return is_array($ret) ? $ret : [];
				});
			$mock
				->method('put')
				->willReturnCallback(function(string $key, array $v) use (&$data, $mock) {
					$data[$key] = $v;

					return $mock;
				});
			$mock
				->method('purge')
				->willReturnCallback(function() use (&$data, $mock) {
					$data = [];

					return $mock;
				});


			return $mock;
		}


		public function testPutGet() {

			$arrayCache = $this->mockArrayCache();

			$manager = new CountriesManager(
				$arrayCache,
				Cache::store()
			);

			$country1 = new Country(
				'DE',
				'DEU',
				'Deutschland',
				'49'
			);

			$country2 = new Country(
				'US',
				'USA',
				'Vereinigte Staaten von Amerika',
				'1'
			);

			$this->assertSame($manager, $manager->put($country1, 'de'));
			$this->assertSame($manager, $manager->put($country2, 'de'));

			$this->assertEquals($country1, $manager->get('DE', 'de'));
			$this->assertEquals($country2, $manager->get('US', 'de'));
			$this->assertSame(null, $manager->get('ES', 'de'));


			// clear cache and db is empty
			CountryMetaData::query()->delete();
			CountryLocalizedData::query()->delete();
			$arrayCache->purge();

			// cache is empty => should be read from memory now
			$this->assertEquals($country1, $manager->get('DE', 'de'));
			$this->assertEquals($country2, $manager->get('US', 'de'));
			$this->assertSame(null, $manager->get('ES', 'de'));
		}

		public function testPutGet_withDefaultLocale() {

			$arrayCache = $this->mockArrayCache();

			$manager = new CountriesManager(
				$arrayCache,
				Cache::store()
			);

			$country1 = new Country(
				'DE',
				'DEU',
				'Deutschland',
				'49'
			);

			$country2 = new Country(
				'US',
				'USA',
				'Vereinigte Staaten von Amerika',
				'1'
			);

			$this->assertSame($manager, $manager->put($country1, app()->getLocale()));
			$this->assertSame($manager, $manager->put($country2, app()->getLocale()));

			$this->assertEquals($country1, $manager->get('DE'));
			$this->assertEquals($country2, $manager->get('US'));
			$this->assertSame(null, $manager->get('ES'));


			// clear cache and db is empty
			CountryMetaData::query()->delete();
			CountryLocalizedData::query()->delete();
			$arrayCache->purge();

			// cache is empty => should be read from memory now
			$this->assertEquals($country1, $manager->get('DE'));
			$this->assertEquals($country2, $manager->get('US'));
			$this->assertSame(null, $manager->get('ES'));
		}

		public function testPutGet_modifiedByOtherInstance() {

			$arrayCache = $this->mockArrayCache();

			Carbon::setTestNow(Carbon::createFromTimestamp(time()));

			$manager = new CountriesManager(
				$arrayCache,
				Cache::store(),
				null,
				0
			);

			$country1 = new Country(
				'DE',
				'DEU',
				'Deutschland',
				'49'
			);

			$country2 = new Country(
				'US',
				'USA',
				'Vereinigte Staaten von Amerika',
				'1'
			);

			$this->assertSame($manager, $manager->put($country1, 'de'));
			$this->assertSame($manager, $manager->put($country2, 'de'));

			// load to cache in first instance
			$manager->get('DE', 'de');
			$manager->get('US', 'de');
			$manager->get('ES', 'de');

			// shift time
			Carbon::setTestNow(Carbon::createFromTimestamp(time() + 1));

			// modify
			$manager2 = new CountriesManager(
				$arrayCache,
				Cache::store(),
				null,
				0
			);
			$country1 = new Country(
				'DE',
				'DDD',
				'GER',
				'59'
			);
			$country2 = new Country(
				'US',
				'UUU',
				'USA',
				'89'
			);
			$manager2->put($country1, 'de');
			$manager2->put($country2, 'de');


			// cache is empty => should be read from memory now
			$this->assertEquals($country1, $manager->get('DE', 'de'));
			$this->assertEquals($country2, $manager->get('US', 'de'));
			$this->assertSame(null, $manager->get('ES', 'de'));
		}

		public function testPutGet_cacheInvalidatedByOtherInstance() {

			$arrayCache = $this->mockArrayCache();

			Carbon::setTestNow(Carbon::createFromTimestamp(time()));

			$manager = new CountriesManager(
				$arrayCache,
				Cache::store(),
				null,
				0
			);

			$country1 = new Country(
				'DE',
				'DEU',
				'Deutschland',
				'49'
			);

			$country2 = new Country(
				'US',
				'USA',
				'Vereinigte Staaten von Amerika',
				'1'
			);

			$this->assertSame($manager, $manager->put($country1, 'de'));
			$this->assertSame($manager, $manager->put($country2, 'de'));

			// load to cache in first instance
			$manager->get('DE', 'de');
			$manager->get('US', 'de');
			$manager->get('ES', 'de');

			// shift time
			Carbon::setTestNow(Carbon::createFromTimestamp(time() + 1));


			// modify db
			CountryMetaData::query()
				->update(['iso3' => 'MOD']);
			CountryLocalizedData::query()
				->update(['data' => json_encode(['name' => 'modified'])]);

			// invalidate cache
			$manager2 = new CountriesManager(
				$arrayCache,
				Cache::store(),
				null,
				0
			);
			$manager2->invalidateCache();


			// cache is invalid => should be read from DB
			$this->assertEquals('MOD', $manager->get('DE', 'de')->getIso3Code());
			$this->assertEquals('MOD', $manager->get('US', 'de')->getIso3Code());
			$this->assertEquals('modified', $manager->get('DE', 'de')->getName());
			$this->assertEquals('modified', $manager->get('US', 'de')->getName());
			$this->assertSame(null, $manager->get('ES', 'de'));
		}

		public function testPutGet_differentLocale_notSet() {

			$arrayCache = $this->mockArrayCache();

			$manager = new CountriesManager(
				$arrayCache,
				Cache::store()
			);

			$country1 = new Country(
				'DE',
				'DEU',
				'Deutschland',
				'49'
			);

			$country2 = new Country(
				'US',
				'USA',
				'Vereinigte Staaten von Amerika',
				'1'
			);

			$this->assertSame($manager, $manager->put($country1, 'de'));
			$this->assertSame($manager, $manager->put($country2, 'de'));

			$expCountry1 = clone $country1;
			$expCountry1->setName('');

			$expCountry2 = clone $country2;
			$expCountry2->setName('');

			$this->assertEquals($expCountry1, $manager->get('DE', 'en'));
			$this->assertEquals($expCountry2, $manager->get('US', 'en'));
			$this->assertSame(null, $manager->get('ES', 'en'));

			$this->assertEquals($country1, $manager->get('DE', 'de'));
			$this->assertEquals($country2, $manager->get('US', 'de'));
			$this->assertSame(null, $manager->get('ES', 'de'));

			// clear cache and db is empty
			CountryMetaData::query()->delete();
			CountryLocalizedData::query()->delete();
			$arrayCache->purge();


			// cache is empty => should be read from memory now
			$this->assertEquals($expCountry1, $manager->get('DE', 'en'));
			$this->assertEquals($expCountry2, $manager->get('US', 'en'));
			$this->assertSame(null, $manager->get('ES', 'en'));

			$this->assertEquals($country1, $manager->get('DE', 'de'));
			$this->assertEquals($country2, $manager->get('US', 'de'));
			$this->assertSame(null, $manager->get('ES', 'de'));
		}

		public function testPutGet_differentLocale_set() {

			$arrayCache = $this->mockArrayCache();

			$manager = new CountriesManager(
				$arrayCache,
				Cache::store()
			);

			$country1 = new Country(
				'DE',
				'DEU',
				'Deutschland',
				'49'
			);

			$country2 = new Country(
				'US',
				'USA',
				'Vereinigte Staaten von Amerika',
				'1'
			);

			$this->assertSame($manager, $manager->put($country1, 'de'));
			$this->assertSame($manager, $manager->put($country2, 'de'));
			$this->assertSame($manager, $manager->putLocalizedData('DE', 'en', ['name' => 'Germany']));
			$this->assertSame($manager, $manager->putLocalizedData('US', 'en', ['name' => 'United States']));


			$expCountry1 = clone $country1;
			$expCountry1->setName('Germany');

			$expCountry2 = clone $country2;
			$expCountry2->setName('United States');

			$this->assertEquals($expCountry1, $manager->get('DE', 'en'));
			$this->assertEquals($expCountry2, $manager->get('US', 'en'));
			$this->assertSame(null, $manager->get('ES', 'en'));

			$this->assertEquals($country1, $manager->get('DE', 'de'));
			$this->assertEquals($country2, $manager->get('US', 'de'));
			$this->assertSame(null, $manager->get('ES', 'de'));


			// clear cache and db is empty
			CountryMetaData::query()->delete();
			CountryLocalizedData::query()->delete();
			$arrayCache->purge();


			// cache is empty => should be read from memory now
			$this->assertEquals($expCountry1, $manager->get('DE', 'en'));
			$this->assertEquals($expCountry2, $manager->get('US', 'en'));
			$this->assertSame(null, $manager->get('ES', 'en'));

			$this->assertEquals($country1, $manager->get('DE', 'de'));
			$this->assertEquals($country2, $manager->get('US', 'de'));
			$this->assertSame(null, $manager->get('ES', 'de'));
		}

		public function testPutGet_fallbackLocale() {

			$arrayCache = $this->mockArrayCache();

			$manager = new CountriesManager(
				$arrayCache,
				Cache::store()
			);

			$country1 = new Country(
				'DE',
				'DEU',
				'Deutschland',
				'49'
			);

			$country2 = new Country(
				'US',
				'USA',
				'Vereinigte Staaten von Amerika',
				'1'
			);

			config()->set('app.fallback_locale', 'de');

			$this->assertSame($manager, $manager->put($country1, 'de'));
			$this->assertSame($manager, $manager->put($country2, 'de'));


			$expCountry1 = clone $country1;
			$expCountry1->setName('Germany');

			$expCountry2 = clone $country2;
			$expCountry2->setName('United States');

			$this->assertEquals($country1, $manager->get('DE'));
			$this->assertEquals($country2, $manager->get('US'));
			$this->assertSame(null, $manager->get('ES'));



			// clear cache and db is empty
			CountryMetaData::query()->delete();
			CountryLocalizedData::query()->delete();
			$arrayCache->purge();


			// cache is empty => should be read from memory now
			$this->assertEquals($country1, $manager->get('DE'));
			$this->assertEquals($country2, $manager->get('US'));
			$this->assertSame(null, $manager->get('ES'));
		}


		public function testPutAll_withDifferentLocales() {

			$arrayCache = $this->mockArrayCache();

			$manager = new CountriesManager(
				$arrayCache,
				Cache::store()
			);

			$country1 = new Country(
				'DE',
				'DEU',
				'Deutschland',
				'49'
			);

			$country2 = new Country(
				'US',
				'USA',
				'Vereinigte Staaten von Amerika',
				'1'
			);

			$this->assertSame($manager, $manager->put($country1, 'de'));
			$this->assertSame($manager, $manager->put($country2, 'de'));
			$this->assertSame($manager, $manager->putLocalizedData('DE', 'en', ['name' => 'Germany']));
			$this->assertSame($manager, $manager->putLocalizedData('US', 'en', ['name' => 'United States']));


			$expCountry1 = clone $country1;
			$expCountry1->setName('Germany');

			$expCountry2 = clone $country2;
			$expCountry2->setName('United States');

			$this->assertEquals([$expCountry1, $expCountry2], $manager->all( 'en')->toArray());
			$this->assertEquals([$country1, $country2], $manager->all( 'de')->toArray());


			// clear cache and db is empty
			CountryMetaData::query()->delete();
			CountryLocalizedData::query()->delete();
			$arrayCache->purge();


			// cache is empty => should be read from memory now
			$this->assertEquals([$expCountry1, $expCountry2], $manager->all('en')->toArray());
			$this->assertEquals([$country1, $country2], $manager->all('de')->toArray());
		}

		public function testPutAll_withDefaultLocale() {

			$arrayCache = $this->mockArrayCache();

			$manager = new CountriesManager(
				$arrayCache,
				Cache::store()
			);

			$country1 = new Country(
				'DE',
				'DEU',
				'Deutschland',
				'49'
			);

			$country2 = new Country(
				'US',
				'USA',
				'Vereinigte Staaten von Amerika',
				'1'
			);

			$this->assertSame($manager, $manager->put($country1, app()->getLocale()));
			$this->assertSame($manager, $manager->put($country2, app()->getLocale()));



			$this->assertEquals([$country1, $country2], $manager->all()->toArray());


			// clear cache and db is empty
			CountryMetaData::query()->delete();
			CountryLocalizedData::query()->delete();
			$arrayCache->purge();


			// cache is empty => should be read from memory now
			$this->assertEquals([$country1, $country2], $manager->all()->toArray());
		}

		public function testPutAll_fallbackLocale() {

			$arrayCache = $this->mockArrayCache();

			$manager = new CountriesManager(
				$arrayCache,
				Cache::store()
			);

			$country1 = new Country(
				'DE',
				'DEU',
				'Deutschland',
				'49'
			);

			$country2 = new Country(
				'US',
				'USA',
				'Vereinigte Staaten von Amerika',
				'1'
			);

			$this->assertSame($manager, $manager->put($country1, 'de'));
			$this->assertSame($manager, $manager->put($country2, 'de'));

			config()->set('app.fallback_locale', 'de');


			$this->assertEquals([$country1, $country2], $manager->all()->toArray());


			// clear cache and db is empty
			CountryMetaData::query()->delete();
			CountryLocalizedData::query()->delete();
			$arrayCache->purge();


			// cache is empty => should be read from memory now
			$this->assertEquals([$country1, $country2], $manager->all()->toArray());
		}

		public function testPutAllIso2Codes() {

			$arrayCache = $this->mockArrayCache();

			$manager = new CountriesManager(
				$arrayCache,
				Cache::store()
			);

			$country1 = new Country(
				'DE',
				'DEU',
				'Deutschland',
				'49'
			);

			$country2 = new Country(
				'US',
				'USA',
				'Vereinigte Staaten von Amerika',
				'1'
			);

			$this->assertSame($manager, $manager->put($country1, 'de'));
			$this->assertSame($manager, $manager->put($country2, 'de'));



			$this->assertEquals(['DE', 'US'], $manager->allIso2Codes()->toArray());


			// clear cache and db is empty
			CountryMetaData::query()->delete();
			CountryLocalizedData::query()->delete();
			$arrayCache->purge();


			// cache is empty => should be read from memory now

			$this->assertEquals(['DE', 'US'], $manager->allIso2Codes()->toArray());
		}
	}