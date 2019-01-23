<?php
/**
 * Licensed under the MIT/X11 License (http://opensource.org/licenses/MIT)
 * Copyright 2018 - Angga Purnama <anggagewor@gmail.com>
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

namespace Anggagewor\Ngmod\Models;

use Yii;

/**
 * Class AccessToken
 *
 * @package app\models
 * @property string $token
 * @property string $authorization_code
 * @property string $expires_at
 * @property int    $user_id
 * @property string $refresh_token
 * @property int    $created_by_user_id
 * @property int    $updated_by_user_id
 * @property string $created_date_user_id
 * @property string $updated_date_user_id
 * @property int    $created_by_client_id
 * @property int    $updated_by_client_id
 * @property string $created_date_client_id
 * @property string $updated_date_client_id
 * @property string $created_via
 *
 */
class AccessToken extends \yii\db\ActiveRecord
{


    /**
     * @return array
     */
    public function behaviors()
    {
        return [
            'Anggagewor\Ngmod\LogBehavior'
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'access_token';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [
                [
                    'expires_at',
                    'created_date_user_id',
                    'updated_date_user_id',
                    'created_date_client_id',
                    'updated_date_client_id'
                ],
                'safe'
            ],
            [
                [
                    'user_id',
                    'created_by_user_id',
                    'updated_by_user_id',
                    'created_by_client_id',
                    'updated_by_client_id'
                ],
                'default',
                'value' => null
            ],
            [
                [
                    'user_id',
                    'created_by_user_id',
                    'updated_by_user_id',
                    'created_by_client_id',
                    'updated_by_client_id'
                ],
                'integer'
            ],
            [
                [
                    'token',
                    'authorization_code',
                    'refresh_token',
                    'created_via'
                ],
                'string',
                'max' => 191
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id'                     => 'ID',
            'token'                  => 'Token',
            'authorization_code'     => 'Authorization Code',
            'expires_at'             => 'Expires At',
            'user_id'                => 'User ID',
            'refresh_token'          => 'Refresh Token',
            'created_by_user_id'     => 'Created By User ID',
            'updated_by_user_id'     => 'Updated By User ID',
            'created_date_user_id'   => 'Created Date User ID',
            'updated_date_user_id'   => 'Updated Date User ID',
            'created_by_client_id'   => 'Created By Client ID',
            'updated_by_client_id'   => 'Updated By Client ID',
            'created_date_client_id' => 'Created Date Client ID',
            'updated_date_client_id' => 'Updated Date Client ID',
            'created_via'            => 'Created Via',
        ];
    }
}
