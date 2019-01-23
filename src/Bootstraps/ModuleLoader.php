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

namespace Anggagewor\Ngmod\Bootstraps;

use Yii;
use yii\base\BootstrapInterface;
use yii\base\Event;
use yii\base\InvalidConfigException;

/**
 * Class ModuleLoader
 *
 * @package Anggagewor\Ngmod\Bootstraps
 */
class ModuleLoader implements BootstrapInterface
{
    const CACHE_ID = 'erp_modules_config';
    public $modules_paths = [ '@app/module' ];

    /**
     * @param \yii\base\Application $app
     *
     * @throws InvalidConfigException
     */
    public function bootstrap( $app )
    {
        $this->getModulesConfig();

    }

    /**
     * @throws InvalidConfigException
     */
    public function getModulesConfig()
    {
        $modules = Yii::$app->cache->get(self::CACHE_ID);
        if ( $modules === false ) {
            $modules = [];
            foreach ( $this->modules_paths as $module_path ) {
                $path = Yii::getAlias($module_path);
                if ( is_dir($path) ) {
                    foreach ( scandir($path) as $module ) {
                        if ( $module[ 0 ] == '.' ) {
                            // skip ".", ".." and hidden files
                            continue;
                        }
                        $base        = $path . DIRECTORY_SEPARATOR . $module;
                        $config_file = $base . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'main.php';
                        if ( $module != 'Base.php' && !is_file($config_file) ) {
                            throw new InvalidConfigException("Module configuration requires a 'main.php' file! ");
                        }
                        $modules[ $base ] = require( $config_file );
                    }
                }
            }
            if ( !YII_DEBUG ) {
                Yii::$app->cache->set(self::CACHE_ID, $modules);
            }
        }
        $this->load($modules);
    }

    /**
     * @param $modules
     *
     * @throws InvalidConfigException
     */
    private function load( $modules )
    {
        foreach ( $modules as $basePath => $config ) {
            // Check mandatory config options
            if ( !isset($config[ 'class' ]) || !isset($config[ 'id' ]) ) {
                throw new InvalidConfigException("Module configuration requires an id and class attribute!");
            }
            $this->register($basePath, $config);
        }
    }

    /**
     * @param $basePath
     * @param $config
     */
    private function register( $basePath, $config )
    {
        // Set module alias
        if ( isset($config[ 'namespace' ]) ) {
            Yii::setAlias('@' . str_replace('\\', '/', $config[ 'namespace' ]), $basePath);
        } else {
            Yii::setAlias('@' . $config[ 'id' ], $basePath);
        }
        // Handle Submodules
        if ( !isset($config[ 'modules' ]) ) {
            $config[ 'modules' ] = [];
        }
        // Append URL Rules
        if ( isset($config[ 'urlManagerRules' ]) ) {
            Yii::$app->urlManager->addRules($config[ 'urlManagerRules' ], false);
        }
        $moduleConfig = [
            'class'   => $config[ 'class' ],
            'modules' => $config[ 'modules' ]
        ];
        // Register Yii Module
        Yii::$app->setModule($config[ 'id' ], $moduleConfig);
        // Register Event Handlers
        if ( isset($config[ 'events' ]) ) {
            foreach ( $config[ 'events' ] as $event ) {
                if ( isset($event[ 'class' ]) ) {
                    Event::on($event[ 'class' ], $event[ 'event' ], $event[ 'callback' ]);
                } else {
                    Event::on($event[ 0 ], $event[ 1 ], $event[ 2 ]);
                }
            }
        }
    }

}