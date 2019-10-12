<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTagTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tag', function (Blueprint $table) {
            $table->comment = '标签';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_general_ci';
            $table->engine = 'InnoDB';

            $table->increments('id');

            $table->string('name', 40)->comment('名称');

            $table->integer('article_total')->default(0)->comment('文章总数量');
            $table->integer('normal_num')->default(0)->comment('正常文章数量');
            $table->integer('down_num')->default(0)->comment('已下架文章数量');
            $table->unsignedInteger('seq_order')->default(0)->comment('排序');
            $table->unsignedTinyInteger('status')->default(1)->comment('状态[1=>正常]');
            $table->integer('editor_id')->comment('编辑人员ID');
            $table->integer('editor_name')->comment('编辑人员');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tag');
    }
}
