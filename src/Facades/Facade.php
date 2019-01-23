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


namespace Anggagewor\Ngmod\Facades;

use yii\base\Application;
use yii\base\InvalidConfigException;

/**
 * Base facade class.
 */
abstract class Facade
{
    /**
     * Facaded component property accessors.
     *
     * @var array
     */
    private static $_accessors = [];

    /**
     * The facaded application.
     *
     * @var Application
     */
    private static $_app;

    /**
     * Facaded components.
     *
     * @var object[]
     */
    private static $_components = [];

    /**
     * Prevents the class to be instantiated.
     */
    private function __construct()
    {
    }

    /**
     * Redirects static calls to component instance calls.
     *
     * @inheritDoc
     */
    public static function __callStatic( $name, $arguments )
    {
        $id = static::getFacadeComponentId();
        if ( !isset(self::$_accessors[ $id ]) ) {
            self::$_accessors[ $id ] = [];
            foreach ( ( new \ReflectionClass(static::getFacadeComponent()) )->getProperties(
                \ReflectionProperty::IS_PUBLIC & ~\ReflectionProperty::IS_STATIC
            ) as $property ) {
                $accessor                                     = ucfirst($property->getName());
                self::$_accessors[ $id ][ 'get' . $accessor ] = $property->getName();
                self::$_accessors[ $id ][ 'set' . $accessor ] = $property->getName();
            }
        }
        if ( isset(self::$_accessors[ $id ][ $name ]) ) {
            if ( $name[ 0 ] === 'g' ) {
                return static::getFacadeComponent()->{self::$_accessors[ $id ][ $name ]};
            } else {
                static::getFacadeComponent()->{self::$_accessors[ $id ][ $name ]} = reset($arguments);
                return null;
            }
        } else {
            return call_user_func_array([
                static::getFacadeComponent(),
                $name,
            ], $arguments
            );
        }
    }

    /**
     * Returns a component ID being facaded.
     *
     * @return string
     * @throws \yii\base\InvalidConfigException
     */
    public static function getFacadeComponentId()
    {
        throw new InvalidConfigException('Facade must implement getFacadeComponentId method.');
    }

    /**
     * Returns a component being facaded.
     *
     * @return object
     */
    public static function getFacadeComponent()
    {
        $id = static::getFacadeComponentId();
        if ( !isset(self::$_components[ $id ]) ) {
            self::$_components[ $id ] = static::getFacadeApplication()->get($id);
        }
        return self::$_components[ $id ];
    }

    /**
     * Returns the facaded application.
     *
     * @return Application
     */
    public static function getFacadeApplication()
    {
        if ( !isset(self::$_app) ) {
            self::$_app = \Yii::$app;
        }
        return self::$_app;
    }

    /**
     * Clears a resolved facade component.
     *
     * @param string $id
     */
    public static function clearResolvedFacadeComponent( $id )
    {
        unset(self::$_accessors[ $id ], self::$_components[ $id ]);
    }

    /**
     * Sets the facaded application.
     *
     * @param Application $value
     */
    public static function setFacadeApplication( Application $value )
    {
        self::$_app = $value;
        self::clearResolvedFacadeComponents();
    }

    /**
     * Clears all resolved facade components.
     */
    public static function clearResolvedFacadeComponents()
    {
        self::$_accessors  = [];
        self::$_components = [];
    }

    /**
     * Prevents the class to be serialized.
     */
    private function __wakeup()
    {
    }

    /**
     * Prevents the class to be cloned.
     */
    private function __clone()
    {
    }
}
