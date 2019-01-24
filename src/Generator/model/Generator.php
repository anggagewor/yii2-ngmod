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


namespace Anggagewor\Ngmod\Generator\model;

use Yii;
use yii\base\NotSupportedException;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\db\Connection;
use yii\db\Schema;
use yii\gii\CodeFile;
use yii\helpers\Inflector;

/**
 * Class Generator
 *
 * @package app\generator\model
 *
 * @property string             $tablePrefix
 * @property string             $name
 * @property string[]           $schemaNames
 * @property \yii\db\Connection $dbConnection
 * @property null|string        $dbDriverName
 */
class Generator extends \yii\gii\Generator
{
    const RELATIONS_NONE        = 'none';
    const RELATIONS_ALL         = 'all';
    const RELATIONS_ALL_INVERSE = 'all-inverse';

    public    $db                                 = 'db';
    public    $ns                                 = 'app\models';
    public    $tableName;
    public    $modelClass;
    public    $baseClass                          = 'yii\db\ActiveRecord';
    public    $generateRelations                  = self::RELATIONS_ALL;
    public    $generateRelationsFromCurrentSchema = true;
    public    $generateLabelsFromComments         = false;
    public    $useTablePrefix                     = false;
    public    $standardizeCapitals                = false;
    public    $useSchemaName                      = true;
    public    $generateQuery                      = false;
    public    $queryNs                            = 'app\models';
    public    $queryClass;
    public    $queryBaseClass                     = 'yii\db\ActiveQuery';
    protected $tableNames;
    protected $classNames;

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'Erp Model Generator';
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription()
    {
        return 'This generator generates an ActiveRecord class for the specified database table.';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return array_merge(parent::rules(), [
                [
                    [ 'db', 'ns', 'tableName', 'modelClass', 'baseClass', 'queryNs', 'queryClass', 'queryBaseClass' ],
                    'filter',
                    'filter' => 'trim'
                ],
                [
                    [ 'ns', 'queryNs' ],
                    'filter',
                    'filter' => function ( $value ) {
                        return trim($value, '\\');
                    }
                ],

                [ [ 'db', 'ns', 'tableName', 'baseClass', 'queryNs', 'queryBaseClass' ], 'required' ],
                [
                    [ 'db', 'modelClass', 'queryClass' ],
                    'match',
                    'pattern' => '/^\w+$/',
                    'message' => 'Only word characters are allowed.'
                ],
                [
                    [ 'ns', 'baseClass', 'queryNs', 'queryBaseClass' ],
                    'match',
                    'pattern' => '/^[\w\\\\]+$/',
                    'message' => 'Only word characters and backslashes are allowed.'
                ],
                [
                    [ 'tableName' ],
                    'match',
                    'pattern' => '/^([\w ]+\.)?([\w\* ]+)$/',
                    'message' => 'Only word characters, and optionally spaces, an asterisk and/or a dot are allowed.'
                ],
                [ [ 'db' ], 'validateDb' ],
                [ [ 'ns', 'queryNs' ], 'validateNamespace' ],
                [ [ 'tableName' ], 'validateTableName' ],
                [ [ 'modelClass' ], 'validateModelClass', 'skipOnEmpty' => false ],
                [ [ 'baseClass' ], 'validateClass', 'params' => [ 'extends' => ActiveRecord::className() ] ],
                [ [ 'queryBaseClass' ], 'validateClass', 'params' => [ 'extends' => ActiveQuery::className() ] ],
                [
                    [ 'generateRelations' ],
                    'in',
                    'range' => [ self::RELATIONS_NONE, self::RELATIONS_ALL, self::RELATIONS_ALL_INVERSE ]
                ],
                [
                    [
                        'generateLabelsFromComments',
                        'useTablePrefix',
                        'useSchemaName',
                        'generateQuery',
                        'generateRelationsFromCurrentSchema'
                    ],
                    'boolean'
                ],
                [ [ 'enableI18N', 'standardizeCapitals' ], 'boolean' ],
                [ [ 'messageCategory' ], 'validateMessageCategory', 'skipOnEmpty' => false ],
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
                'ns'                                 => 'Namespace',
                'db'                                 => 'Database Connection ID',
                'tableName'                          => 'Table Name',
                'standardizeCapitals'                => 'Standardize Capitals',
                'modelClass'                         => 'Model Class Name',
                'baseClass'                          => 'Base Class',
                'generateRelations'                  => 'Generate Relations',
                'generateRelationsFromCurrentSchema' => 'Generate Relations from Current Schema',
                'generateLabelsFromComments'         => 'Generate Labels from DB Comments',
                'generateQuery'                      => 'Generate ActiveQuery',
                'queryNs'                            => 'ActiveQuery Namespace',
                'queryClass'                         => 'ActiveQuery Class',
                'queryBaseClass'                     => 'ActiveQuery Base Class',
                'useSchemaName'                      => 'Use Schema Name',
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function hints()
    {
        return array_merge(parent::hints(), [
                'ns'                                 => 'This is the namespace of the ActiveRecord class to be generated, e.g., <code>app\models</code>',
                'db'                                 => 'This is the ID of the DB application component.',
                'tableName'                          => 'This is the name of the DB table that the new ActiveRecord class is associated with, e.g. <code>post</code>.
                The table name may consist of the DB schema part if needed, e.g. <code>public.post</code>.
                The table name may end with asterisk to match multiple table names, e.g. <code>tbl_*</code>
                will match tables who name starts with <code>tbl_</code>. In this case, multiple ActiveRecord classes
                will be generated, one for each matching table name; and the class names will be generated from
                the matching characters. For example, table <code>tbl_post</code> will generate <code>Post</code>
                class.',
                'modelClass'                         => 'This is the name of the ActiveRecord class to be generated. The class name should not contain
                the namespace part as it is specified in "Namespace". You do not need to specify the class name
                if "Table Name" ends with asterisk, in which case multiple ActiveRecord classes will be generated.',
                'standardizeCapitals'                => 'This indicates whether the generated class names should have standardized capitals. For example,
            table names like <code>SOME_TABLE</code> or <code>Other_Table</code> will have class names <code>SomeTable</code>
            and <code>OtherTable</code>, respectively. If not checked, the same tables will have class names <code>SOMETABLE</code>
            and <code>OtherTable</code> instead.',
                'baseClass'                          => 'This is the base class of the new ActiveRecord class. It should be a fully qualified namespaced class name.',
                'generateRelations'                  => 'This indicates whether the generator should generate relations based on
                foreign key constraints it detects in the database. Note that if your database contains too many tables,
                you may want to uncheck this option to accelerate the code generation process.',
                'generateRelationsFromCurrentSchema' => 'This indicates whether the generator should generate relations from current schema or from all available schemas.',
                'generateLabelsFromComments'         => 'This indicates whether the generator should generate attribute labels
                by using the comments of the corresponding DB columns.',
                'useTablePrefix'                     => 'This indicates whether the table name returned by the generated ActiveRecord class
                should consider the <code>tablePrefix</code> setting of the DB connection. For example, if the
                table name is <code>tbl_post</code> and <code>tablePrefix=tbl_</code>, the ActiveRecord class
                will return the table name as <code>{{%post}}</code>.',
                'useSchemaName'                      => 'This indicates whether to include the schema name in the ActiveRecord class
                when it\'s auto generated. Only non default schema would be used.',
                'generateQuery'                      => 'This indicates whether to generate ActiveQuery for the ActiveRecord class.',
                'queryNs'                            => 'This is the namespace of the ActiveQuery class to be generated, e.g., <code>app\models</code>',
                'queryClass'                         => 'This is the name of the ActiveQuery class to be generated. The class name should not contain
                the namespace part as it is specified in "ActiveQuery Namespace". You do not need to specify the class name
                if "Table Name" ends with asterisk, in which case multiple ActiveQuery classes will be generated.',
                'queryBaseClass'                     => 'This is the base class of the new ActiveQuery class. It should be a fully qualified namespaced class name.',
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function autoCompleteData()
    {
        $db = $this->getDbConnection();
        if ( $db !== null ) {
            return [
                'tableName' => function () use ( $db ) {
                    return $db->getSchema()->getTableNames();
                },
            ];
        }

        return [];
    }

    /**
     * @return object|null
     * @throws \yii\base\InvalidConfigException
     */
    protected function getDbConnection()
    {
        return Yii::$app->get($this->db, false);
    }

    /**
     * {@inheritdoc}
     */
    public function requiredTemplates()
    {
        // @todo make 'query.php' to be required before 2.1 release
        return [ 'model.php','search.php'/*, 'query.php'*/ ];
    }

    /**
     * {@inheritdoc}
     */
    public function stickyAttributes()
    {
        return array_merge(parent::stickyAttributes(), [
                'ns',
                'db',
                'baseClass',
                'generateRelations',
                'generateLabelsFromComments',
                'queryNs',
                'queryBaseClass',
                'useTablePrefix',
                'generateQuery'
            ]
        );
    }

    /**
     * @return string
     * @throws \yii\base\InvalidConfigException
     */
    public function getTablePrefix()
    {
        $db = $this->getDbConnection();
        if ( $db !== null ) {
            return $db->tablePrefix;
        }

        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function generate()
    {
        $files     = [];
        $relations = $this->generateRelations();
        $db        = $this->getDbConnection();
        foreach ( $this->getTableNames() as $tableName ) {
            // model :
            $modelClassName = $this->generateClassName($tableName);
            $queryClassName = ( $this->generateQuery ) ? $this->generateQueryClassName($modelClassName) : false;
            $tableSchema    = $db->getTableSchema($tableName);
            $params         = [
                'tableName'      => $tableName,
                'className'      => $modelClassName,
                'queryClassName' => $queryClassName,
                'tableSchema'    => $tableSchema,
                'properties'     => $this->generateProperties($tableSchema),
                'labels'         => $this->generateLabels($tableSchema),
                'rules'          => $this->generateRules($tableSchema),
                'relations'      => isset($relations[ $tableName ]) ? $relations[ $tableName ] : [],
            ];
            $files[]        = new CodeFile(
                Yii::getAlias('@' . str_replace('\\', '/', $this->ns)) . '/' . $modelClassName . '.php',
                $this->render('model.php', $params)
            );
            $files[]        = new CodeFile(
                Yii::getAlias('@' . str_replace('\\', '/', $this->ns)) . '/search/' . $modelClassName . 'Search.php',
                $this->render('search.php', $params)
            );

            // query :
            if ( $queryClassName ) {
                $params[ 'className' ]      = $queryClassName;
                $params[ 'modelClassName' ] = $modelClassName;
                $files[]                    = new CodeFile(
                    Yii::getAlias('@' . str_replace('\\', '/', $this->queryNs)) . '/' . $queryClassName . '.php',
                    $this->render('query.php', $params)
                );
            }
        }

        return $files;
    }

    /**
     * @return array|mixed
     * @throws NotSupportedException
     * @throws \ReflectionException
     * @throws \yii\base\InvalidConfigException
     */
    protected function generateRelations()
    {
        if ( $this->generateRelations === self::RELATIONS_NONE ) {
            return [];
        }

        $db          = $this->getDbConnection();
        $relations   = [];
        $schemaNames = $this->getSchemaNames();
        foreach ( $schemaNames as $schemaName ) {
            foreach ( $db->getSchema()->getTableSchemas($schemaName) as $table ) {
                $className = $this->generateClassName($table->fullName);
                foreach ( $table->foreignKeys as $refs ) {
                    $refTable       = $refs[ 0 ];
                    $refTableSchema = $db->getTableSchema($refTable);
                    if ( $refTableSchema === null ) {
                        // Foreign key could point to non-existing table: https://github.com/yiisoft/yii2-gii/issues/34
                        continue;
                    }
                    unset($refs[ 0 ]);
                    $fks          = array_keys($refs);
                    $refClassName = $this->generateClassName($refTable);

                    // Add relation for this table
                    $link                                           = $this->generateRelationLink(array_flip($refs));
                    $relationName                                   = $this->generateRelationName($relations, $table,
                        $fks[ 0 ], false
                    );
                    $relations[ $table->fullName ][ $relationName ] = [
                        "return \$this->hasOne($refClassName::className(), $link);",
                        $refClassName,
                        false,
                    ];

                    // Add relation for the referenced table
                    $hasMany                                                 = $this->isHasManyRelation($table, $fks);
                    $link                                                    = $this->generateRelationLink($refs);
                    $relationName                                            = $this->generateRelationName($relations,
                        $refTableSchema, $className, $hasMany
                    );
                    $relations[ $refTableSchema->fullName ][ $relationName ] = [
                        "return \$this->" . ( $hasMany ? 'hasMany' : 'hasOne' ) . "($className::className(), $link);",
                        $className,
                        $hasMany,
                    ];
                }

                if ( ( $junctionFks = $this->checkJunctionTable($table) ) === false ) {
                    continue;
                }

                $relations = $this->generateManyManyRelations($table, $junctionFks, $relations);
            }
        }

        if ( $this->generateRelations === self::RELATIONS_ALL_INVERSE ) {
            return $this->addInverseRelations($relations);
        }

        return $relations;
    }

    /**
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    protected function getSchemaNames()
    {
        $db = $this->getDbConnection();

        if ( $this->generateRelationsFromCurrentSchema ) {
            if ( $db->schema->defaultSchema !== null ) {
                return [ $db->schema->defaultSchema ];
            }
            return [ '' ];
        }

        $schema = $db->getSchema();
        if ( $schema->hasMethod('getSchemaNames') ) { // keep BC to Yii versions < 2.0.4
            try {
                $schemaNames = $schema->getSchemaNames();
            } catch ( NotSupportedException $e ) {
                // schema names are not supported by schema
            }
        }
        if ( !isset($schemaNames) ) {
            if ( ( $pos = strpos($this->tableName, '.') ) !== false ) {
                $schemaNames = [ substr($this->tableName, 0, $pos) ];
            } else {
                $schemaNames = [ '' ];
            }
        }
        return $schemaNames;
    }

    /**
     * @param      $tableName
     * @param null $useSchemaName
     *
     * @return string
     * @throws \yii\base\InvalidConfigException
     */
    protected function generateClassName( $tableName, $useSchemaName = null )
    {
        if ( isset($this->classNames[ $tableName ]) ) {
            return $this->classNames[ $tableName ];
        }

        $schemaName    = '';
        $fullTableName = $tableName;
        if ( ( $pos = strrpos($tableName, '.') ) !== false ) {
            if ( ( $useSchemaName === null && $this->useSchemaName ) || $useSchemaName ) {
                $schemaName = substr($tableName, 0, $pos) . '_';
            }
            $tableName = substr($tableName, $pos + 1);
        }

        $db         = $this->getDbConnection();
        $patterns   = [];
        $patterns[] = "/^{$db->tablePrefix}(.*?)$/";
        $patterns[] = "/^(.*?){$db->tablePrefix}$/";
        if ( strpos($this->tableName, '*') !== false ) {
            $pattern = $this->tableName;
            if ( ( $pos = strrpos($pattern, '.') ) !== false ) {
                $pattern = substr($pattern, $pos + 1);
            }
            $patterns[] = '/^' . str_replace('*', '(\w+)', $pattern) . '$/';
        }
        $className = $tableName;
        foreach ( $patterns as $pattern ) {
            if ( preg_match($pattern, $tableName, $matches) ) {
                $className = $matches[ 1 ];
                break;
            }
        }

        if ( $this->standardizeCapitals ) {
            $schemaName = ctype_upper(preg_replace('/[_-]/', '', $schemaName)) ? strtolower($schemaName) : $schemaName;
            $className  = ctype_upper(preg_replace('/[_-]/', '', $className)) ? strtolower($className) : $className;
            return $this->classNames[ $fullTableName ] = Inflector::camelize(Inflector::camel2words($schemaName
                                                                                                    . $className
            )
            );
        } else {
            return $this->classNames[ $fullTableName ] = Inflector::id2camel($schemaName . $className, '_');
        }

    }

    /**
     * Generates the link parameter to be used in generating the relation declaration.
     *
     * @param array $refs reference constraint
     *
     * @return string the generated link parameter.
     */
    protected function generateRelationLink( $refs )
    {
        $pairs = [];
        foreach ( $refs as $a => $b ) {
            $pairs[] = "'$a' => '$b'";
        }

        return '[' . implode(', ', $pairs) . ']';
    }

    /**
     * @param $relations
     * @param $table
     * @param $key
     * @param $multiple
     *
     * @return string
     * @throws \ReflectionException
     */
    protected function generateRelationName( $relations, $table, $key, $multiple )
    {
        static $baseModel;
        /* @var $baseModel \yii\db\ActiveRecord */
        if ( $baseModel === null ) {
            $baseClass          = $this->baseClass;
            $baseClassReflector = new \ReflectionClass($baseClass);
            if ( $baseClassReflector->isAbstract() ) {
                $baseClassWrapper =
                    'namespace ' . __NAMESPACE__ . ';' .
                    'class GiiBaseClassWrapper extends \\' . $baseClass . ' {' .
                    'public static function tableName(){' .
                    'return "' . addslashes($table->fullName) . '";' .
                    '}' .
                    '};' .
                    'return new GiiBaseClassWrapper();';
                $baseModel        = eval($baseClassWrapper);
            } else {
                $baseModel = new $baseClass();
            }
            $baseModel->setAttributes([]);
        }

        if ( !empty($key) && strcasecmp($key, 'id') ) {
            if ( substr_compare($key, 'id', -2, 2, true) === 0 ) {
                $key = rtrim(substr($key, 0, -2), '_');
            } elseif ( substr_compare($key, 'id', 0, 2, true) === 0 ) {
                $key = ltrim(substr($key, 2, strlen($key)), '_');
            }
        }
        if ( $multiple ) {
            $key = Inflector::pluralize($key);
        }
        $name = $rawName = Inflector::id2camel($key, '_');
        $i    = 0;
        while ( $baseModel->hasProperty(lcfirst($name)) ) {
            $name = $rawName . ( $i++ );
        }
        while ( isset($table->columns[ lcfirst($name) ]) ) {
            $name = $rawName . ( $i++ );
        }
        while ( isset($relations[ $table->fullName ][ $name ]) ) {
            $name = $rawName . ( $i++ );
        }

        return $name;
    }

    /**
     * @param $table
     * @param $fks
     *
     * @return bool
     * @throws \yii\base\InvalidConfigException
     */
    protected function isHasManyRelation( $table, $fks )
    {
        $uniqueKeys = [ $table->primaryKey ];
        try {
            $uniqueKeys = array_merge($uniqueKeys, $this->getDbConnection()->getSchema()->findUniqueIndexes($table));
        } catch ( NotSupportedException $e ) {
            // ignore
        }
        foreach ( $uniqueKeys as $uniqueKey ) {
            if ( count(array_diff(array_merge($uniqueKey, $fks), array_intersect($uniqueKey, $fks))) === 0 ) {
                return false;
            }
        }
        return true;
    }

    /**
     * @param $table
     *
     * @return array|bool
     * @throws \yii\base\InvalidConfigException
     */
    protected function checkJunctionTable( $table )
    {
        if ( count($table->foreignKeys) < 2 ) {
            return false;
        }
        $uniqueKeys = [ $table->primaryKey ];
        try {
            $uniqueKeys = array_merge($uniqueKeys, $this->getDbConnection()->getSchema()->findUniqueIndexes($table));
        } catch ( NotSupportedException $e ) {
            // ignore
        }
        $result = [];
        // find all foreign key pairs that have all columns in an unique constraint
        $foreignKeys      = array_values($table->foreignKeys);
        $foreignKeysCount = count($foreignKeys);

        for ( $i = 0; $i < $foreignKeysCount; $i++ ) {
            $firstColumns = $foreignKeys[ $i ];
            unset($firstColumns[ 0 ]);

            for ( $j = $i + 1; $j < $foreignKeysCount; $j++ ) {
                $secondColumns = $foreignKeys[ $j ];
                unset($secondColumns[ 0 ]);

                $fks = array_merge(array_keys($firstColumns), array_keys($secondColumns));
                foreach ( $uniqueKeys as $uniqueKey ) {
                    if ( count(array_diff(array_merge($uniqueKey, $fks), array_intersect($uniqueKey, $fks))) === 0 ) {
                        // save the foreign key pair
                        $result[] = [ $foreignKeys[ $i ], $foreignKeys[ $j ] ];
                        break;
                    }
                }
            }
        }
        return empty($result) ? false : $result;
    }

    /**
     * @param $table
     * @param $fks
     * @param $relations
     *
     * @return mixed
     * @throws \ReflectionException
     * @throws \yii\base\InvalidConfigException
     */
    private function generateManyManyRelations( $table, $fks, $relations )
    {
        $db = $this->getDbConnection();

        foreach ( $fks as $pair ) {
            list($firstKey, $secondKey) = $pair;
            $table0 = $firstKey[ 0 ];
            $table1 = $secondKey[ 0 ];
            unset($firstKey[ 0 ], $secondKey[ 0 ]);
            $className0   = $this->generateClassName($table0);
            $className1   = $this->generateClassName($table1);
            $table0Schema = $db->getTableSchema($table0);
            $table1Schema = $db->getTableSchema($table1);

            // @see https://github.com/yiisoft/yii2-gii/issues/166
            if ( $table0Schema === null || $table1Schema === null ) {
                continue;
            }

            $link                                                  = $this->generateRelationLink(array_flip($secondKey)
            );
            $viaLink                                               = $this->generateRelationLink($firstKey);
            $relationName                                          = $this->generateRelationName($relations,
                $table0Schema, key($secondKey), true
            );
            $relations[ $table0Schema->fullName ][ $relationName ] = [
                "return \$this->hasMany($className1::className(), $link)->viaTable('"
                . $this->generateTableName($table->name) . "', $viaLink);",
                $className1,
                true,
            ];

            $link                                                  = $this->generateRelationLink(array_flip($firstKey));
            $viaLink                                               = $this->generateRelationLink($secondKey);
            $relationName                                          = $this->generateRelationName($relations,
                $table1Schema, key($firstKey), true
            );
            $relations[ $table1Schema->fullName ][ $relationName ] = [
                "return \$this->hasMany($className0::className(), $link)->viaTable('"
                . $this->generateTableName($table->name) . "', $viaLink);",
                $className0,
                true,
            ];
        }

        return $relations;
    }

    /**
     * @param $tableName
     *
     * @return string
     * @throws \yii\base\InvalidConfigException
     */
    public function generateTableName( $tableName )
    {
        if ( !$this->useTablePrefix ) {
            return $tableName;
        }

        $db = $this->getDbConnection();
        if ( preg_match("/^{$db->tablePrefix}(.*?)$/", $tableName, $matches) ) {
            $tableName = '{{%' . $matches[ 1 ] . '}}';
        } elseif ( preg_match("/^(.*?){$db->tablePrefix}$/", $tableName, $matches) ) {
            $tableName = '{{' . $matches[ 1 ] . '%}}';
        }
        return $tableName;
    }

    /**
     * @param $relations
     *
     * @return mixed
     * @throws \ReflectionException
     * @throws \yii\base\InvalidConfigException
     */
    protected function addInverseRelations( $relations )
    {
        $db            = $this->getDbConnection();
        $relationNames = [];

        $schemaNames = $this->getSchemaNames();
        foreach ( $schemaNames as $schemaName ) {
            foreach ( $db->schema->getTableSchemas($schemaName) as $table ) {
                $className = $this->generateClassName($table->fullName);
                foreach ( $table->foreignKeys as $refs ) {
                    $refTable       = $refs[ 0 ];
                    $refTableSchema = $db->getTableSchema($refTable);
                    if ( $refTableSchema === null ) {
                        // Foreign key could point to non-existing table: https://github.com/yiisoft/yii2-gii/issues/34
                        continue;
                    }
                    unset($refs[ 0 ]);
                    $fks = array_keys($refs);

                    $leftRelationName                                                 = $this->generateRelationName($relationNames,
                        $table, $fks[ 0 ], false
                    );
                    $relationNames[ $table->fullName ][ $leftRelationName ]           = true;
                    $hasMany                                                          = $this->isHasManyRelation($table,
                        $fks
                    );
                    $rightRelationName                                                = $this->generateRelationName(
                        $relationNames,
                        $refTableSchema,
                        $className,
                        $hasMany
                    );
                    $relationNames[ $refTableSchema->fullName ][ $rightRelationName ] = true;

                    $relations[ $table->fullName ][ $leftRelationName ][ 0 ]           =
                        rtrim($relations[ $table->fullName ][ $leftRelationName ][ 0 ], ';')
                        . "->inverseOf('" . lcfirst($rightRelationName) . "');";
                    $relations[ $refTableSchema->fullName ][ $rightRelationName ][ 0 ] =
                        rtrim($relations[ $refTableSchema->fullName ][ $rightRelationName ][ 0 ], ';')
                        . "->inverseOf('" . lcfirst($leftRelationName) . "');";
                }
            }
        }
        return $relations;
    }

    /**
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    protected function getTableNames()
    {
        if ( $this->tableNames !== null ) {
            return $this->tableNames;
        }
        $db = $this->getDbConnection();
        if ( $db === null ) {
            return [];
        }
        $tableNames = [];
        if ( strpos($this->tableName, '*') !== false ) {
            if ( ( $pos = strrpos($this->tableName, '.') ) !== false ) {
                $schema  = substr($this->tableName, 0, $pos);
                $pattern = '/^' . str_replace('*', '\w+', substr($this->tableName, $pos + 1)) . '$/';
            } else {
                $schema  = '';
                $pattern = '/^' . str_replace('*', '\w+', $this->tableName) . '$/';
            }

            foreach ( $db->schema->getTableNames($schema) as $table ) {
                if ( preg_match($pattern, $table) ) {
                    $tableNames[] = $schema === '' ? $table : ( $schema . '.' . $table );
                }
            }
        } elseif ( ( $table = $db->getTableSchema($this->tableName, true) ) !== null ) {
            $tableNames[]                         = $this->tableName;
            $this->classNames[ $this->tableName ] = $this->modelClass;
        }

        return $this->tableNames = $tableNames;
    }

    /**
     * Generates a query class name from the specified model class name.
     *
     * @param string $modelClassName model class name
     *
     * @return string generated class name
     */
    protected function generateQueryClassName( $modelClassName )
    {
        $queryClassName = $this->queryClass;
        if ( empty($queryClassName) || strpos($this->tableName, '*') !== false ) {
            $queryClassName = $modelClassName . 'Query';
        }
        return $queryClassName;
    }

    /**
     * Generates the properties for the specified table.
     *
     * @param \yii\db\TableSchema $table the table schema
     *
     * @return array the generated properties (property => type)
     * @since 2.0.6
     */
    protected function generateProperties( $table )
    {
        $properties = [];
        foreach ( $table->columns as $column ) {
            $columnPhpType = $column->phpType;
            if ( $columnPhpType === 'integer' ) {
                $type = 'int';
            } elseif ( $columnPhpType === 'boolean' ) {
                $type = 'bool';
            } else {
                $type = $columnPhpType;
            }
            $properties[ $column->name ] = [
                'type'    => $type,
                'name'    => $column->name,
                'comment' => $column->comment,
            ];
        }

        return $properties;
    }

    /**
     * Generates the attribute labels for the specified table.
     *
     * @param \yii\db\TableSchema $table the table schema
     *
     * @return array the generated attribute labels (name => label)
     */
    public function generateLabels( $table )
    {
        $labels = [];
        foreach ( $table->columns as $column ) {
            if ( $this->generateLabelsFromComments && !empty($column->comment) ) {
                $labels[ $column->name ] = $column->comment;
            } elseif ( !strcasecmp($column->name, 'id') ) {
                $labels[ $column->name ] = 'ID';
            } else {
                $label = Inflector::camel2words($column->name);
                if ( !empty($label) && substr_compare($label, ' id', -3, 3, true) === 0 ) {
                    $label = substr($label, 0, -3) . ' ID';
                }
                $labels[ $column->name ] = $label;
            }
        }

        return $labels;
    }

    /**
     * @param $table
     *
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    public function generateRules( $table )
    {
        $types   = [];
        $lengths = [];
        foreach ( $table->columns as $column ) {
            if ( $column->autoIncrement ) {
                continue;
            }
            if ( !$column->allowNull && $column->defaultValue === null ) {
                $types[ 'required' ][] = $column->name;
            }
            switch ( $column->type ) {
                case Schema::TYPE_SMALLINT:
                case Schema::TYPE_INTEGER:
                case Schema::TYPE_BIGINT:
                case Schema::TYPE_TINYINT:
                    $types[ 'integer' ][] = $column->name;
                    break;
                case Schema::TYPE_BOOLEAN:
                    $types[ 'boolean' ][] = $column->name;
                    break;
                case Schema::TYPE_FLOAT:
                case Schema::TYPE_DOUBLE:
                case Schema::TYPE_DECIMAL:
                case Schema::TYPE_MONEY:
                    $types[ 'number' ][] = $column->name;
                    break;
                case Schema::TYPE_DATE:
                case Schema::TYPE_TIME:
                case Schema::TYPE_DATETIME:
                case Schema::TYPE_TIMESTAMP:
                case Schema::TYPE_JSON:
                    $types[ 'safe' ][] = $column->name;
                    break;
                default: // strings
                    if ( $column->size > 0 ) {
                        $lengths[ $column->size ][] = $column->name;
                    } else {
                        $types[ 'string' ][] = $column->name;
                    }
            }
        }
        $rules      = [];
        $driverName = $this->getDbDriverName();
        foreach ( $types as $type => $columns ) {
            if ( $driverName === 'pgsql' && $type === 'integer' ) {
                $rules[] = "[['" . implode("', '", $columns) . "'], 'default', 'value' => null]";
            }
            $rules[] = "[['" . implode("', '", $columns) . "'], '$type']";
        }
        foreach ( $lengths as $length => $columns ) {
            $rules[] = "[['" . implode("', '", $columns) . "'], 'string', 'max' => $length]";
        }

        $db = $this->getDbConnection();

        // Unique indexes rules
        try {
            $uniqueIndexes = array_merge($db->getSchema()->findUniqueIndexes($table), [ $table->primaryKey ]);
            $uniqueIndexes = array_unique($uniqueIndexes, SORT_REGULAR);
            foreach ( $uniqueIndexes as $uniqueColumns ) {
                // Avoid validating auto incremental columns
                if ( !$this->isColumnAutoIncremental($table, $uniqueColumns) ) {
                    $attributesCount = count($uniqueColumns);

                    if ( $attributesCount === 1 ) {
                        $rules[] = "[['" . $uniqueColumns[ 0 ] . "'], 'unique']";
                    } elseif ( $attributesCount > 1 ) {
                        $columnsList = implode("', '", $uniqueColumns);
                        $rules[]     = "[['$columnsList'], 'unique', 'targetAttribute' => ['$columnsList']]";
                    }
                }
            }
        } catch ( NotSupportedException $e ) {
            // doesn't support unique indexes information...do nothing
        }

        // Exist rules for foreign keys
        foreach ( $table->foreignKeys as $refs ) {
            $refTable       = $refs[ 0 ];
            $refTableSchema = $db->getTableSchema($refTable);
            if ( $refTableSchema === null ) {
                // Foreign key could point to non-existing table: https://github.com/yiisoft/yii2-gii/issues/34
                continue;
            }
            $refClassName = $this->generateClassName($refTable);
            unset($refs[ 0 ]);
            $attributes       = implode("', '", array_keys($refs));
            $targetAttributes = [];
            foreach ( $refs as $key => $value ) {
                $targetAttributes[] = "'$key' => '$value'";
            }
            $targetAttributes = implode(', ', $targetAttributes);
            $rules[]          = "[['$attributes'], 'exist', 'skipOnError' => true, 'targetClass' => $refClassName::className(), 'targetAttribute' => [$targetAttributes]]";
        }

        return $rules;
    }

    /**
     * @return string|null
     * @throws \yii\base\InvalidConfigException
     */
    protected function getDbDriverName()
    {
        /** @var Connection $db */
        $db = $this->getDbConnection();
        return $db instanceof \yii\db\Connection ? $db->driverName : null;
    }

    /**
     * Checks if any of the specified columns is auto incremental.
     *
     * @param \yii\db\TableSchema $table   the table schema
     * @param array               $columns columns to check for autoIncrement property
     *
     * @return bool whether any of the specified columns is auto incremental.
     */
    protected function isColumnAutoIncremental( $table, $columns )
    {
        foreach ( $columns as $column ) {
            if ( isset($table->columns[ $column ]) && $table->columns[ $column ]->autoIncrement ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Validates the [[db]] attribute.
     */
    public function validateDb()
    {
        if ( !Yii::$app->has($this->db) ) {
            $this->addError('db', 'There is no application component named "db".');
        } elseif ( !Yii::$app->get($this->db) instanceof Connection ) {
            $this->addError('db', 'The "db" application component must be a DB connection instance.');
        }
    }

    /**
     * Validates the namespace.
     *
     * @param string $attribute Namespace variable.
     */
    public function validateNamespace( $attribute )
    {
        $value = $this->$attribute;
        $value = ltrim($value, '\\');
        $path  = Yii::getAlias('@' . str_replace('\\', '/', $value), false);
        if ( $path === false ) {
            $this->addError($attribute, 'Namespace must be associated with an existing directory.');
        }
    }

    /**
     * Validates the [[modelClass]] attribute.
     */
    public function validateModelClass()
    {
        if ( $this->isReservedKeyword($this->modelClass) ) {
            $this->addError('modelClass', 'Class name cannot be a reserved PHP keyword.');
        }
        if ( ( empty($this->tableName) || substr_compare($this->tableName, '*', -1, 1) ) && $this->modelClass == '' ) {
            $this->addError('modelClass', 'Model Class cannot be blank if table name does not end with asterisk.');
        }
    }

    /**
     * Validates the [[tableName]] attribute.
     */
    public function validateTableName()
    {
        if ( strpos($this->tableName, '*') !== false && substr_compare($this->tableName, '*', -1, 1) ) {
            $this->addError('tableName', 'Asterisk is only allowed as the last character.');

            return;
        }
        $tables = $this->getTableNames();
        if ( empty($tables) ) {
            $this->addError('tableName', "Table '{$this->tableName}' does not exist.");
        } else {
            foreach ( $tables as $table ) {
                $class = $this->generateClassName($table);
                if ( $this->isReservedKeyword($class) ) {
                    $this->addError('tableName', "Table '$table' will generate a class which is a reserved PHP keyword."
                    );
                    break;
                }
            }
        }
    }
}
