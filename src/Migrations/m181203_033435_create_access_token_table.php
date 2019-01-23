<?php
/**
 * Licensed under the MIT/X11 License (http://opensource.org/licenses/MIT)
 * Copyright 2019 - Angga Purnama <anggagewor@gmail.com>
 * Permission is hereby granted, free of charge,
 * to any person obtaining a copy of this software and associated documentation files (the "Software"),
 * to deal in the Software without restriction,
 * including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense,
 * and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so,
 * subject to the following conditions:
 * The above copyright notice and this permission notice shall be included in all copies
 * or substantial portions of the Software.
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED,
 * INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
 * IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM,
 * DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE,
 * ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */


use yii\db\Migration;

/**
 * Class m181203_033435_create_access_token_table
 */
class m181203_033435_create_access_token_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('access_token', [
            'id'                     => $this->primaryKey(),
            'token'                  => $this->string(191), // token
            'authorization_code'     => $this->string(191), // authorization_code
            'expires_at'             => $this->dateTime(), // expires_at
            'user_id'                => $this->bigInteger(), // user_id
            'refresh_token'          => $this->string(191), // refresh_token

            /**
             * ini adalah kolom yang wajib ada di setiap table
             */
            'created_by_user_id'     => $this->bigInteger(), // created by user id
            'updated_by_user_id'     => $this->bigInteger(), // updated by user id
            'created_date_user_id'   => $this->dateTime(), // created date user id
            'updated_date_user_id'   => $this->dateTime(), // updated date user id
            'created_by_client_id'   => $this->bigInteger(), // created by client id
            'updated_by_client_id'   => $this->bigInteger(), // updated by client id
            'created_date_client_id' => $this->dateTime(), // created date client id
            'updated_date_client_id' => $this->dateTime(), // updated date client id
            'created_via'            => $this->string(191), // created via
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('access_token');
    }
}
