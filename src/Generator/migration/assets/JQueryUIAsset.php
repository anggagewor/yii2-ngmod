<?php
namespace Anggagewor\Ngmod\Generator\migration\assets;
use yii\web\AssetBundle;

class JQueryUIAsset extends AssetBundle
{
     public $sourcePath = '@bower/jquery-ui';
     public $js = [
         'jquery-ui.js',
         'ui/widgets/sortable.js',
     ];
     public $depends = [
         'yii\web\JqueryAsset',
     ];
}