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

use Anggagewor\Ngmod\Facades\User;
use Yii;
use yii\db\ActiveRecord;


/**
 *
 * @property mixed               $parent
 * @property \yii\db\ActiveQuery $user
 * @property int                 $id
 * @property string              $old_value
 * @property string              $new_value
 * @property string              $action
 * @property string              $model
 * @property string              $field
 * @property string              $stamp
 * @property int                 $user_id
 * @property int                 $model_id
 *
 */
class Log extends ActiveRecord
{
    private $_message_category = 'audittrail';

    /**
     * @return string
     */
    public static function tableName()
    {
        if ( isset(Yii::$app->params[ 'audittrail.table' ]) ) {
            return Yii::$app->params[ 'audittrail.table' ];
        } else {
            return '{{%audit_trail}}';
        }
    }

    /**
     * @return object|\yii\db\Connection|null
     * @throws \yii\base\InvalidConfigException
     */
    public static function getDb()
    {
        if ( isset(Yii::$app->params[ 'audittrail.db' ]) ) {
            return Yii::$app->get(Yii::$app->params[ 'audittrail.db' ]);
        } else {
            return parent::getDb();
        }
    }

    /**
     * @param $query
     */
    public static function recently( $query )
    {
        $query->orderBy([ '[[stamp]]' => SORT_DESC ]);
    }

    public function init()
    {
        parent::init();
        \Yii::$app->i18n->translations[ $this->_message_category ] = [
            'class' => 'yii\i18n\PhpMessageSource',
        ];
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'id'        => Yii::t('app', 'ID'),
            'old_value' => Yii::t('app', 'Old Value'),
            'new_value' => Yii::t('app', 'New Value'),
            'action'    => Yii::t('app', 'Action'),
            'model'     => Yii::t('app', 'Type'),
            'field'     => Yii::t('app', 'Field'),
            'stamp'     => Yii::t('app', 'Stamp'),
            'user_id'   => Yii::t('app', 'User'),
            'model_id'  => Yii::t('app', 'ID'),
        ];
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [ [ 'action', 'model', 'stamp', 'model_id' ], 'required' ],
            [ 'action', 'string', 'max' => 255 ],
            [ 'model', 'string', 'max' => 255 ],
            [ 'field', 'string', 'max' => 255 ],
            [ 'model_id', 'string', 'max' => 255 ],
            [ [ 'user_id' ], 'integer' ],
            [ [ 'old_value', 'new_value' ], 'safe' ]
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        if ( isset(Yii::$app->params[ 'audittrail.model' ]) && isset(Yii::$app->params[ 'audittrail.model' ]) ) {
            return $this->hasOne(Yii::$app->params[ 'audittrail.model' ], [ 'id' => 'user_id' ]);
        } else {
            return $this->hasOne(User::getIdentityClass(), [ 'id' => 'user_id' ]);
        }
    }

    /**
     * @return mixed
     */
    public function getParent()
    {
        $model_name =
            (
            isset(Yii::$app->params[ 'audittrail.FQNPrefix' ])
            && rtrim(Yii::$app->params[ 'audittrail.FQNPrefix' ], '\\') ?
                rtrim(Yii::$app->params[ 'audittrail.FQNPrefix' ], '\\') . '\\' :
                ''
            ) . $this->model;
        return new $model_name;
    }

}