<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateArticleTagTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('article_tag', function (Blueprint $table) {
            $table->comment = '文章标签';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_general_ci';
            $table->engine = 'InnoDB';

            $table->increments('id');

            $table->integer('article_id')->comment('文章ID');
            $table->integer('tag_id')->comment('标签ID');
            $table->unsignedTinyInteger('status')->default(1)->comment('状态[1=>正常,2=>下架]');

            $table->integer('editor_id')->comment('编辑人员ID');
            $table->integer('editor_name')->comment('编辑人员');

            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('article_tag');
    }
}
