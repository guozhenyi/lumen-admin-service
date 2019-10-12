<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserWeixinTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_weixin', function (Blueprint $table) {
            $table->comment = '微信';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_general_ci';
            $table->engine = 'InnoDB';

            $table->increments('id');

            $table->string('code', 19)->comment('编码');

            $table->unsignedInteger('user_id')->default(0)->comment('用户ID');

            $table->string('openid')->comment('微信openid');

            $table->string('access_token', 500)->default('')->comment('微信token');
            $table->string('refresh_token', 500)->default('')->comment('微信刷新token');

            $table->unsignedInteger('expires_in')->default(0)->comment('access_token过期秒数');
            $table->unsignedInteger('expires_at')->default(0)->comment('access_token过期时间戳');
            $table->unsignedInteger('refresh_at')->default(0)->comment('refresh_token过期时间戳');

            $table->string('nickname')->default('')->comment('昵称');
            $table->string('gender')->default('')->comment('性别');
            $table->string('avatar')->default('')->comment('头像');
            $table->string('country')->default('')->comment('国家');
            $table->string('province')->default('')->comment('省份');
            $table->string('city')->default('')->comment('城市');
            $table->string('privilege')->default('')->comment('用户特权信息');

            $table->string('scope')->default('')->comment('授权作用域');

            $table->string('unionid')->default('')->comment('UNIONID');

            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();

            $table->unique('openid');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_weixin');
    }
}
