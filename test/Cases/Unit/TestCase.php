<?php


	namespace MehrItLaraCountriesTest\Cases\Unit;


	use Carbon\Carbon;
	use MehrIt\LaraCountries\Provider\CountriesServiceProvider;

	class TestCase extends \Orchestra\Testbench\TestCase
	{
		protected function setUp(): void {
			parent::setUp();

			Carbon::setTestNow();
		}


		/**
		 * @inheritDoc
		 */
		protected function getPackageProviders($app) {
			return [
				CountriesServiceProvider::class,
			];
		}

		/**
		 * Mocks an instance in the application service container
		 * @param string $instance The instance to mock
		 * @param string|null $mockedClass The class to use for creating a mock object. Null to use same as $instance
		 * @return \PHPUnit\Framework\MockObject\MockObject
		 */
		protected function mockAppSingleton($instance, $mockedClass = null) {

			if (!$mockedClass)
				$mockedClass = $instance;

			$mock = $this->getMockBuilder($mockedClass)->disableOriginalConstructor()->getMock();
			app()->singleton($instance, function () use ($mock) {
				return $mock;
			});

			return $mock;
		}

	}