<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSysRoleTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sys_role', function (Blueprint $table) {
            $table->comment = '系统角色';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_general_ci';
            $table->engine = 'InnoDB';

            $table->increments('id');

            $table->string('name', 50)->comment('角色名称');
            $table->string('desc')->default('')->comment('描述');
            $table->string('menu_ids', 1000)->default('')->comment('菜单ID');

            $table->unsignedInteger('seq_order')->default(0)->comment('排序');
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
        Schema::dropIfExists('sys_role');
    }
}
