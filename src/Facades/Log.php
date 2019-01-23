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

/** @noinspection PhpUnnecessaryFullyQualifiedNameInspection */

/**
 * Yii::$app->get('log') facade.
 *
 * Methods
 *
 * @method static \yii\base\Behavior attachBehavior( string $name, string|array|\yii\base\Behavior $behavior ) Attaches a behavior to this component.
 * @see \yii\base\Component::attachBehavior
 *
 * @method static void attachBehaviors( array $behaviors ) Attaches a list of behaviors to the component.
 * @see \yii\base\Component::attachBehaviors
 *
 * @method static array behaviors() Returns a list of behaviors that this component should behave as.
 * @see \yii\base\Component::behaviors
 *
 * @method static null|\yii\base\Behavior detachBehavior( string $name ) Detaches a behavior from the component.
 * @see \yii\base\Component::detachBehavior
 *
 * @method static void detachBehaviors() Detaches all behaviors from the component.
 * @see \yii\base\Component::detachBehaviors
 *
 * @method static void dispatch( array $messages, bool $final ) Dispatches the logged messages to [ [ targets ] ].
 * @see \yii\log\Dispatcher::dispatch
 *
 * @method static void ensureBehaviors() Makes sure that the behaviors declared in [ [ behaviors() ] ] are attached to this component.
 * @see \yii\base\Component::ensureBehaviors
 *
 * @method static null|\yii\base\Behavior getBehavior( string $name ) Returns the named behavior object.
 * @see \yii\base\Component::getBehavior
 *
 * @method static \yii\base\Behavior[] getBehaviors() Returns all behaviors attached to this component.
 * @see \yii\base\Component::getBehaviors
 *
 * @method static int getFlushInterval()
 * @see \yii\log\Dispatcher::getFlushInterval
 *
 * @method static \yii\log\Logger getLogger() Gets the connected logger.
 * @see \yii\log\Dispatcher::getLogger
 *
 * @method static int getTraceLevel()
 * @see \yii\log\Dispatcher::getTraceLevel
 *
 * @method static bool hasEventHandlers( string $name ) Returns a value indicating whether there is any handler attached to the named event.
 * @see \yii\base\Component::hasEventHandlers
 *
 * @method static bool off( string $name, callable $handler = null ) Detaches an existing event handler from this component.
 * @see \yii\base\Component::off
 *
 * @method static void on( string $name, callable $handler, mixed $data = null, bool $append = true ) Attaches an event handler to an event.
 * @see \yii\base\Component::on
 *
 * @method static void setFlushInterval( int $value )
 * @see \yii\log\Dispatcher::setFlushInterval
 *
 * @method static void setLogger( \yii\log\Logger|string|array $value ) Sets the connected logger.
 * @see \yii\log\Dispatcher::setLogger
 *
 * @method static void setTraceLevel( int $value )
 * @see \yii\log\Dispatcher::setTraceLevel
 *
 * @method static void trigger( string $name, \yii\base\Event $event = null ) Triggers an event.
 * @see \yii\base\Component::trigger
 *
 * Property accessors
 *
 * @method static array|\yii\log\Target[] getTargets() Returns the log targets.
 * @see \yii\log\Dispatcher::targets
 *
 * @method static void setTargets( array|\yii\log\Target[] $value ) Sets the log targets.
 * @see \yii\log\Dispatcher::targets
 */
class Log extends Facade
{
    /**
     * @inheritDoc
     */
    public static function getFacadeComponentId()
    {
        return 'log';
    }
}
