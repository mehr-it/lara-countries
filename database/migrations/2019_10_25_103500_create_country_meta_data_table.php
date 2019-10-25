<?php

	use Illuminate\Support\Facades\Schema;
	use Illuminate\Database\Schema\Blueprint;
	use Illuminate\Database\Migrations\Migration;

	class CreateCountryMetaDataTable extends Migration
	{
		/**
		 * Run the migrations.
		 *
		 * @return void
		 */
		public function up() {
			Schema::create('country_meta_data', function (Blueprint $table) {
				$table->string('iso2', 2);
				$table->string('iso3', 3);
				$table->string('dialing_code', 6);
				$table->primary('iso2');
				$table->timestamps();
			});
		}

		/**
		 * Reverse the migrations.
		 *
		 * @return void
		 */
		public function down() {
			Schema::dropIfExists('country_meta_data');
		}
	}