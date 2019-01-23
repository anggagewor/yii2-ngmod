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

namespace Anggagewor\Ngmod\Traits;


use app\models\AuthorizationCodes;
use app\models\Users;
use Yii;

/**
 * Trait AuthorizationCode
 *
 * @package app\components\traits
 */
trait AuthorizationCode
{
    /**
     * @param string $username
     * @param string $password
     *
     * @return AuthorizationCodes|bool
     * @throws \Exception
     */
    public function getAuthCode( $username, $password )
    {
        $user = $this->_user($username, $password);
        if ( !$user ) {
            return false;
        }

        $now                              = date('Y-m-d H:i:s');
        $duration                         = Yii::$app->params[ 'AUTHORIZATION_CODE_TIMEOUT' ];
        $exp                              = date('Y-m-d H:i:s', strtotime("+$duration sec"));
        $authCode                         = new AuthorizationCodes();
        $authCode->code                   = uniqidReal();
        $authCode->expires_at             = $exp;
        $authCode->user_id                = $user->id;
        $authCode->application_code       = 'HRMS';
        $authCode->created_by_user_id     = $user->id;
        $authCode->updated_by_user_id     = null;
        $authCode->created_date_user_id   = $now;
        $authCode->updated_date_user_id   = null;
        $authCode->created_by_client_id   = null;
        $authCode->updated_by_client_id   = null;
        $authCode->created_date_client_id = null;
        $authCode->updated_date_client_id = null;
        $authCode->created_via            = 'TRAIT::AUTHORIZATIONCODE::GETAUTHCODE';
        if ( !$authCode->save() ) {
            return false;
        }

        return $authCode;

    }

    /**
     * @param string $username
     * @param string $password
     *
     * @return array|bool|\yii\db\ActiveRecord|null
     */
    public function _user( $username, $password )
    {
        $user = Users::find()->where([ 'username' => $username, 'password' => $password ])->one();
        if ( !$user ) {
            return false;
        }
        return $user;
    }
}