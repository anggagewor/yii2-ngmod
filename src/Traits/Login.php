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

namespace Anggagewor\Ngmod\Traits;


use Anggagewor\Ngmod\Facades\User;
use Anggagewor\Ngmod\Models\AccessToken;
use Yii;

trait Login
{
    /**
     * @param $token
     *
     * @return bool
     */
    public function loginWithToken( $token )
    {
        $token     = AccessToken::find()->where([ 'token' => $token ])->one();
        $userClass = User::getIdentityClass();
        $identity  = $userClass::findIdentity($token->user_id);
        return Yii::$app->user->login($identity, 0);
    }
}