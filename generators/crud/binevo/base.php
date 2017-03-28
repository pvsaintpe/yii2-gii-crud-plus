<?php
/**
 * This is the template for generating CRUD search class of the specified model.
 */

use yii\helpers\StringHelper;
use yii\db\Schema;

/* @var $this yii\web\View */
/* @var $generator backend\templates\generators\crud\Generator */

$modelClass = StringHelper::basename($generator->modelClass);
$searchModelClass = StringHelper::basename($generator->searchModelClass);
if ($modelClass === $searchModelClass) {
    $modelAlias = $modelClass . 'Model';
}
$rules = $generator->generateSearchRules();
$labels = $generator->generateSearchLabels();
$searchAttributes = $generator->getSearchAttributes();
$searchConditions = $generator->generateSearchConditions();

echo "<?php\n";
?>

namespace <?= StringHelper::dirname(ltrim($generator->searchModelClass, '\\')) ?>;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use <?= ltrim($generator->modelClass, '\\') . (isset($modelAlias) ? " as $modelAlias" : "") ?>;
use yii\helpers\ArrayHelper;

/**
 * <?= $searchModelClass ?> represents the model behind the search form about `<?= $generator->modelClass ?>`.
 */
class <?= $searchModelClass ?> extends <?= isset($modelAlias) ? $modelAlias : $modelClass ?>

{
    /**
     * @return string
     */
    public function getGridTitle()
    {
        return Yii::t('export', <?= $generator->generateI18N(
            \yii\helpers\Inflector::pluralize(
                \yii\helpers\Inflector::camel2words(
                    StringHelper::basename($generator->modelClass)
                )
            ),
            true
        )?>);
    }

    /**
     * @return []
     */
    public function getGridColumns()
    {
        return [
            'serial' => [
                'class' => 'backend\components\grid\SerialColumn'
            ],<?php

            $tableSchema = $generator->getTableSchema();

            $columns = [];
            $to_end = [];
            $generator->generateTimestampAttributes($generator->getTableSchema());
            $generator->generateStatusAttributes($generator->getTableSchema());
            $end_sort = array_merge($generator->getDatetimeAttributes(), $generator->getStatusAttributes());
            $count = 0;
            if (($tableSchema = $generator->getTableSchema()) === false) {
                foreach ($generator->getColumnNames() as $name) {
                    if (in_array($name, $end_sort)) {
                        $to_end[$name] = $name;
                    } else {
                        $columns[] = $name;
                    }
                }
            } else {
                $__relations = array();
                foreach ($tableSchema->foreignKeys as $i => $key) {
                    $__relationTable = array_shift($key);
                    $__key = array_keys($key);
                    $__relationKey = $__key[0];
                    $__relations[$__relationKey] = lcfirst(\yii\helpers\Inflector::camelize($__relationTable));
                }

                foreach ($tableSchema->columns as $column) {
                    if (in_array($column->name, $end_sort)) {
                        $to_end[$column->name] = $column;
                    } else {
                        $columns[] = $column;
                    }
                }
            }
            $count = count($columns) + count($to_end);
            foreach ($columns as $index => $column) {
                echo $generator->generateColumn($tableSchema, $column, $index * 2 >= $count);
            }
            if (!empty($to_end)) {
                foreach ($end_sort as $name) {
                    if (isset($to_end[$name])) {
                        echo $generator->generateColumn($tableSchema, $to_end[$name], true);
                    }
                }
            }
            ?>

            'action' => [
                'class' => 'backend\components\grid\ActionColumn',
                'permissionPrefix' => $this->getPermissionPrefix(),
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            <?= implode(",\n            ", $rules) ?>,
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params = null)
    {
        $query = <?= isset($modelAlias) ? $modelAlias : $modelClass ?>::find();

        <?php
            $model = new $generator->modelClass();
            $attributes = array_keys($model->getAttributes());
            if (in_array('app_id', $attributes)) {
                ?>$this->query->initAppFilters();<?php
            }

            if (in_array('app_group_id', $attributes)) {
                ?>$this->query->initAppGroupFilters();<?php
            }
        ?>

        if (!empty($params)) {
            $this->load($params);
        }
        
        <?= implode("\n        ", $searchConditions) ?>

        <?php
        $__classModel = ltrim($generator->modelClass, '\\') . (isset($modelAlias) ? " as $modelAlias" : "");
        $__className = new $__classModel();
        $__relations = array();

        $keys = $__className->getTableSchema()->foreignKeys;

        foreach ($keys as $i => $key) {
            $__relationTable = array_shift($key);
            $__key = array_keys($key);
            $__relationKey = $__key[0];
            $__relations[$__relationKey] = lcfirst(\yii\helpers\Inflector::camelize($__relationTable));
        }
        ?>// join Relations to query
        <?php
        echo 'return $this->query';
        if (sizeof($__relations) > 0) {
            $i = 0;
            foreach ($__relations as $key => $item) {
                if ($i>0) {
                    echo "
                             ";
                }
                $alias = "t_{$i}";
                echo "->innerJoinWith('{$item} {$alias}')";
                $i++;
            }
        }

        echo ";\n";
        ?>
        
        return parent::getDataProvider();
    }
}