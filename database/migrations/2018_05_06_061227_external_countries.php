<?php

use App\Address;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ExternalCountries extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('addresses', function (Blueprint $table) {
            $table->char('country', 3)->after('country_id')->nullable();
        });

        Address::chunk(200, function ($addresses) {
            foreach ($addresses as $addresse) {
                $iso = DB::table('countries')->where('id', $addresse->country_id)->value('iso');
                $addresse->update(['country' => mb_strtoupper(CountriesSeederTable::fixIso($iso))]);
            }
        });

        Schema::table('addresses', function (Blueprint $table) {
            $table->dropColumn('country_id');
        });
        Schema::dropIfExists('countries');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('addresses', function (Blueprint $table) {
            $table->integer('country_id')->after('country')->nullable();
        });

        Schema::create('countries', function (Blueprint $table) {
            $table->increments('id');
            $table->string('iso');
            $table->string('country');
        });
        (new CountriesSeederTable)->run();

        Address::chunk(200, function ($addresses) {
            foreach ($addresses as $addresse) {
                $id = DB::table('countries')->where('iso', mb_strtolower($addresse->country))->value('id');
                $addresse->update(['country_id' => $id]);
            }
        });

        Schema::table('addresses', function (Blueprint $table) {
            $table->dropColumn('country');
        });
    }
}
