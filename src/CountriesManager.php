<?php


	namespace MehrIt\LaraCountries;


	use Carbon\Carbon;
	use Illuminate\Contracts\Cache\Repository;
	use Illuminate\Support\Collection;
	use InvalidArgumentException;
	use MehrIt\LaraCountries\Contracts\CountryContract;
	use MehrIt\LaraCountries\Model\CountryMetaData;
	use MehrIt\LaraCountries\Model\CountryLocalizedData;
	use MehrIt\PhpCache\PhpCache;
	use Psr\SimpleCache\InvalidArgumentException as CacheArgumentException;
	use Throwable;

	class CountriesManager
	{

		const DATA_TYPE_META = 'meta';
		const DATA_TYPE_LOCALIZED = 'localized';

		/**
		 * @var Repository
		 */
		protected $cache;

		/**
		 * @var string|null
		 */
		protected $cacheKeyPrefix;


		protected $data = [];

		protected $cacheCountryDataTs = false;

		protected $arrayCacheTtl = 5;

		/**
		 * @var PhpCache
		 */
		protected $localCache;


		/**
		 * CountriesManager constructor.
		 * @param PhpCache $localCache The php cache to use
		 * @param Repository $cache The cache to use
		 * @param string|null $cacheKeyPrefix The cache key prefix
		 * @param int $arrayCacheTtl The array cache time to live in seconds
		 */
		public function __construct(PhpCache $localCache, Repository $cache, ?string $cacheKeyPrefix = null, int $arrayCacheTtl = 5) {
			$this->localCache     = $localCache;
			$this->cache          = $cache;
			$this->cacheKeyPrefix = $cacheKeyPrefix;
			$this->arrayCacheTtl  = $arrayCacheTtl;
		}


		/**
		 * Returns if the given country exists
		 * @param string $iso2Code The ISO2 code
		 * @return bool True if existing. Else false.
		 */
		public function exists(string $iso2Code): bool {

			$metaData = $this->getData(self::DATA_TYPE_META);

			return array_key_exists($iso2Code, $metaData);
		}

		/**
		 * Gets the country data for given ISO2 code
		 * @param string $iso2Code The code
		 * @param string|null $locale The locale. If omitted, app locale will be used
		 * @return CountryContract|null The country data or null
		 */
		public function get(string $iso2Code, string $locale = null): ?CountryContract {

			if ($locale === null)
				$locale = app()->getLocale();

			// fetch meta data
			$meta     = $this->getData(self::DATA_TYPE_META);
			$metaData = $meta[$iso2Code] ?? false;
			if (!$metaData)
				return null;


			// fetch localized data (with fallback locale)
			$localized     = $this->getData(self::DATA_TYPE_LOCALIZED, $locale);
			$localizedData = $localized[$iso2Code] ?? false;
			if (!$localizedData) {
				$localized     = $this->getData(self::DATA_TYPE_LOCALIZED, config('app.fallback_locale'));
				$localizedData = $localized[$iso2Code] ?? [];
			}


			return new Country(
				$metaData['iso2'],
				$metaData['iso3'],
				$localizedData['name'] ?? '',
				$metaData['dialing_code']
			);
		}

		/**
		 * Gets all countries
		 * @param string $locale The locale
		 * @return CountryContract[]|\Illuminate\Support\Collection The countries
		 */
		public function all(string $locale = null): Collection {

			if ($locale === null)
				$locale = app()->getLocale();

			// fetch meta data
			$meta = $this->getData(self::DATA_TYPE_META);

			// fetch localized data (with fallback locale)
			$localized = $this->getData(self::DATA_TYPE_LOCALIZED, $locale);

			// fetch localized data (with fallback locale)
			$fallback = $this->getData(self::DATA_TYPE_LOCALIZED, config('app.fallback_locale'));


			$ret = [];
			foreach ($meta as $iso2 => $curr) {
				$ret[] = new Country(
					$iso2,
					$curr['iso3'],
					($localized[$iso2]['name'] ?? '') ?: ($fallback[$iso2]['name'] ?? ''),
					$curr['dialing_code']
				);
			}

			return collect($ret);
		}

		/**
		 * Gets all iso2 country codes
		 * @return Collection
		 */
		public function allIso2Codes(): Collection {
			return collect(array_keys($this->getData(self::DATA_TYPE_META)));
		}

		/**
		 * Persists the given country information
		 * @param CountryContract $country The country
		 * @param string $locale The locale the data was given in
		 * @return CountriesManager
		 * @throws InvalidArgumentException
		 * @throws Throwable
		 */
		public function put(CountryContract $country, string $locale): CountriesManager {

			(new CountryMetaData())->getConnection()->transaction(function () use ($country, $locale) {

				$metaModel = (new CountryMetaData())->query()
					->where('iso2', '=', $country->getIso2Code())
					->lockForUpdate()
					->first();

				if (!$metaModel)
					$metaModel = new CountryMetaData();

				$metaModel->iso2         = $country->getIso2Code();
				$metaModel->iso3         = $country->getIso3Code();
				$metaModel->dialing_code = $country->getDialingCode();
				$metaModel->save();

				$this->updateLocalizedData($country->getIso2Code(), $locale, [
					'name' => $country->getName(),
				]);

				// invalidate cache
				$this->invalidateCache();

			});

			return $this;
		}

		/**
		 * Persists the given country name
		 * @param string $iso2Code The ISO2 code
		 * @param array $data The data as associative array
		 * @param string $locale The locale
		 * @return CountriesManager
		 * @throws Throwable
		 */
		public function putLocalizedData(string $iso2Code, string $locale, array $data): CountriesManager {

			(new CountryLocalizedData())->getConnection()->transaction(function () use ($iso2Code, $data, $locale) {

				$this->updateLocalizedData($iso2Code, $locale, $data);

				$this->invalidateCache();
			});

			return $this;
		}

		/**
		 * Invalidates the cache
		 * @return CountriesManager The cache
		 */
		public function invalidateCache(): CountriesManager {

			$this->cacheCountryDataTs = false;
			$this->cache->forever($this->getDataTsKey(), Carbon::now()->getTimestamp());

			$this->localCache->clear();

			return $this;
		}

		/**
		 * Gets the cache key for the data timestamp
		 * @return string The cache key
		 */
		protected function getDataTsKey(): string {
			return "{$this->cacheKeyPrefix}_country_data_ts";
		}

		/**
		 * Checks if the cache with given timestamp is expired
		 * @param int $cacheTs The cache timestamp
		 * @return bool True if expired. Else false.
		 */
		protected function isCacheExpired($cacheTs) {

			if (!$cacheTs)
				return true;

			if (!$this->cacheCountryDataTs || $this->cacheCountryDataTs < Carbon::now()->getTimestamp() - $this->arrayCacheTtl) {
				try {
					$this->cacheCountryDataTs = $this->cache->get($this->getDataTsKey(), false);
				}
				catch (CacheArgumentException $ex) {
					$this->cacheCountryDataTs = false;
				}
			}

			if (!$this->cacheCountryDataTs)
				return true;

			return $this->cacheCountryDataTs > $cacheTs;
		}


		/**
		 * Gets the data array with the given key
		 * @param string $type The data type
		 * @param string|null $locale The locale
		 * @return array The data
		 */
		protected function getData(string $type, string $locale = null): array {

			$key = $this->getCacheKey($type, $locale);

			$data = $this->data[$key] ?? false;

			// load from array cache if not in memory
			if ($data === false)
				$data = $this->data[$key] = $this->localCache->get($key);

			// reload data from DB if expired
			if ($this->isCacheExpired($data['ts'] ?? 0))
				$data = $this->data[$key] = $this->rebuildCache($type, $locale);


			$ret = $data['data'] ?? [];
			if (!is_array($ret))
				$ret = [];

			return $ret;
		}

		/**
		 * Rebuilds the cache for the given data
		 * @param string $type The data type
		 * @param string|null $locale The locale
		 * @return array The data which was written to cache
		 */
		protected function rebuildCache(string $type, string $locale = null): array {

			switch ($type) {
				case self::DATA_TYPE_META:
					$data = CountryMetaData::query()
						->get()
						->keyBy('iso2')
						->toArray();
					break;

				case self::DATA_TYPE_LOCALIZED:
					$data = CountryLocalizedData::query()
						->where('locale', '=', $locale)
						->pluck('data', 'iso2')
						->toArray();
					break;

				default:
					throw new InvalidArgumentException('Unexpected data type');
			}

			$cacheData = [
				'ts'   => Carbon::now()->getTimestamp(),
				'data' => $data,
			];

			$this->localCache->set($this->getCacheKey($type, $locale), $cacheData);

			// if yet not set, set the data timestamp in cache (if we would not do this, 
			// cached data will always be treated as expired until any of the put-methods is
			// invoked)
			if (!$this->cache->get($this->getDataTsKey()))
				$this->cache->set($this->getDataTsKey(), Carbon::now()->getTimestamp());

			return $cacheData;
		}

		/**
		 * Gets the cache key for the given data
		 * @param string $type The data type
		 * @param string|null $locale The locale
		 * @return string The cache key
		 */
		protected function getCacheKey(string $type, ?string $locale) {
			switch ($type) {
				case self::DATA_TYPE_META:
					return 'meta';

				case self::DATA_TYPE_LOCALIZED:
					return "localized_{$locale}";

				default:
					throw new InvalidArgumentException('Unexpected data type');
			}

		}

		/**
		 * Updates the localized data
		 * @param string $iso2Code The ISO2 code
		 * @param string $locale The locale
		 * @param array $data The data
		 */
		protected function updateLocalizedData(string $iso2Code, string $locale, array $data) {
			$record = CountryLocalizedData::query()
				->lockForUpdate()
				->where('locale', '=', $locale)
				->where('iso2', '=', $iso2Code)
				->first();

			if ($record) {
				$record->data = $data;
			}
			else {
				$record         = new CountryLocalizedData();
				$record->iso2   = $iso2Code;
				$record->locale = $locale;
				$record->data   = $data;
			}

			$record->save();
		}
	}