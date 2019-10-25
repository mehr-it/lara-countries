<?php


	namespace MehrIt\LaraCountries\Model;


	use Illuminate\Database\Eloquent\Model;

	class CountryMetaData extends Model
	{

		protected $table = 'country_meta_data';

		protected $primaryKey = 'iso2';

		protected $keyType = 'string';

		public $incrementing = false;
	}