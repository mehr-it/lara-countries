<?php

	use Illuminate\Support\Facades\Schema;
	use Illuminate\Database\Schema\Blueprint;
	use Illuminate\Database\Migrations\Migration;

	class CreateCountryLocalizedDataTable extends Migration
	{
		/**
		 * Run the migrations.
		 *
		 * @return void
		 */
		public function up() {
			Schema::create('country_localized_data', function (Blueprint $table) {
				$table->bigIncrements('id');
				$table->string('locale', 16);
				$table->string('iso2', 2);
				$table->json('data');
				$table->index(['locale', 'iso2']);
				$table->timestamps();
			});
		}

		/**
		 * Reverse the migrations.
		 *
		 * @return void
		 */
		public function down() {
			Schema::dropIfExists('country_localized_data');
		}
	}