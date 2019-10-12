<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFileTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('file', function (Blueprint $table) {
            $table->comment = '文件';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_general_ci';

            $table->increments('id');

            $table->string('code', 40)->comment('编码');
            $table->string('ext', 10)->comment('扩展名');
            $table->string('path')->comment('路径');
            $table->string('url')->comment('链接');
            $table->string('origin')->comment('原始文件名');
            $table->string('size')->default('');

            $table->timestamp('created_at')->useCurrent();

            $table->unique('code');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('file');
    }
}
