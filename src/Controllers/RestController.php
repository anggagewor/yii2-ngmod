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

namespace Anggagewor\Ngmod\Controllers;

use yii\web\Controller;
use Anggagewor\Ngmod\Models\Options;
use Anggagewor\Ngmod\Traits\AuthorizationCode;
use Anggagewor\Ngmod\Traits\Request;
use Anggagewor\Ngmod\Traits\AccessToken;
use Anggagewor\Ngmod\Traits\Login;
use Yii;
class RestController extends Controller
{
    use AuthorizationCode, Request, AccessToken, Login;
    public function beforeAction( $actions )
    {

        $timezone = Options::find()->where([ 'name' => 'timezone', 'is_active' => 1 ])->one();
        if ( $timezone ) {
            Yii::$app->timeZone = $timezone->value;
        }

        $request = $this->validate('post');
        if ( is_array($request) ) {
            asJson(Yii::$app->api->results($request[ 'status' ], $request));
            return false;
        }
    	$except = Yii::$app->params[ 'BeforePageIDExcept' ];
    	savePageID(pageID());
        if ( in_array(pageID(), $except) ) {
            return true;
        }
        
        $headers = Yii::$app->request->headers;
        $post     = Yii::$app->request->post();
        if ( $headers->has('x-access-token') ) {
            $accessToken = $headers->get('x-access-token');
        } else {
            $accessToken = issetNull($post, 'access_token');
        }

        if ( !empty($accessToken) ) {
            $checkExp = $this->checkExpiresToken($accessToken);
            if ( is_array($checkExp) ) {
                asJson($checkExp);
                return false;
            }

            $this->loginWithToken($accessToken);
            return true;
        }
        $this->asJson(Yii::$app->api->results(401, [
                'status'  => 0,
                'page_id' => pageID(),
                'message' => 'could not be authenticated',
                'data'    => [],
            ]));
        return false;
    }
}