<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSysTokenBlacklistTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sys_token_blacklist', function (Blueprint $table) {
            $table->comment = '系统token黑名单';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_general_ci';
            $table->engine = 'InnoDB';

            $table->increments('id');

            $table->string('token_hash', 40)->comment('token哈希值');
            $table->unsignedInteger('expires_at')->default(0)->comment('过期时间戳');

            $table->timestamp('created_at')->useCurrent()->comment('创建时间');

            $table->index('token_hash');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sys_token_blacklist');
    }
}
