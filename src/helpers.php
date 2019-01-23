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


if ( !function_exists('issetNull') ) {

    /**
     * @param $arg
     * @param $string
     *
     * @return mixed|null
     */
    function issetNull( $arg, $string )
    {
        if ( is_array($arg) ) {
            return isset($arg[ $string ]) ? $arg[ $string ] : null;
        }

        if ( is_object($arg) ) {
            return isset($arg->$string) ? $arg->$string : null;
        }
    }
}

if ( !function_exists('issetString') ) {
    /**
     * @param $arg
     * @param $string
     * @param $out
     *
     * @return mixed|null
     */
    function issetString( $arg, $string, $out )
    {
        if ( issetNull($arg, $string) ) {
            return issetNull($arg, $string);
        } else {
            return $out;
        }
    }
}
if ( !function_exists('convert_array_to_obj_recursive') ) {
    /**
     * @param $a
     *
     * @return array|object
     */
    function convert_array_to_obj_recursive( $a )
    {
        if ( is_array($a) ) {
            foreach ( $a as $k => $v ) {
                if ( is_integer($k) ) {
                    // only need this if you want to keep the array indexes separate
                    // from the object notation: eg. $o->{1}
                    $a[ 'index' ][ $k ] = convert_array_to_obj_recursive($v);
                } else {
                    $a[ $k ] = convert_array_to_obj_recursive($v);
                }
            }

            return (object)$a;
        }

        // else maintain the type of $a
        return $a;
    }
}

if ( !function_exists('asJson') ) {
    /**
     * @param $data
     *
     * @return \yii\console\Response|\yii\web\Response
     */
    function asJson( $data )
    {
        $response         = Yii::$app->getResponse();
        $response->format = \yii\web\Response::FORMAT_JSON;
        $response->data   = $data;
        return $response;
    }
}

if ( !function_exists('asXml') ) {
    /**
     * @param $data
     *
     * @return \yii\console\Response|\yii\web\Response
     */
    function asXml( $data )
    {
        $response         = Yii::$app->getResponse();
        $response->format = \yii\web\Response::FORMAT_XML;
        $response->data   = $data;
        return $response;
    }
}

if ( !function_exists('uniqidReal') ) {
    /**
     * @param int $lenght
     *
     * @return bool|string
     * @throws Exception
     */
    function uniqidReal( $lenght = 50 )
    {
        // uniqid gives 13 chars, but you could adjust it to your needs.
        if ( function_exists("random_bytes") ) {
            $bytes = random_bytes(ceil($lenght / 2));
        } elseif ( function_exists("openssl_random_pseudo_bytes") ) {
            $bytes = openssl_random_pseudo_bytes(ceil($lenght / 2));
        } else {
            throw new \Exception("no cryptographically secure random function available");
        }
        return substr(bin2hex($bytes), 0, $lenght);
    }
}

if ( !function_exists('invalidParameterType') ) {
    /**
     * @param $param
     * @param $type
     *
     * @return \yii\console\Response|\yii\web\Response
     */
    function invalidParameterType( $param, $type )
    {
        $results = [
            "name"    => "Unprocessable Entity",
            "message" => "parameter " . $param . " must be an " . $type,
            "code"    => 0,
            "status"  => 422,
        ];
        return asJson(Yii::$app->api->results(422, $results));
    }
}
if ( !function_exists('pageID') ) {
    /**
     * @return string|string[]|null
     */
    function pageID()
    {
        $actionID    = Yii::$app->controller->action->id;
        $url         = preg_replace('/\?.*/', '', Yii::$app->request->url);
        $currentPage = trim($url, '/') . '/' . Yii::$app->controller->id . '/' . $actionID;
        $tmp         = preg_replace('/\//', ".", $currentPage);
        return $tmp;
    }
}

if ( !function_exists('savePageID') ) {
    /**
     * @param $pageID
     */
    function savePageID( $pageID )
    {
        $permission = Anggagewor\Ngmod\Models\Permissions::find()->where([ 'name' => $pageID ])->one();
        if ( !$permission ) {
            $modelPermission       = new Anggagewor\Ngmod\Models\Permissions();
            $modelPermission->name = $pageID;
            $modelPermission->save();
        }
    }
}

if ( !function_exists('missingParameter') ) {
    /**
     * @param $parameter
     *
     * @return \yii\console\Response|\yii\web\Response
     */
    function missingParameter( $parameter )
    {
        $results = [
            "name"    => "Unprocessable Entity",
            "message" => "Missing parameter " . $parameter,
            "code"    => 0,
            "status"  => 422,
        ];
        return asJson(Yii::$app->api->results(422, $results));
    }
}

if ( !function_exists('appEnv') ) {
    /**
     * @return string
     */
    function appEnv()
    {
        $env = 'LOCAL';
        if ( getenv('YII_ENV') == 'local' ):
            $env = 'LOCAL';
        endif;
        if ( getenv('YII_ENV') == 'dev' ):
            $env = 'LOCAL';
        endif;
        if ( getenv('YII_ENV') == 'staging' ):
            $env = 'STAGING';
        endif;
        if ( getenv('YII_ENV') == 'production' ):
            $env = 'PRODUCTION';
        endif;
        return $env;
    }
}
if ( !function_exists('slugify') ) {
    /**
     * @param string $text
     *
     * @return false|string|string[]|null
     */
    function slugify( $text )
    {
        // replace non letter or digits by -
        $text = preg_replace('~[^\pL\d]+~u', '-', $text);

        // transliterate
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);

        // remove unwanted characters
        $text = preg_replace('~[^-\w]+~', '', $text);

        // trim
        $text = trim($text, '-');

        // remove duplicate -
        $text = preg_replace('~-+~', '-', $text);

        // lowercase
        $text = strtolower($text);

        if ( empty($text) ) {
            return 'n-a';
        }

        return $text;
    }
}