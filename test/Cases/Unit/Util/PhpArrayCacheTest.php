<?php


	namespace MehrItLaraCountriesTest\Cases\Unit\Util;


	use MehrIt\LaraCountries\Util\PhpArrayCache;
	use MehrItLaraCountriesTest\Cases\Unit\TestCase;

	class PhpArrayCacheTest extends TestCase
	{
		protected $tmpDir;

		protected function cleanDir($directory) {
			$files = glob("{$directory}/*");
			foreach ($files as $file) {
				if (is_file($file))
					unlink($file);
			}
		}

		protected function setUp(): void {
			parent::setUp();

			$this->tmpDir = sys_get_temp_dir() . '/PhpArrayCacheTest';
			if (file_exists($this->tmpDir))
				$this->cleanDir($this->tmpDir);
			else
				mkdir($this->tmpDir);
		}

		protected function tearDown(): void {

			if (file_exists($this->tmpDir))
				$this->cleanDir($this->tmpDir);

			parent::tearDown();
		}


		public function testPutGet() {

			$cache = new PhpArrayCache($this->tmpDir);

			$data1 = ['a' => 56, 'b' => ['78', 9]];
			$data2 = ['a' => 66, 'b' => null];

			$this->assertSame($cache, $cache->put('key1', $data1));
			$this->assertSame($cache, $cache->put('key2', $data2));

			$this->assertSame($data1, $cache->get('key1'));
			$this->assertSame($data2, $cache->get('key2'));
			$this->assertSame([], $cache->get('key3'));

		}

		public function testPutGetPurge() {

			$cache = new PhpArrayCache($this->tmpDir);

			$data1 = ['a' => 56, 'b' => ['78', 9]];
			$data2 = ['a' => 66, 'b' => null];

			$this->assertSame($cache, $cache->put('key1', $data1));
			$this->assertSame($cache, $cache->put('key2', $data2));

			$this->assertSame($cache, $cache->purge());

			$this->assertSame([], $cache->get('key1'));
			$this->assertSame([], $cache->get('key2'));
			$this->assertSame([], $cache->get('key3'));

		}

	}