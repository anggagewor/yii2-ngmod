<?php
/**
 * Licensed under the MIT/X11 License (http://opensource.org/licenses/MIT)
 * Copyright 2019 - Angga Purnama
 * <anggagewor@gmail.com>
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

namespace Anggagewor\Ngmod\Generator\module;

use Yii;
use yii\gii\CodeFile;
use yii\helpers\Html;
use yii\helpers\StringHelper;

/**
 *
 * @property bool   $modulePath
 * @property string $name
 * @property string $controllerNamespace
 */
class Generator extends \yii\gii\Generator
{
    public $moduleClass;
    public $moduleID;
    public $authorName;
    public $authorEmail;


    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'Erp Module Generator';
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription()
    {
        return 'This generator helps you to generate the skeleton code needed by a Erp module.';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return array_merge(parent::rules(), [
                [ [ 'moduleID', 'moduleClass' ], 'filter', 'filter' => 'trim' ],
                [ [ 'moduleID', 'moduleClass','authorName','authorEmail' ], 'required' ],
                [
                    [ 'moduleID' ],
                    'match',
                    'pattern' => '/^[\w\\-]+$/',
                    'message' => 'Only word characters and dashes are allowed.'
                ],
                [
                    [ 'moduleClass' ],
                    'match',
                    'pattern' => '/^[\w\\\\]*$/',
                    'message' => 'Only word characters and backslashes are allowed.'
                ],
                [ [ 'moduleClass' ], 'validateModuleClass' ],
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'moduleID'    => 'Module ID',
            'moduleClass' => 'Module Class',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function hints()
    {
        return [
            'moduleID'    => 'This refers to the ID of the module, e.g., <code>admin</code>.',
            'moduleClass' => 'This is the fully qualified class name of the module, e.g., <code>app\modules\admin\Module</code>.',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function successMessage()
    {
        if ( Yii::$app->hasModule($this->moduleID) ) {
            $link = Html::a('try it now', Yii::$app->getUrlManager()->createUrl($this->moduleID),
                [ 'target' => '_blank' ]
            );

            return "The module has been generated successfully. You may $link.";
        }

        $output = <<<EOD
<p>The module has been generated successfully.</p>
EOD;
        $code   = "SUCCESS";

        return $output . '<pre>' . highlight_string($code, true) . '</pre>';
    }

    /**
     * {@inheritdoc}
     */
    public function requiredTemplates()
    {
        return [ '.gitattributes','.gitignore','CHANGELOG.md','CONTRIBUTING.md','README.md','module.php','CONDUCT.md', 'controller.php', 'erp.php', 'routes.php', 'LICENSE.md','.gitkeep' ];
    }

    /**
     * {@inheritdoc}
     */
    public function generate()
    {
        $files      = [];
        $modulePath = $this->getModulePath();
        $files[]    = new CodeFile(
            $modulePath . '/' . StringHelper::basename($this->moduleClass) . '.php',
            $this->render("module.php")
        );
        $files[]    = new CodeFile(
            $modulePath . '/controllers/DefaultController.php',
            $this->render("controller.php")
        );
        $files[]    = new CodeFile(
            $modulePath . '/config/main.php',
            $this->render("erp.php")
        );
        $files[]    = new CodeFile(
            $modulePath . '/config/routes.php',
            $this->render("routes.php")
        );
        $files[]    = new CodeFile(
            $modulePath . '/LICENSE.md',
            $this->render("LICENSE.md")
        );
        $files[]    = new CodeFile(
            $modulePath . '/.gitattributes',
            $this->render(".gitattributes")
        );
        $files[]    = new CodeFile(
            $modulePath . '/.gitignore',
            $this->render(".gitignore")
        );
        $files[]    = new CodeFile(
            $modulePath . '/CHANGELOG.md',
            $this->render("CHANGELOG.md")
        );
        $files[]    = new CodeFile(
            $modulePath . '/CONTRIBUTING.md',
            $this->render("CONTRIBUTING.md")
        );
        $files[]    = new CodeFile(
            $modulePath . '/README.md',
            $this->render("README.md")
        );
        $files[]    = new CodeFile(
            $modulePath . '/CONDUCT.md',
            $this->render("CONDUCT.md")
        );
        $files[]    = new CodeFile(
            $modulePath . '/models/.gitkeep',
            $this->render(".gitkeep")
        );
        $files[]    = new CodeFile(
            $modulePath . '/commands/.gitkeep',
            $this->render(".gitkeep")
        );

        return $files;
    }

    /**
     * @return bool the directory that contains the module class
     */
    public function getModulePath()
    {
        return Yii::getAlias('@' . str_replace('\\', '/',
                substr($this->moduleClass, 0, strrpos($this->moduleClass, '\\'))
            )
        );
    }

    /**
     * Validates [[moduleClass]] to make sure it is a fully qualified class name.
     */
    public function validateModuleClass()
    {
        if ( strpos($this->moduleClass, '\\') === false
             || Yii::getAlias('@' . str_replace('\\', '/', $this->moduleClass), false) === false ) {
            $this->addError('moduleClass', 'Module class must be properly namespaced.');
        }
        if ( empty($this->moduleClass) || substr_compare($this->moduleClass, '\\', -1, 1) === 0 ) {
            $this->addError('moduleClass',
                'Module class name must not be empty. Please enter a fully qualified class name. e.g. "app\\modules\\admin\\Module".'
            );
        }
    }

    /**
     * @return string the controller namespace of the module.
     */
    public function getControllerNamespace()
    {
        return substr($this->moduleClass, 0, strrpos($this->moduleClass, '\\')) . '\controllers';
    }
}
