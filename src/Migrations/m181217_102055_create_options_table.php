<?php

use yii\db\Migration;

/**
 * Class m181217_102055_create_options_table
 */
class m181217_102055_create_options_table
    extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('options', [
            'id'        => $this->bigPrimaryKey(),
            'name'      => $this->string(191)->unique(),
            'value'     => $this->text(),
            'is_active' => $this->smallInteger(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('options');
    }
}
