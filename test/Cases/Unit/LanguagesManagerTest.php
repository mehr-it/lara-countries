<?php


	namespace MehrItLaraCountriesTest\Cases\Unit;


	use Carbon\Carbon;
	use Illuminate\Foundation\Testing\DatabaseMigrations;
	use Illuminate\Support\Facades\Cache;
	use MehrIt\LaraCountries\Language;
	use MehrIt\LaraCountries\LanguagesManager;
	use MehrIt\LaraCountries\Model\LanguageLocalizedData;
	use MehrIt\LaraCountries\Model\LanguageMetaData;

	class LanguagesManagerTest extends TestCase
	{
		use DatabaseMigrations;

		
		public function testPutGet() {

			$arrayCache = $this->makeLocalCache();

			$manager = new LanguagesManager(
				$arrayCache,
				Cache::store()
			);

			$lang1 = new Language(
				'de',
				'Deutsch'
			);
			
			$lang2 = new Language(
				'en',
				'Englisch'
			);
			

			$this->assertSame($manager, $manager->put($lang1, 'de'));
			$this->assertSame($manager, $manager->put($lang2, 'de'));

			$this->assertEquals($lang1, $manager->get('de', 'de'));
			$this->assertEquals($lang2, $manager->get('en', 'de'));
			$this->assertSame(null, $manager->get('fr', 'de'));


			// clear cache and db is empty
			LanguageMetaData::query()->delete();
			LanguageLocalizedData::query()->delete();
			$arrayCache->clear();

			// cache is empty => should be read from memory now
			$this->assertEquals($lang1, $manager->get('de', 'de'));
			$this->assertEquals($lang2, $manager->get('en', 'de'));
			$this->assertSame(null, $manager->get('fr', 'de'));
		}

		public function testPutGet_withDefaultLocale() {

			$arrayCache = $this->makeLocalCache();

			$manager = new LanguagesManager(
				$arrayCache,
				Cache::store()
			);

			$lang1 = new Language(
				'de',
				'Deutsch'
			);

			$lang2 = new Language(
				'en',
				'Englisch'
			);

			$this->assertSame($manager, $manager->put($lang1, app()->getLocale()));
			$this->assertSame($manager, $manager->put($lang2, app()->getLocale()));

			$this->assertEquals($lang1, $manager->get('de'));
			$this->assertEquals($lang2, $manager->get('en'));
			$this->assertSame(null, $manager->get('fr'));


			// clear cache and db is empty
			LanguageMetaData::query()->delete();
			LanguageLocalizedData::query()->delete();
			$arrayCache->clear();

			// cache is empty => should be read from memory now
			$this->assertEquals($lang1, $manager->get('de'));
			$this->assertEquals($lang2, $manager->get('en'));
			$this->assertSame(null, $manager->get('fr'));
		}

		public function testPutGet_modifiedByOtherInstance() {

			$arrayCache = $this->makeLocalCache();

			Carbon::setTestNow(Carbon::createFromTimestamp(time()));

			$manager = new LanguagesManager(
				$arrayCache,
				Cache::store()
			);

			$lang1 = new Language(
				'de',
				'Deutsch'
			);

			$lang2 = new Language(
				'en',
				'Englisch'
			);

			$this->assertSame($manager, $manager->put($lang1, 'de'));
			$this->assertSame($manager, $manager->put($lang2, 'de'));

			// load to cache in first instance
			$manager->get('de', 'de');
			$manager->get('en', 'de');
			$manager->get('fr', 'de');

			// shift time
			Carbon::setTestNow(Carbon::createFromTimestamp(time() + 10));

			$manager2 = new LanguagesManager(
				$arrayCache,
				Cache::store()
			);

			$lang1 = new Language(
				'de',
				'DDD'
			);

			$lang2 = new Language(
				'en',
				'EEE'
			);
			$manager2->put($lang1, 'de');
			$manager2->put($lang2, 'de');


			$this->assertEquals($lang1, $manager->get('de', 'de'));
			$this->assertEquals($lang2, $manager->get('en', 'de'));
			$this->assertSame(null, $manager->get('fr', 'de'));
		}

		public function testPutGet_cacheInvalidatedByOtherInstance() {

			$arrayCache = $this->makeLocalCache();

			Carbon::setTestNow(Carbon::createFromTimestamp(time()));

			$manager = new LanguagesManager(
				$arrayCache,
				Cache::store()
			);

			$lang1 = new Language(
				'de',
				'Deutsch'
			);

			$lang2 = new Language(
				'en',
				'Englisch'
			);

			$this->assertSame($manager, $manager->put($lang1, 'de'));
			$this->assertSame($manager, $manager->put($lang2, 'de'));

			// load to cache in first instance
			$manager->get('DE', 'de');
			$manager->get('US', 'de');
			$manager->get('ES', 'de');

			// shift time
			Carbon::setTestNow(Carbon::createFromTimestamp(time() + 10));


			// modify db
			LanguageLocalizedData::query()
				->update(['data' => json_encode(['name' => 'modified'])]);

			// invalidate cache
			$manager2 = new LanguagesManager(
				$arrayCache,
				Cache::store()
			);

			$manager2->invalidateCache();


			// cache is invalid => should be read from DB
			$this->assertEquals('modified', $manager->get('de', 'de')->getName());
			$this->assertEquals('modified', $manager->get('en', 'de')->getName());
			$this->assertSame(null, $manager->get('fr', 'de'));
		}

		public function testPutGet_differentLocale_notSet() {

			$arrayCache = $this->makeLocalCache();

			$manager = new LanguagesManager(
				$arrayCache,
				Cache::store()
			);

			$lang1 = new Language(
				'de',
				'Deutsch'
			);

			$lang2 = new Language(
				'en',
				'Englisch'
			);

			$this->assertSame($manager, $manager->put($lang1, 'de'));
			$this->assertSame($manager, $manager->put($lang2, 'de'));

			$expLang1 = clone $lang1;
			$expLang1->setName('');

			$expLang2 = clone $lang2;
			$expLang2->setName('');

			$this->assertEquals($expLang1, $manager->get('de', 'en'));
			$this->assertEquals($expLang2, $manager->get('en', 'en'));
			$this->assertSame(null, $manager->get('fr', 'en'));

			$this->assertEquals($lang1, $manager->get('de', 'de'));
			$this->assertEquals($lang2, $manager->get('en', 'de'));
			$this->assertSame(null, $manager->get('fr', 'de'));

			// clear cache and db is empty
			LanguageMetaData::query()->delete();
			LanguageLocalizedData::query()->delete();
			$arrayCache->clear();


			// cache is empty => should be read from memory now
			$this->assertEquals($expLang1, $manager->get('de', 'en'));
			$this->assertEquals($expLang2, $manager->get('en', 'en'));
			$this->assertSame(null, $manager->get('fr', 'en'));

			$this->assertEquals($lang1, $manager->get('de', 'de'));
			$this->assertEquals($lang2, $manager->get('en', 'de'));
			$this->assertSame(null, $manager->get('fr', 'de'));
		}

		public function testPutGet_differentLocale_set() {

			$arrayCache = $this->makeLocalCache();

			$manager = new LanguagesManager(
				$arrayCache,
				Cache::store()
			);

			$lang1 = new Language(
				'de',
				'Deutsch'
			);

			$lang2 = new Language(
				'en',
				'Englisch'
			);

			$this->assertSame($manager, $manager->put($lang1, 'de'));
			$this->assertSame($manager, $manager->put($lang2, 'de'));
			$this->assertSame($manager, $manager->putLocalizedData('de', 'en', ['name' => 'German']));
			$this->assertSame($manager, $manager->putLocalizedData('en', 'en', ['name' => 'English']));


			$expLang1 = clone $lang1;
			$expLang1->setName('German');

			$expLang2 = clone $lang2;
			$expLang2->setName('English');

			$this->assertEquals($expLang1, $manager->get('de', 'en'));
			$this->assertEquals($expLang2, $manager->get('en', 'en'));
			$this->assertSame(null, $manager->get('fr', 'en'));

			$this->assertEquals($lang1, $manager->get('de', 'de'));
			$this->assertEquals($lang2, $manager->get('en', 'de'));
			$this->assertSame(null, $manager->get('fr', 'de'));


			// clear cache and db is empty
			LanguageMetaData::query()->delete();
			LanguageLocalizedData::query()->delete();
			$arrayCache->clear();


			// cache is empty => should be read from memory now
			$this->assertEquals($expLang1, $manager->get('de', 'en'));
			$this->assertEquals($expLang2, $manager->get('en', 'en'));
			$this->assertSame(null, $manager->get('fr', 'en'));

			$this->assertEquals($lang1, $manager->get('de', 'de'));
			$this->assertEquals($lang2, $manager->get('en', 'de'));
			$this->assertSame(null, $manager->get('fr', 'de'));
		}

		public function testPutGet_fallbackLocale() {

			$arrayCache = $this->makeLocalCache();

			$manager = new LanguagesManager(
				$arrayCache,
				Cache::store()
			);

			$lang1 = new Language(
				'de',
				'Deutsch'
			);

			$lang2 = new Language(
				'en',
				'Englisch'
			);

			config()->set('app.fallback_locale', 'de');

			$this->assertSame($manager, $manager->put($lang1, 'de'));
			$this->assertSame($manager, $manager->put($lang2, 'de'));


			$expLang1 = clone $lang1;
			$expLang1->setName('German');

			$expLang2 = clone $lang2;
			$expLang2->setName('English');

			$this->assertEquals($lang1, $manager->get('de'));
			$this->assertEquals($lang2, $manager->get('en'));
			$this->assertSame(null, $manager->get('fr'));


			// clear cache and db is empty
			LanguageMetaData::query()->delete();
			LanguageLocalizedData::query()->delete();
			$arrayCache->clear();


			// cache is empty => should be read from memory now
			$this->assertEquals($lang1, $manager->get('de'));
			$this->assertEquals($lang2, $manager->get('en'));
			$this->assertSame(null, $manager->get('fr'));
		}


		public function testPutAll_withDifferentLocales() {

			$arrayCache = $this->makeLocalCache();

			$manager = new LanguagesManager(
				$arrayCache,
				Cache::store()
			);

			$lang1 = new Language(
				'de',
				'Deutsch'
			);

			$lang2 = new Language(
				'en',
				'Englisch'
			);

			$this->assertSame($manager, $manager->put($lang1, 'de'));
			$this->assertSame($manager, $manager->put($lang2, 'de'));
			$this->assertSame($manager, $manager->putLocalizedData('de', 'en', ['name' => 'German']));
			$this->assertSame($manager, $manager->putLocalizedData('en', 'en', ['name' => 'English']));


			$expLang1 = clone $lang1;
			$expLang1->setName('German');

			$expLang2 = clone $lang2;
			$expLang2->setName('English');

			$this->assertEquals([$expLang1, $expLang2], $manager->all('en')->toArray());
			$this->assertEquals([$lang1, $lang2], $manager->all('de')->toArray());


			// clear cache and db is empty
			LanguageMetaData::query()->delete();
			LanguageLocalizedData::query()->delete();
			$arrayCache->clear();


			// cache is empty => should be read from memory now
			$this->assertEquals([$expLang1, $expLang2], $manager->all('en')->toArray());
			$this->assertEquals([$lang1, $lang2], $manager->all('de')->toArray());
		}

		public function testPutAll_withDefaultLocale() {

			$arrayCache = $this->makeLocalCache();

			$manager = new LanguagesManager(
				$arrayCache,
				Cache::store()
			);

			$lang1 = new Language(
				'de',
				'Deutsch'
			);

			$lang2 = new Language(
				'en',
				'Englisch'
			);

			$this->assertSame($manager, $manager->put($lang1, app()->getLocale()));
			$this->assertSame($manager, $manager->put($lang2, app()->getLocale()));


			$this->assertEquals([$lang1, $lang2], $manager->all()->toArray());


			// clear cache and db is empty
			LanguageMetaData::query()->delete();
			LanguageLocalizedData::query()->delete();
			$arrayCache->clear();


			// cache is empty => should be read from memory now
			$this->assertEquals([$lang1, $lang2], $manager->all()->toArray());
		}

		public function testPutAll_fallbackLocale() {

			$arrayCache = $this->makeLocalCache();

			$manager = new LanguagesManager(
				$arrayCache,
				Cache::store()
			);

			$lang1 = new Language(
				'de',
				'Deutsch'
			);

			$lang2 = new Language(
				'en',
				'Englisch'
			);

			$this->assertSame($manager, $manager->put($lang1, 'de'));
			$this->assertSame($manager, $manager->put($lang2, 'de'));

			config()->set('app.fallback_locale', 'de');


			$this->assertEquals([$lang1, $lang2], $manager->all()->toArray());


			// clear cache and db is empty
			LanguageMetaData::query()->delete();
			LanguageLocalizedData::query()->delete();
			$arrayCache->clear();


			// cache is empty => should be read from memory now
			$this->assertEquals([$lang1, $lang2], $manager->all()->toArray());
		}

		public function testPutAllIso2Codes() {

			$arrayCache = $this->makeLocalCache();

			$manager = new LanguagesManager(
				$arrayCache,
				Cache::store()
			);

			$lang1 = new Language(
				'de',
				'Deutsch'
			);

			$lang2 = new Language(
				'en',
				'Englisch'
			);

			$this->assertSame($manager, $manager->put($lang1, 'de'));
			$this->assertSame($manager, $manager->put($lang2, 'de'));


			$this->assertEquals(['de', 'en'], $manager->allIso2Codes()->toArray());


			// clear cache and db is empty
			LanguageMetaData::query()->delete();
			LanguageLocalizedData::query()->delete();
			$arrayCache->clear();


			// cache is empty => should be read from memory now

			$this->assertEquals(['de', 'en'], $manager->allIso2Codes()->toArray());
		}

	}