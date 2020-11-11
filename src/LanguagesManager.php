<?php


	namespace MehrIt\LaraCountries;


	use Carbon\Carbon;
	use Illuminate\Contracts\Cache\Repository;
	use Illuminate\Support\Collection;
	use InvalidArgumentException;
	use MehrIt\LaraCountries\Contracts\LanguageContract;
	use MehrIt\LaraCountries\Model\LanguageLocalizedData;
	use MehrIt\LaraCountries\Model\LanguageMetaData;
	use MehrIt\LaraCountries\Util\PhpArrayCache;
	use Psr\SimpleCache\InvalidArgumentException as CacheArgumentException;
	use Throwable;

	class LanguagesManager
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

		protected $cacheLanguageDataTs = false;

		protected $arrayCacheTtl = 5;

		/**
		 * @var PhpArrayCache
		 */
		protected $arrayCache;


		/**
		 * LanguagesManager constructor.
		 * @param PhpArrayCache $arrayCache The array cache to use
		 * @param Repository $cache The cache to use
		 * @param string|null $cacheKeyPrefix The cache key prefix
		 * @param int $arrayCacheTtl The array cache time to live in seconds
		 */
		public function __construct(PhpArrayCache $arrayCache, Repository $cache, ?string $cacheKeyPrefix = null, int $arrayCacheTtl = 5) {
			$this->arrayCache     = $arrayCache;
			$this->cache          = $cache;
			$this->cacheKeyPrefix = $cacheKeyPrefix;
			$this->arrayCacheTtl  = $arrayCacheTtl;
		}


		/**
		 * Returns if the given language exists
		 * @param string $iso2Code The ISO2 code
		 * @return bool True if existing. Else false.
		 */
		public function exists(string $iso2Code): bool {

			$metaData = $this->getData(self::DATA_TYPE_META);

			return array_key_exists($iso2Code, $metaData);
		}

		/**
		 * Gets the language data for given ISO2 code
		 * @param string $iso2Code The code
		 * @param string|null $locale The locale. If omitted, app locale will be used
		 * @return LanguageContract|null The language data or null
		 */
		public function get(string $iso2Code, string $locale = null): ?LanguageContract {

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


			return new Language(
				$metaData['iso2'],
				$localizedData['name'] ?? ''
			);
		}

		/**
		 * Gets all languages
		 * @param string $locale The locale
		 * @return LanguageContract[]|\Illuminate\Support\Collection The languages
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
				$ret[] = new Language(
					$iso2,
					($localized[$iso2]['name'] ?? '') ?: ($fallback[$iso2]['name'] ?? '')
				);
			}

			return collect($ret);
		}

		/**
		 * Gets all iso2 language codes
		 * @return Collection
		 */
		public function allIso2Codes(): Collection {
			return collect(array_keys($this->getData(self::DATA_TYPE_META)));
		}

		/**
		 * Persists the given language information
		 * @param LanguageContract $language The language
		 * @param string $locale The locale the data was given in
		 * @return LanguagesManager
		 * @throws InvalidArgumentException
		 * @throws Throwable
		 */
		public function put(LanguageContract $language, string $locale): LanguagesManager {

			(new LanguageMetaData())->getConnection()->transaction(function () use ($language, $locale) {

				$metaModel = (new LanguageMetaData())->query()
					->where('iso2', '=', $language->getIso2Code())
					->lockForUpdate()
					->first();

				if (!$metaModel)
					$metaModel = new LanguageMetaData();

				$metaModel->iso2 = $language->getIso2Code();
				$metaModel->save();

				$this->updateLocalizedData($language->getIso2Code(), $locale, [
					'name' => $language->getName(),
				]);

				// invalidate cache
				$this->invalidateCache();

			});

			return $this;
		}

		/**
		 * Persists the given language name
		 * @param string $iso2Code The ISO2 code
		 * @param array $data The data as associative array
		 * @param string $locale The locale
		 * @return LanguagesManager
		 * @throws Throwable
		 */
		public function putLocalizedData(string $iso2Code, string $locale, array $data): LanguagesManager {

			(new LanguageMetaData())->getConnection()->transaction(function () use ($iso2Code, $data, $locale) {

				$this->updateLocalizedData($iso2Code, $locale, $data);

				$this->invalidateCache();
			});

			return $this;
		}

		/**
		 * Invalidates the cache
		 * @return LanguagesManager The cache
		 */
		public function invalidateCache(): LanguagesManager {

			$this->cacheLanguageDataTs = false;
			$this->cache->forever($this->getDataTsKey(), Carbon::now()->getTimestamp());

			$this->arrayCache->purge();

			return $this;
		}

		/**
		 * Gets the cache key for the data timestamp
		 * @return string The cache key
		 */
		protected function getDataTsKey(): string {
			return "{$this->cacheKeyPrefix}_language_data_ts";
		}

		/**
		 * Checks if the cache with given timestamp is expired
		 * @param int $cacheTs The cache timestamp
		 * @return bool True if expired. Else false.
		 */
		protected function isCacheExpired($cacheTs) {

			if (!$cacheTs)
				return true;

			if (!$this->cacheLanguageDataTs || $this->cacheLanguageDataTs < Carbon::now()->getTimestamp() - $this->arrayCacheTtl) {
				try {
					$this->cacheLanguageDataTs = $this->cache->get($this->getDataTsKey(), false);
				}
				catch (CacheArgumentException $ex) {
					$this->cacheLanguageDataTs = false;
				}
			}

			if (!$this->cacheLanguageDataTs)
				return true;

			return $this->cacheLanguageDataTs > $cacheTs;
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
				$data = $this->data[$key] = $this->arrayCache->get($key);

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
					$data = LanguageMetaData::query()
						->get()
						->keyBy('iso2')
						->toArray();
					break;

				case self::DATA_TYPE_LOCALIZED:
					$data = LanguageLocalizedData::query()
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

			$this->arrayCache->put($this->getCacheKey($type, $locale), $cacheData);

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
					return 'lang_meta';

				case self::DATA_TYPE_LOCALIZED:
					return "lang_localized_{$locale}";
					break;

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
			$record = LanguageLocalizedData::query()
				->lockForUpdate()
				->where('locale', '=', $locale)
				->where('iso2', '=', $iso2Code)
				->first();

			if ($record) {
				$record->data = $data;
			}
			else {
				$record         = new LanguageLocalizedData();
				$record->iso2   = $iso2Code;
				$record->locale = $locale;
				$record->data   = $data;
			}

			$record->save();
		}
	}