<?php
/**
 * This is the template for generating CRUD search class of the specified model.
 */

use yii\helpers\StringHelper;
use yii\db\Schema;
use yii\helpers\Inflector;

/* @var $this yii\web\View */
/* @var $generator \backend\templates\generators\crudReport\Generator */

$modelClass = StringHelper::basename($generator->modelClass);
$searchModelClass = StringHelper::basename($generator->searchModelClass);
if ($modelClass === $searchModelClass) {
    $modelAlias = $modelClass . 'Model';
}
$rules = $generator->generateSearchRules();
$labels = $generator->generateSearchLabels();
$searchAttributes = $generator->getSearchAttributes();
$searchConditions = $generator->generateSearchConditions();

$columnNames = $generator->getTableSchema()->columnNames;

echo "<?php\n";
?>

namespace <?= StringHelper::dirname(ltrim($generator->searchModelClass, '\\')) ?>;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use <?= ltrim($generator->modelClass, '\\') . (isset($modelAlias) ? " as $modelAlias" : "") ?>;
use yii\helpers\ArrayHelper;
use yii\helpers\Inflector;

/**
 * <?= $searchModelClass ?> represents the model behind the search form about `<?= $generator->modelClass ?>`.
 */
class <?= $searchModelClass ?> extends <?= isset($modelAlias) ? $modelAlias : $modelClass ?> 
{
    public $skipEmptyValues = true;

    public $hallGroup = true;

    public $strategy;

    /**
     * @return string
     */
    public function getGridTitle()
    {
        return parent::modelTitle();
    }

    /**
     * @return []
     */
    public function getGridColumns()
    {
        $searchModel = $this;
        return [<?php
        $tableSchema = $generator->getTableSchema();

        $generator->generateTimestampAttributes($tableSchema);
        $generator->generateStatusAttributes($tableSchema);

        $allColumns = $generator->getGridViewColumns();

        $disableColumns = [];
        foreach ($tableSchema->columns as $column) {
            if (in_array($column->name, $allColumns)) {
                $columns[] = $column;
                $disableColumns[] = $column->name;
            }
        }

        foreach ($columns as $i => $column) {
            echo $generator->generateColumn($tableSchema, $column);
        }

        $calcColumns = $generator->getCalculateColumns();
        foreach ($calcColumns as $column) {
            if (!in_array($column, $disableColumns)) {
                echo "
            '$column' => [
                'class' => 'backend\\components\\grid\\DataColumn',
                'attribute' => '$column',
                'pageSummary' => true,
                'format' => 'currency',
            ],";
            }
        }

        ?>

        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return array_merge(
            parent::attributeLabels(),
            [
                'strategy' => Yii::t('charts', 'Группировка'),
            ]
        );
    }

    /**
     * Get group politicies for calculate columns
     * @return []
     */
    public function getCalculateColumns()
    {
        return [<?php
            foreach ($generator->getCalculateColumns() as $columnName) {
                echo "\n            ";
                if ($generator->getDividerColumns($columnName)) {
                    $columnOperation = 'division';
                }
                if ($generator->getAverageColumns($columnName)) {
                    $columnOperation = 'averaging';
                }
                if ($generator->getSummaryColumns($columnName)) {
                    $columnOperation = 'summation';
                }
                if ($generator->getDistinctColumns($columnName)) {
                    $columnOperation = 'distinct';
                }
                ?>'<?=$columnName?>' => [<?php
                    echo "\n                ";
                    ?>'operation' => '<?=$columnOperation?>',<?php
                    echo "\n                ";
                    if ($columnOperation == 'division') {
                        ?>'dividend' => '<?= $generator->getDividendColumns($columnName)?>',<?php
                        echo "\n                ";
                        ?>'divider' => '<?= $generator->getDividerColumns($columnName)?>',<?php
                    }
                    if ($columnOperation == 'averaging') {
                        ?>'column' => '<?= $generator->getAverageColumns($columnName)?>',<?php
                    }
                    if ($columnOperation == 'summation') {
                        ?>'column' => '<?= $generator->getSummaryColumns($columnName)?>',<?php
                    }
                    if ($columnOperation == 'distinct') {
                        ?>'column' => '<?= $generator->getDistinctColumns($columnName)?>',<?php
                    }
                    echo "\n            ";
                ?>],<?
            }

            echo "\n        ";
        ?>];
    }

    /**
     * Get format for grouping date/datetime attributes
     * @param null $key
     * @param null $strategy
     * @return array|mixed
     */
    public function getGroupFormat($key = null, $strategy = null)
    {
        $attributes = [<?php
            $dateColumns = [];
            foreach($generator->getTableSchema()->columns as $column) {
                foreach ($generator->getDatetimeAttributes() as $datetimeAttribute) {
                    if ($column->name == $datetimeAttribute) {
                        switch ($column->type) {
                            case 'datetime':
                            case 'timestamp':
                                $dateColumns[$column->name] = 'datetime';
                                break;
                            case 'date':
                                $dateColumns[$column->name] = 'date';
                                break;
                        }
                    }
                }
            }

            foreach ($dateColumns as $column => $type) {
                echo "\n            ";
                ?>'<?=$column?>' => [<?
                if ($type == 'datetime') {
                    echo "\n                ";
                    ?>'hour' => '%Y-%m-%d %H:00:00',<?php
                }
                echo "\n                ";
                ?>'day' => '%Y-%m-%d 00:00:00',<?php
                echo "\n                ";
                ?>'month' => '%Y-%m-01 00:00:00',<?php
                echo "\n                ";
                ?>'year' => '%Y-01-01 00:00:00',<?php
                echo "\n            ";
                ?>],<?php
            }
            echo "\n        ";
    ?>];

        if ($key && !$strategy) {
            if (isset($attributes[$key])) {
                return $attributes[$key];
            }

            return null;
        }

        if ($key && $strategy) {
            if (isset($attributes[$key][$strategy])) {
                return $attributes[$key][$strategy];
            }

            return null;
        }

        return $attributes;
    }

    <?php
        $formats = [];
        foreach($generator->getTableSchema()->columns as $column) {
            foreach ($generator->getDatetimeAttributes() as $datetimeAttribute) {
                if ($column->name == $datetimeAttribute) {
                    switch ($column->type) {
                        case 'datetime':
                        case 'timestamp':
                            $formats[$column->name] = [
                                'php' => [
                                    'hour' => "php:d M Y H:i",
                                    'day' => "php:d M Y",
                                    'month' => "php:m/Y",
                                    'year' => "php:Y",
                                ],
                                'highcharts' => [
                                    'hour' => '{value:%d.%m.%y %H:00}',
                                    'day' => '{value:%d %b %Y}',
                                    'month' => "{value:%b'%y}",
                                    'year' => '{value:%Y}',
                                ]
                            ];
                            break;
                        case 'date':
                            $formats[$column->name] = [
                                'php' => [
                                    'day' => "php:d M Y",
                                    'month' => "php:m/Y",
                                    'year' => "php:Y",
                                ],
                                'highcharts' => [
                                    'day' => '{value:%d %b %Y}',
                                    'month' => "{value:%b'%y}",
                                    'year' => '{value:%Y}',
                                ],
                            ];
                            break;
                    }
                }
            }
        }
    ?>/**
     * Get dateformat for date/datetime attributes
     * @param string $key
     * @param string $source
     * @return string
     */
    public function getDateFormat($key, $source)
    {
        $attributes = [<?php
            foreach ($formats as $column => $dateFormat) {
                echo "\n            ";
                ?>'<?=$column?>' => [<?
                foreach ($dateFormat as $source => $format) {
                    echo "\n                ";
                    ?>'<?= $source ?>' => [<?php
                        foreach ($format as $type => $f) {
                            echo "\n                    ";
                            ?>'<?=$type?>' => "<?=$f?>",<?php
                        }
                        echo "\n                ";
                    ?>],<?php
                }
                echo "\n            ";
                ?>],<?php

            }

            echo "\n        ";
        ?>];

        return $attributes[$key][$source][$this->strategy];
    }

    /**
     * Get format for calculate columns
     * @return []
     */
    public function getOtherFormat()
    {
        return [<?php
            foreach ($generator->getCalculateColumns() as $column) {
                echo "\n            ";
                ?>'<?=$column?>' => [<?
                    $col = $generator->getColumnTypes($column);
                    switch($col['type']) {
                        case 'int':
                            ?>'int'<?php
                            break;
                        case 'float':
                            ?>'float', <?=$col['scale']?><?php
                            break;
                    }
                    
                ?>],<?php 
            }
            echo "\n        ";
        ?>];
    }

    /**
     * Get keys without primary keys
     * @return []
     */
    public function getOtherKeys()
    {
        return [<?php
            foreach ($generator->getCalculateColumns() as $column) {
                echo "\n            ";
                ?>'<?=$column?>',<?  
            }
            echo "\n        ";
        ?>];
    }

    /**
     * Get group columns for aggregate values
     * @return []
     */
    public function getGroupByColumns()
    {
        if (in_array('pk_terminal_group_id', array_keys($this->getPrimaryKey()))) {
            if ($this->pk_terminal_group_id) {
                if (!Yii::$app->user->can('cashier')) { $this->hallGroup = false; }
            }
        }

        if ($this->hallGroup) {
            return [
                <?php
                $aggregateColumns = [];
                foreach ($generator->getPrimaryKeys() as $key) {
                    if (strpos($key, 'terminal_id') !== false) {
                        $aggregateColumns[] = $key;
                    }
                    if (strpos($key, 'payment_system_id') !== false) {
                        $aggregateColumns[] = $key;
                    }
                    if (strpos($key, 'terminal_group_id') !== false) {
                        $aggregateColumns[] = $key;
                    }
                    if (strpos($key, 'workshift_id') !== false) {
                        $aggregateColumns[] = $key;
                    }
                    if (strpos($key, 'currency_id') !== false) {
                        $aggregateColumns[] = $key;
                    }
                }

                if (count($aggregateColumns) > 0) {
                    ?>'<?= implode("',\n                '", $aggregateColumns) ?>'<?php
                }
                echo "\n";
                ?>
            ];
        } else {
            return [
                <?php
                $aggregateColumns = [];
                foreach ($generator->getPrimaryKeys() as $key) {
                    if (strpos($key, 'terminal_id') !== false) {
                        $aggregateColumns[] = $key;
                    }
                    if (strpos($key, 'payment_system_id') !== false) {
                        $aggregateColumns[] = $key;
                    }
                    if (strpos($key, 'terminal_group_id') !== false) {
                        $aggregateColumns[] = $key;
                    }
                    if (strpos($key, 'workshift_id') !== false) {
                        $aggregateColumns[] = $key;
                    }
                    if (strpos($key, 'currency_id') !== false) {
                        $aggregateColumns[] = $key;
                    }
                }

                if (count($aggregateColumns) > 0) {
                    ?>'<?= implode("',\n                '", $aggregateColumns) ?>'<?php
                }
                echo "\n";
                ?>
            ];
        }
    }

    /**
     * Get column name contain period
     * @return string
     */
    public function getPeriodKey()
    {
        return '<?php
            $periodKey = array_intersect_key(
                array_flip($generator->getPrimaryKeys()), 
                array_flip($generator->getDatetimeAttributes())
            );

            echo array_flip($periodKey)[0];
        ?>';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            <?= implode(",\n            ", $rules) ?>,
            [['strategy', 'hallGroup'], 'safe'],
        ];
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
        if (!empty($params)) {
            $this->load($params);
        }

        $periodKey = $this->getPeriodKey();

        static::initDefaultValues();

        if (!$this->strategy) {
            if (!empty($this->{$periodKey})) {
                $date = explode(' - ', $this->{$periodKey});

                $start = date_create($date[0])->getTimestamp();
                $end = date_create($date[1])->getTimestamp();

                $periodType = ($end - $start) / (24 * 3600);

                switch (true) {
                    case ($periodType < 2):
                        $this->strategy = 'hour';
                    break;
                    case ($periodType <= 31):
                        $this->strategy = 'day';
                    break;
                    default:
                        $this->strategy = 'month';
                }
            } else {
                $this->strategy = 'day';
            }
        }

        $this->query = <?= isset($modelAlias) ? $modelAlias : $modelClass ?>::find();

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
            echo '$this->query';
            if (sizeof($__relations) > 0) {
                $i = 0;
                foreach ($__relations as $key => $item) {
                    if ($i>0) {
                        echo "
             ";
                    }
                    $alias = "t_{$i}";
                    echo "->joinWith('{$item} {$alias}')";
                    $i++;
                }
            }

            echo ";\n";
        ?>

        <?php

            $model = new $generator->modelClass();
            $attributes = array_keys($model->getAttributes());
            if (in_array('pk_app_id', $attributes)) {
                ?>$this->query->initAppFilters();<?php
            }

            if (in_array('pk_app_group_id', $attributes)) {
                ?>$this->query->initAppGroupFilters();<?php
            }
        ?>
        
        <?= implode("\n        ", $searchConditions) ?>

        if (Yii::$app->user->can('is_terminal')) {
            $attribute = static::getPeriodKey();

            if (!$this->periodChanged) {
                $this->{$attribute} = null;
            }
        }

        return parent::getDataProvider();
    }

    /**
     * @return array
     */
    protected function getSort()
    {
        return \yii\helpers\ArrayHelper::merge(
            parent::getSort(),
            [
                'defaultOrder' => [<?php
                foreach ($generator->getDatetimeAttributes() as $attribute) {
                    echo "\n\t\t\t\t\t";
                    ?>'<?= $attribute?>' => SORT_DESC,<?php
                    break;
                }
                echo "\n\t\t\t\t";
                ?>],
            ]
        );
    }
}