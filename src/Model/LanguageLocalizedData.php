<?php


	namespace MehrIt\LaraCountries\Model;


	use Illuminate\Database\Eloquent\Model;

	class LanguageLocalizedData extends Model
	{
		protected $table = 'language_localized_data';


		protected $casts = [
			'data' => 'array'
		];
	}