<?php

use yii\db\Migration;

/**
 * Class m181206_073943_create_role_table
 */
class m181206_073943_create_role_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('role', [
            'id'   => $this->bigPrimaryKey(),
            'name' => $this->string(191), //
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('role');
    }
}
