<?php
namespace Anggagewor\Ngmod\Generator\migration\components;

use yii\base\Model;
use yii\base\Object;

class Field extends Model
{
    public $name;
    public $type;
    public $params;
    public $isNull = false;
    public $defaultValue;
    public $isIndex = false;
    public $comment;
    protected $field;


    public function rules()
    {
        return [
            [['name', 'type'], 'required'],
            [['isNull', 'isIndex'], 'boolean'],
            [['name', 'type', 'params', 'defaultValue', 'comment'], 'string'],
        ];
    }

    public function build()
    {
        $this->buildNull();
        $this->buildDefaultValue();
        $this->buildComment();

        return "'{$this->name}' => '\$this->'" . implode('->', $this->field);
    }

    protected function buildNull()
    {
        $this->field['null'] = $this->isNull ? 'null()' : 'notNull()';
        return $this;
    }

    protected function buildDefaultValue()
    {
        if (!empty($this->defaultValue)) {
            $this->field['null'] = "defaultValue({$this->defaultValue})";
        }
        return $this;
    }

    protected function buildComment()
    {
        if (!empty($this->comment)) {
            $this->field['null'] = "comment({$this->comment})";
        }
        return $this;
    }
}
