<?php


	namespace MehrIt\LaraCountries\Model;


	use Illuminate\Database\Eloquent\Model;

	class CountryLocalizedData extends Model
	{
		protected $table = 'country_localized_data';



		protected $casts = [
			'data' => 'array'
		];
	}