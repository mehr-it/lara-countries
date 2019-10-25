<?php


	namespace MehrIt\LaraCountries\Util;


	use InvalidArgumentException;

	class PhpArrayCache
	{
		protected $directory;

		/**
		 * Creates a new instance
		 * @param string $directory The cache directory
		 */
		public function __construct(string $directory) {

			if (!$directory)
				throw new InvalidArgumentException('Directory is required');

			// remove trailing directory separator
			$directory = rtrim($directory, '/');

			$this->directory = $directory;
		}


		/**
		 * Puts data to the array cache
		 * @param string $key The key
		 * @param array $data The data
		 * @return PhpArrayCache
		 */
		public function put(string $key, array $data): PhpArrayCache {

			file_put_contents($this->getFilename($key), '<?php return ' . var_export($data, true) . ';');

			return $this;
		}

		/**
		 * Gets data from the array cache
		 * @param string $key The key
		 * @return array The data
		 */
		public function get(string $key): array {

			$data = null;

			// load php file
			$filename = $this->getFilename($key);
			if (file_exists($filename))
				$data = include $filename;

			if (!is_array($data))
				$data = [];

			return $data;
		}

		/**
		 * Purges all data in the cache
		 * @return PhpArrayCache
		 */
		public function purge(): PhpArrayCache {

			$files = glob("{$this->directory}/*.php");
			foreach ($files as $file) {
				if (is_file($file))
					unlink($file);
			}

			return $this;
		}


		/**
		 * Gets the hash for the given key
		 * @param string $key The key
		 * @return string The hash
		 */
		protected function getKeyHash(string $key): string {
			return sha1($key);
		}

		/**
		 * Gets the file name for the given key
		 * @param string $key The key
		 * @return string The file name
		 */
		protected function getFilename(string $key): string {

			$hash = $this->getKeyHash($key);

			return "{$this->directory}/{$hash}.php";
		}
	}