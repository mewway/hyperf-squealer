<?php

class CreateOperationLoggerTable extends \Hyperf\Database\Migrations\Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \Hyperf\Database\Schema\Schema::create('user_operation_logger', function (\Hyperf\Database\Schema\Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('trigger_class', 192)->default('')->comment('触发类');
            $table->bigInteger('associated_id')->default(0)->comment('关联主键');
            $table->string('associated_value', 32)->default('')->comment('业务关键键');
            $table->string('user_name', 32)->default('')->comment('用户名称');
            $table->string('user_id', 32)->default('')->comment('用户名称');
            $table->string('client_ip', 32)->default('')->comment('客户端ip');
            $table->dateTime('trigger_time')->comment('触发时间');
            $table->string('event_desc', 256)->default('')->comment('事件描述');
            $table->string('change_content', 2048)->default('')->comment('变化内容');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        \Hyperf\Database\Schema\Schema::dropIfExists('user_operation_logger');
    }
}
