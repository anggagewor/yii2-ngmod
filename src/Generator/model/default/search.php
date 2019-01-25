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
* Copyright <?php echo date('Y');?> - <?php echo $generator->authorName;?> <<?php echo $generator->authorEmail;?>>
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
namespace <?= $generator->ns.'\\search' ?>;

use Yii;
use Anggagewor\Ngmod\Facades\Request;
use <?= $generator->ns.'\\'.$className ?>;
/**
 * Class <?= $className.'Search' ?>
 *
 * @package <?= $generator->ns.'\\search' ?>
 */
class <?= $className.'Search' ?> extends <?php echo $className;?>
{
	/**
     * @param $params
     *
     * @return array
     */
    public static function search( $params )
    {

        $page   = Request::getQueryParam('page');
        $limit  = Request::getQueryParam('limit');
        $order  = Request::getQueryParam('order');
        $search = Request::getQueryParam('search');
        $sort   = Request::getQueryParam('sort');
        if ( isset($search) ) {
            $params = $search;
        }
        $limit  = isset($limit) ? $limit : 10;
        $page   = isset($page) ? $page : 1;
        $offset = ( $page - 1 ) * $limit;
        $query  = <?php echo $className;?>::find()->asArray(true)
                          ->limit($limit)
                          ->offset($offset);
<?php foreach ( $properties as $property => $data ): ?>
  <?php if($data['type'] == 'int'):?>
    ( isset($params[ '<?php echo str_replace('$','',$property);?>' ]) ) ? $query->andFilterWhere([ '<?php echo str_replace('$','',$property);?>' => $params[ '<?php echo str_replace('$','',$property);?>' ] ]) : null;
  <?php endif;?>
    <?php if($data['type'] == 'string'):?>
    ( isset($params[ '<?php echo str_replace('$','',$property);?>' ]) ) ? $query->andFilterWhere([ 'ilike', '<?php echo str_replace('$','',$property);?>' ,$params[ '<?php echo str_replace('$','',$property);?>' ] ]) : null;
  <?php endif;?>
<?php endforeach; ?>
        if ( isset($order) ) {
            /**
             * @params int $sort
             *         3 untuk SORT_DESC
             *         4 untuk SORT_ASC
             */
            $query->orderBy([ $order => (int)$sort ]);

        }
        $additional_info = [
            'page'       => $page,
            'size'       => $limit,
            'totalCount' => (int)$query->count()
        ];
        return [
            'data' => $query->all(),
            'info' => $additional_info
        ];
    }
}