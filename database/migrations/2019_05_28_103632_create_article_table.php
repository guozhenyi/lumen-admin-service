<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateArticleTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('article', function (Blueprint $table) {
            $table->comment = '文章';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_general_ci';
            $table->engine = 'InnoDB';

            $table->increments('id');

            $table->string('code', 40)->comment('编码');
            $table->string('title')->comment('标题');
            $table->string('author_name')->comment('作者');
            $table->integer('editor_id')->comment('编辑人员ID');
            $table->integer('editor_name')->comment('编辑人员');
            $table->integer('validity_day')->default(0)->comment('时效天数[0:长期]');
            $table->integer('validity_time')->default(0)->comment('时效时间戳');
            $table->unsignedTinyInteger('status')->default(1)->comment('状态[1=>正常]');
            $table->timestamp('publish_at')->useCurrent()->comment('发布时间');
            $table->timestamp('created_at')->useCurrent()->comment('创建时间');
            $table->text('content')->comment('文章内容');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('article');
    }
}
