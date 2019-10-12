<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateArticleStatisticTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('article_statistic', function (Blueprint $table) {
            $table->comment = '文章统计';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_general_ci';
            $table->engine = 'InnoDB';

            $table->increments('id');

            $table->integer('article_id')->comment('文章ID');

            $table->integer('show_num')->default(0)->comment('曝光量');
            $table->integer('read_num')->default(0)->comment('阅读量');
            $table->integer('like_num')->default(0)->comment('喜欢');
            $table->integer('share_num')->default(0)->comment('分享');
            $table->integer('comment_num')->default(0)->comment('评论');
            $table->integer('unlike_num')->default(0)->comment('不感兴趣');

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
        Schema::dropIfExists('article_statistic');
    }
}
