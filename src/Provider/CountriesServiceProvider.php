<?php


	namespace MehrIt\LaraCountries\Provider;

	use Illuminate\Contracts\Support\DeferrableProvider;
	use Illuminate\Support\Facades\Cache;
	use Illuminate\Support\ServiceProvider;
	use MehrIt\LaraCountries\Command\ImportCountriesCommand;
	use MehrIt\LaraCountries\Command\ImportLocalizedCountryDataCommand;
	use MehrIt\LaraCountries\CountriesManager;
	use MehrIt\LaraCountries\Util\PhpArrayCache;

	class CountriesServiceProvider extends ServiceProvider implements DeferrableProvider
	{

		public function boot() {
			// migrations
			$this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');

			// register commands
			$this->commands([
				ImportCountriesCommand::class,
				ImportLocalizedCountryDataCommand::class,
			]);
		}

		/**
		 * Register the service provider.
		 *
		 * @return void
		 */
		public function register() {

			$this->mergeConfigFrom(__DIR__ . '/../../config/countries.php', 'countries');

			$this->app->singleton(CountriesManager::class, function() {


				$arrayCacheDir = storage_path(config('countries.array_cache_dir', 'countriesCache'));
				if (!file_exists($arrayCacheDir))
					mkdir($arrayCacheDir);

				$cacheName = config('countries.cache');

				return new CountriesManager(
					new PhpArrayCache($arrayCacheDir),
					Cache::store($cacheName),
					config('countries.cache_key_prefix'),
					config('countries.array_cache_ttl', 5)
				);
			});
		}

		/**
		 * @inheritDoc
		 */
		public function provides() {
			return [
				CountriesManager::class,
			];
		}
	}