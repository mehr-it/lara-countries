<?php


	namespace MehrIt\LaraCountries\Model;


	use Illuminate\Database\Eloquent\Model;

	class LanguageMetaData extends Model
	{

		protected $table = 'language_meta_data';

		protected $primaryKey = 'iso2';

		protected $keyType = 'string';

		public $incrementing = false;
	}