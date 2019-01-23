<?php

use yii\db\Migration;

/**
 * Class m181206_074147_create_role_has_permission_table
 */
class m181206_074147_create_role_has_permission_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('role_has_permission', [
            'id'            => $this->bigPrimaryKey(),
            'permission_id' => $this->bigInteger(),
            'role_id'       => $this->bigInteger(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('role_has_permission');
    }
}
