<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSysUserActionLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sys_user_action_log', function (Blueprint $table) {
            $table->comment = '系统用户操作日志';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_general_ci';
            $table->engine = 'InnoDB';

            $table->increments('id');

            $table->unsignedInteger('editor_id')->comment('操作者ID');

            $table->string('editor_name', 40)->default('')->comment('操作者姓名');

            $table->string('describe')->comment('描述');

            $table->timestamp('created_at')->useCurrent();

            $table->string('ipv4', 40)->default('')->comment('IP地址');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sys_user_action_log');
    }
}
