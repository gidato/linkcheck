<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pages', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('scan_id');
            $table->string('url');
            $table->string('method')->length(10)->nullable();
            $table->integer('depth');
            $table->boolean('is_external');
            $table->boolean('checked')->default(0);
            $table->string('mime_type')->nullable();
            $table->integer('status_code')->nullable();
            $table->text('redirect')->nullable();
            $table->text('html_errors')->nullable();
            $table->timestamps();

            $table->foreign('scan_id')->references('id')->on('scans')->onDelete('cascade');;
        });

        Schema::create('page_references', function(Blueprint $table) {
            $table->increments('id');
            $table->unsignedBigInteger('referrer_id');
            $table->unsignedBigInteger('referred_id');
            $table->string('type');
            $table->string('target')->nullable();
            $table->string('tag')->nullable();
            $table->string('attribute')->nullable();
            $table->integer('times')->default(1);

            $table->foreign('referrer_id')->references('id')->on('pages')->onDelete('cascade');;
            $table->foreign('referred_id')->references('id')->on('pages')->onDelete('cascade');;

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pages');
    }
}
