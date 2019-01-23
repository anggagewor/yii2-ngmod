<?php

use yii\db\Migration;

/**
 * Class m181206_073659_create_permissions_table
 */
class m181206_073659_create_permissions_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('permissions', [
            'id'   => $this->bigPrimaryKey(),
            'name' => $this->string(191), //
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('permissions');
    }
}
