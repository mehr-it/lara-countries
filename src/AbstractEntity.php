<?php


	namespace MehrIt\LaraCountries;


	use ArrayAccess;
	use Illuminate\Support\Str;

	abstract class AbstractEntity implements ArrayAccess
	{
		/**
		 * @inheritDoc
		 */
		public function offsetExists($offset) {
			return method_exists($this, 'get' . Str::ucfirst($offset));
		}

		/**
		 * @inheritDoc
		 */
		public function offsetGet($offset) {
			$methodName = 'get' . Str::ucfirst($offset);

			if (!method_exists($this, $methodName))
				return null;

			return $this->{$methodName}();
		}

		/**
		 * @inheritDoc
		 */
		public function offsetSet($offset, $value) {
			$methodName = 'set' . Str::ucfirst($offset);

			if ($value !== null && method_exists($this, $methodName))
				$this->{$methodName}((string)$value);
		}

		/**
		 * @inheritDoc
		 */
		public function offsetUnset($offset) {
			// do nothing here
		}
	}