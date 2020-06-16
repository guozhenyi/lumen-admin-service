<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAssetTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('asset', function (Blueprint $table) {
            $table->comment = '资源';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_general_ci';
            $table->engine = 'InnoDB';

            $table->increments('id');

            $table->string('md5', 32)->comment('md5');
            $table->string('ext', 10)->comment('扩展名');
            $table->string('path')->comment('路径');
            $table->string('url')->comment('链接');
            $table->string('origin', 100)->default('')->comment('原始文件名');
            $table->string('size', 50)->default('')->comment('大小');

            $table->integer('create_at')->default(0);
            $table->timestamp('created_at')->useCurrent();

            $table->index('md5');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('asset');
    }
}
