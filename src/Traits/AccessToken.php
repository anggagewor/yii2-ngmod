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

use DateTime;
use Yii;

trait AccessToken
{
    public function setToken( $authorization_code )
    {
        return true;
    }

    public function getToken( $authorization_code )
    {
        $token = Anggagewor\Ngmod\Models\AccessToken::find()->where([ 'authorization_code' => $authorization_code ])->one();
        return $token->token;
    }

    public function getRefreshCode( $access_token )
    {
        $token = Anggagewor\Ngmod\Models\AccessToken::find()->where([ 'token' => $access_token ])->one();
        return $token->refresh_token;
    }

    /**
     * @param string $access_token
     *
     * @return array|\yii\db\ActiveRecord|null
     * @throws \Exception
     */
    public function checkExpiresToken( $access_token )
    {
        $accessToken = $this->checkToken($access_token);
        if ( is_array($accessToken) ) {
            $result = [
                "name"    => "No Data Found",
                "message" => $access_token . " data not found on AccessToken::checkExpiresAccessToken()",
                'page_id' => pageID(),
                "code"    => 0,
                "status"  => 422,
            ];
            return Yii::$app->api->results($result[ 'status' ], $result);
        }

        $tokenNow = new DateTime(date('Y-m-d H:i:s'));
        $tokenexp = new DateTime($accessToken->expires_at);
        if ( $tokenexp < $tokenNow ) {
            // token expired bro
            $result = [
                "name"    => "Expired",
                "message" => $access_token . " has Expired AccessToken::checkExpiresAccessToken()",
                'page_id' => pageID(),
                "code"    => 0,
                "status"  => 401,
            ];
            return $result;
        }
        return $accessToken;
    }

    /**
     * @param  string $access_token
     *
     * @return array|\yii\db\ActiveRecord|null
     */
    public function checkToken( $access_token )
    {
        $accessToken = Anggagewor\Ngmod\Models\AccessToken::find()->where([ 'token' => $access_token ])->one();
        if ( !$accessToken ) {
            $result = [
                "name"    => "No Data Found",
                "message" => $access_token . " data not found on AccessToken::checkAccessToken()",
                'page_id' => pageID(),
                "code"    => 0,
                "status"  => 422,
            ];
            return $result;
        }
        return $accessToken;
    }
}