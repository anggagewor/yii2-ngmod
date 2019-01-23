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

namespace Anggagewor\Ngmod;


use Anggagewor\Ngmod\Models\Log;
use Exception;
use Yii;
use yii\base\Behavior;
use yii\db\ActiveRecord;

/**
 * Class LogBehavior
 *
 * @package Anggagewor\Ngmod
 *
 * @property array           $oldAttributes
 * @property null|string|int $userId
 * @property string|false    $normalizedPk
 */
class LogBehavior extends Behavior
{

    const ACTION_DELETE = 'DELETE';
    const ACTION_CREATE = 'CREATE';
    const ACTION_SET    = 'SET';
    const ACTION_CHANGE = 'CHANGE';
    public  $allowed        = array();
    public  $ignored        = array();
    public  $ignoredClasses = array();
    public  $dateFormat     = 'Y-m-d H:i:s';
    public  $userAttribute  = null;
    public  $storeTimestamp = false;
    public  $skipNulls      = true;
    public  $active         = true;
    private $_oldattributes = array();

    /**
     * @return array
     */
    public function events()
    {
        return [
            ActiveRecord::EVENT_AFTER_FIND   => 'afterFind',
            ActiveRecord::EVENT_AFTER_INSERT => 'afterInsert',
            ActiveRecord::EVENT_AFTER_UPDATE => 'afterUpdate',
            ActiveRecord::EVENT_AFTER_DELETE => 'afterDelete',
        ];
    }

    /**
     * @param $event
     */
    public function afterDelete( $event )
    {
        $this->leaveTrail(self::ACTION_DELETE);
    }

    /**
     * @param      $action
     * @param null $name
     * @param null $value
     * @param null $old_value
     *
     * @return bool
     */
    public function leaveTrail( $action, $name = null, $value = null, $old_value = null )
    {
        if ( $this->active ) {
            $log       = new Log();
            $className = $this->owner->className();
            if (
                isset(Yii::$app->params[ 'audittrail.FQNPrefix' ])
                && Yii::$app->params[ 'audittrail.FQNPrefix' ]
            ) {
                $classNameParts = explode('\\', $className);
                $log->model     = end($classNameParts);
            } else {
                $log->model = $className;
            }
            $log->old_value = $old_value;
            $log->new_value = $value;
            $log->action    = $action;
            $log->model_id  = (string)$this->getNormalizedPk();
            $log->field     = $name;
            $log->stamp     = $this->storeTimestamp ? time() : date($this->dateFormat
            ); // If we are storing a timestamp lets get one else lets get the date
            $log->user_id   = (string)$this->getUserId(); // Lets get the user id
            return $log->save();
        } else {
            return true;
        }
    }

    /**
     * @return false|string
     */
    protected function getNormalizedPk()
    {
        $pk = $this->owner->getPrimaryKey();
        return is_array($pk) ? json_encode($pk) : $pk;
    }

    /**
     * @return int|string|null
     */
    public function getUserId()
    {
        if ( isset($this->userAttribute) ) {
            $data = $this->owner->getAttributes();
            return isset($data[ $this->userAttribute ]) ? $data[ $this->userAttribute ] : null;
        } else {
            try {
                $userid = Yii::$app->user->identity->userId;
                return empty($userid) ? null : $userid;
            } catch ( Exception $e ) { //If we have no user object, this must be a command line program
                return null;
            }
        }
    }

    /**
     * @param $event
     */
    public function afterFind( $event )
    {
        $this->setOldAttributes($this->owner->getAttributes());
    }

    /**
     * @param $event
     */
    public function afterInsert( $event )
    {
        $this->audit(true);
    }

    /**
     * @param $insert
     */
    public function audit( $insert )
    {
        $allowedFields  = $this->allowed;
        $ignoredFields  = $this->ignored;
        $ignoredClasses = $this->ignoredClasses;
        $newattributes  = $this->owner->getAttributes();
        $oldattributes  = $this->getOldAttributes();
        // Lets check if the whole class should be ignored
        if ( sizeof($ignoredClasses) > 0 ) {
            if ( array_search(get_class($this->owner), $ignoredClasses) !== false ) {
                return;
            }
        }
        // Lets unset fields which are not allowed
        if ( sizeof($allowedFields) > 0 ) {
            foreach ( $newattributes as $f => $v ) {
                if ( array_search($f, $allowedFields) === false ) {
                    unset($newattributes[ $f ]);
                }
            }
            foreach ( $oldattributes as $f => $v ) {
                if ( array_search($f, $allowedFields) === false ) {
                    unset($oldattributes[ $f ]);
                }
            }
        }
        // Lets unset fields which are ignored
        if ( sizeof($ignoredFields) > 0 ) {
            foreach ( $newattributes as $f => $v ) {
                if ( array_search($f, $ignoredFields) !== false ) {
                    unset($newattributes[ $f ]);
                }
            }
            foreach ( $oldattributes as $f => $v ) {
                if ( array_search($f, $ignoredFields) !== false ) {
                    unset($oldattributes[ $f ]);
                }
            }
        }
        // If no difference then WHY?
        // There is some kind of problem here that means "0" and 1 do not diff for array_diff so beware: stackoverflow.com/questions/12004231/php-array-diff-weirdness :S
        if ( count(array_diff_assoc($newattributes, $oldattributes)) <= 0 ) {
            return;
        }
        // If this is a new record lets add a CREATE notification
        if ( $insert ) {
            $this->leaveTrail(self::ACTION_CREATE);
        }
        // Now lets actually write the attributes
        $this->auditAttributes($insert, $newattributes, $oldattributes);
        // Reset old attributes to handle the case with the same model instance updated multiple times
        $this->setOldAttributes($this->owner->getAttributes());
    }

    /**
     * @return array
     */
    public function getOldAttributes()
    {
        return $this->_oldattributes;
    }

    /**
     * @param $value
     */
    public function setOldAttributes( $value )
    {
        $this->_oldattributes = $value;
    }

    /**
     * @param       $insert
     * @param       $newattributes
     * @param array $oldattributes
     */
    public function auditAttributes( $insert, $newattributes, $oldattributes = array() )
    {
        foreach ( $newattributes as $name => $value ) {
            $old = isset($oldattributes[ $name ]) ? $oldattributes[ $name ] : '';
            // If we are skipping nulls then lets see if both sides are null
            if ( $this->skipNulls && empty($old) && empty($value) ) {
                continue;
            }
            // If they are not the same lets write an audit log
            if ( $value != $old ) {
                $this->leaveTrail($insert ? self::ACTION_SET : self::ACTION_CHANGE, $name, $value, $old);
            }
        }
    }

    /**
     * @param $event
     */
    public function afterUpdate( $event )
    {
        $this->audit(true);
    }
}