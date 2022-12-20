<?php


	namespace MehrIt\LaraCountries\Provider;

	use Illuminate\Contracts\Support\DeferrableProvider;
	use Illuminate\Support\Facades\Cache;
	use Illuminate\Support\ServiceProvider;
	use MehrIt\LaraCountries\Command\ImportCountriesCommand;
	use MehrIt\LaraCountries\Command\ImportLanguagesCommand;
	use MehrIt\LaraCountries\Command\ImportLocalizedCountryDataCommand;
	use MehrIt\LaraCountries\Command\ImportLocalizedLanguageDataCommand;
	use MehrIt\LaraCountries\CountriesManager;
	use MehrIt\LaraCountries\LanguagesManager;
	use MehrIt\PhpCache\PhpCache;

	class CountriesServiceProvider extends ServiceProvider implements DeferrableProvider
	{

		public function boot() {
			// migrations
			$this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');

			// register commands
			$this->commands([
				ImportCountriesCommand::class,
				ImportLanguagesCommand::class,
				ImportLocalizedCountryDataCommand::class,
				ImportLocalizedLanguageDataCommand::class,
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

				return new CountriesManager(
					new PhpCache($this->arrayCacheDirectory()),
					Cache::store(config('countries.cache')),
					config('countries.cache_key_prefix'),
					config('countries.array_cache_ttl', 5)
				);
			});

			$this->app->singleton(LanguagesManager::class, function() {

				return new LanguagesManager(
					new PhpCache($this->arrayCacheDirectory()),
					Cache::store(config('countries.cache')),
					config('countries.cache_key_prefix'),
					config('countries.array_cache_ttl', 5)
				);
			});
		}

		/**
		 * Creates and returns the array cache directory
		 * @return string
		 */
		protected function arrayCacheDirectory(): string {
			$arrayCacheDir = storage_path(config('countries.array_cache_dir', 'countriesCache'));
			if (!file_exists($arrayCacheDir))
				mkdir($arrayCacheDir);

			return $arrayCacheDir;
		}

		/**
		 * @inheritDoc
		 */
		public function provides() {
			return [
				CountriesManager::class,
				LanguagesManager::class,
			];
		}
	}