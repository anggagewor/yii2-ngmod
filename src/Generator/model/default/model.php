<?php
/**
 * This is the template for generating the model class of a specified table.
 */

/* @var $this yii\web\View */
/* @var $generator yii\gii\generators\model\Generator */
/* @var $tableName string full table name */
/* @var $className string class name */
/* @var $queryClassName string query class name */
/* @var $tableSchema yii\db\TableSchema */
/* @var $properties array list of properties (property => [type, name. comment]) */
/* @var $labels string[] list of attribute labels (name => label) */
/* @var $rules string[] list of validation rules */
/* @var $relations array list of relations (name => relation declaration) */

echo "<?php\n";
?>

/**
* Licensed under the MIT/X11 License (http://opensource.org/licenses/MIT)
* Copyright 2018 - Angga Purnama
<anggagewor@gmail.com>
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
namespace <?= $generator->ns ?>;

use Yii;

/**
* This is the model class for table "<?= $generator->generateTableName($tableName) ?>".
*
<?php foreach ( $properties as $property => $data ): ?>
    * @property <?= "{$data['type']} \${$property}" . ( $data[ 'comment' ] ? ' ' . strtr($data[ 'comment' ],
            [ "\n" => ' ' ]
        ) : '' ) . "\n" ?>
<?php endforeach; ?>
<?php if ( !empty($relations) ): ?>
    *
    <?php foreach ( $relations as $name => $relation ): ?>
        * @property <?= $relation[ 1 ] . ( $relation[ 2 ] ? '[]' : '' ) . ' $' . lcfirst($name) . "\n" ?>
    <?php endforeach; ?>
<?php endif; ?>
*/
class <?= $className ?> extends <?= '\\' . ltrim($generator->baseClass, '\\') . "\n" ?>
{


/**
* @return array
*/
public function behaviors()
{
return [
'Anggagewor\Ngmod\LogBehavior'
];
}

/**
* {@inheritdoc}
*/
public static function tableName()
{
return '<?= $generator->generateTableName($tableName) ?>';
}
<?php if ( $generator->db !== 'db' ): ?>

    /**
    * @return \yii\db\Connection the database connection used by this AR class.
    */
    public static function getDb()
    {
    return Yii::$app->get('<?= $generator->db ?>');
    }
<?php endif; ?>

/**
* {@inheritdoc}
*/
public function rules()
{
return [<?= empty($rules) ? '' : ( "\n            " . implode(",\n            ", $rules) . ",\n        " ) ?>];
}

/**
* {@inheritdoc}
*/
public function attributeLabels()
{
return [
<?php foreach ( $labels as $name => $label ): ?>
    <?= "'$name' => " . $generator->generateString($label) . ",\n" ?>
<?php endforeach; ?>
];
}
<?php foreach ( $relations as $name => $relation ): ?>

    /**
    * @return \yii\db\ActiveQuery
    */
    public function get<?= $name ?>()
    {
    <?= $relation[ 0 ] . "\n" ?>
    }
<?php endforeach; ?>
<?php if ( $queryClassName ): ?>
    <?php
    $queryClassFullName = ( $generator->ns === $generator->queryNs ) ? $queryClassName : '\\' . $generator->queryNs
                                                                                         . '\\' . $queryClassName;
    echo "\n";
    ?>
    /**
    * {@inheritdoc}
    * @return <?= $queryClassFullName ?> the active query used by this AR class.
    */
    public static function find()
    {
    return new <?= $queryClassFullName ?>(get_called_class());
    }
<?php endif; ?>
}
