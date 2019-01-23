<?php

use yii\db\Migration;

/**
 * Class m181219_110345_create_logs_table
 */
class m181219_110345_create_logs_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('logs', [
            'id'        => $this->bigPrimaryKey(),
            'old_value' => $this->string(191),
            'new_value' => $this->string(191),
            'action'    => $this->string(191),
            'model'     => $this->string(191),
            'field'     => $this->string(191),
            'stamp'     => $this->dateTime(),
            'user_id'   => $this->bigInteger(),
            'model_id'  => $this->string(191),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('logs');
    }
}
