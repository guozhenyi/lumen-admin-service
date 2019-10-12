<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSysMenuTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sys_menu', function (Blueprint $table) {
            $table->comment = '系统菜单';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_general_ci';
            $table->engine = 'InnoDB';

            $table->increments('id');

            $table->unsignedInteger('parent_id')->default(0)->comment('父菜单ID');
            $table->unsignedTinyInteger('type')->default(1)->comment('菜单类型[1:目录菜单,2:页面,3:页面上的按键]');
            $table->string('name', 50)->comment('菜单名称');
            $table->unsignedInteger('seq_order')->default(0)->comment('排序');
            $table->string('route_api')->default('')->comment('接口路由');
            $table->string('route_web')->default('')->comment('前端路由');

            $table->timestamp('created_at')->useCurrent()->comment('创建时间');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sys_menu');
    }
}
