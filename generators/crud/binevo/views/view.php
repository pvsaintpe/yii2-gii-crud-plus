<?php

use yii\helpers\Inflector;
use yii\helpers\StringHelper;

/* @var $this yii\web\View */
/* @var $generator backend\templates\generators\crud\Generator */

$urlParams = $generator->generateUrlParams();

echo "<?php\n";
?>

use backend\helpers\Html;
use backend\widgets\DetailView;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;

/* @var $this yii\web\View */
/* @var $model <?= ltrim($generator->modelClass, '\\') ?> */

$this->title = <?= $generator->generateI18N(Inflector::camel2words(StringHelper::basename($generator->modelClass)), true) ?> . ': ' . $model-><?= $generator->getNameAttribute() ?>;
$this->params['title'] = <?= $model_many_string = $generator->generateI18N(Inflector::pluralize(Inflector::camel2words(StringHelper::basename($generator->modelClass))), true) ?>;
$this->params['title_desc'] = <?= $generator->generateI18N('Просмотр') ?>;
$this->params['breadcrumbs'][] = ['label' => <?= $model_many_string ?>, 'url' => ['index']];
$this->params['breadcrumbs'][] = $model-><?= $generator->getNameAttribute() ?>;
?>
<div class="box box-primary <?= Inflector::camel2id(StringHelper::basename($generator->modelClass)) ?>-view">
    <div class="box-body" style="margin-top: 10px">
    <?= "<?php\n\n\t" ?>$updateButton = Html::updateButton($model);

    $createButton = Html::createButton();
    $indexButton = Html::indexButton(<?=$model_many_string?>);
    $attributes = [
        <?php
        $columns = [];
        if (($tableSchema = $generator->getTableSchema()) === false) {
            foreach ($generator->getColumnNames() as $name) {
                $columns[] = $name;
            }
        } else {
            foreach ($generator->getTableSchema()->columns as $column) {
                $columns[] = $column;
            }
        }

        $relations = $generator->getRelationsNs($generator->getTableSchema());

        $tableName = $generator->getTableSchema()->fullName;
        $relation_links = $generator->getLinks($tableName);

        $generator->generateTimestampAttributes($generator->getTableSchema());
        $generator->generateStatusAttributes($generator->getTableSchema());
        foreach ($columns as $column) {
            $name = $tableSchema ? $column->name : $column;
            if ($generator->existRelation($column)) {
                $relationName = $generator->getRelationByColumn($column);
                $relationModel = $generator->getRelationModel($column);

                $code = $generator->generateRelationCode($relationName, $relationModel, $column->name);
                ?>[
            'attribute' => '<?= $name ?>',
            'value' => function ($form, $widget) {
                $model = $widget->model;
                <?=$code?>

            },
            'format' => 'raw',
        ],<?php
                echo "\n\t\t";
            } elseif (in_array($name, $generator->getDatetimeAttributes())) { ?>'<?= $name ?>',<?php
                echo "\n\t\t";
            } elseif (in_array($name, $generator->imageAttributes)) { ?>[
            'attribute' => '<?= $name ?>',
            'value' => function ($form, $widget) {
                $model = $widget->model;
                return Html::tag('img', '', ['src' => $model-><?= $name ?>Url]);
            },
            'format' => 'raw',
        ],<?php
                echo "\n\t\t";
            } elseif (in_array($name, $generator->getStatusAttributes())) { ?>'<?= $name ?>',<?php
                echo "\n\t\t";
            } elseif ($tableSchema && $column->phpType === 'double') {
                $decimals = $column->scale && is_int($column->scale) ? $column->scale : 4;
                ?>[
            'attribute' => '<?= $name ?>',
            'format' => ['decimal', <?= $decimals ?>],
        ],<?php
                echo "\n\t\t";
            } else {
                if (preg_match('~(.*)_amount$~', $name, $match)) {
                    ?>[
            'attribute' => '<?= $name ?>',
            'format' => 'currency',
        ],<?php
                } else {
                    ?>'<?= $name ?>',<?php
                }

                echo "\n\t\t";
            }
        }
        echo "\n\t";
        ?>];

    $widget_content = "<p align=right>$updateButton $createButton $indexButton</p>";
        $widget_content .= DetailView::widget([
            'model' => $model,
            'attributes' => $attributes,
            'disableAttributes' => $disableAttributes,
        ]);
    ?>

    <?="<?php "?> echo $widget_content; ?>
    
    </div>
</div>