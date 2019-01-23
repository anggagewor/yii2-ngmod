<?php

use yii\db\Migration;

/**
 * Class m181206_074047_create_role_has_user_table
 */
class m181206_074047_create_role_has_user_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('role_has_user', [
            'id'      => $this->bigPrimaryKey(),
            'user_id' => $this->bigInteger(),
            'role_id' => $this->bigInteger(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('role_has_user');
    }
}
