<?php

use yii\helpers\Inflector;
use yii\helpers\StringHelper;

/* @var $this yii\web\View */
/* @var $generator \backend\templates\generators\crudReport\Generator */

/* @var $model \yii\db\ActiveRecord */
$model = new $generator->modelClass();
$safeAttributes = $model->safeAttributes();
if (empty($safeAttributes)) {
    $safeAttributes = $model->attributes();
}

echo "<?php\n";
?>

use kartik\daterange\DateRangePicker;
use backend\helpers\Html;
use kartik\form\ActiveForm;
use yii\widgets\ActiveField;
use common\models\UserType;
<?php
$relations = $generator->getRelationsNs($generator->getTableSchema());
if (sizeof($relations) > 0) {
    foreach ($relations as $relation) {
        ?>use common\models\<?=  ucfirst($relation)?>;<?php echo "\n";
    }
}
?>

/* @var $this yii\web\View */
/* @var $form yii\widgets\ActiveForm */
/* @var $model <?= ltrim($generator->modelClass, '\\') ?> <?= "*/" ?>

$form = ActiveForm::begin([
    'id' => $model->formName(),
    'enableClientValidation' => false,
    'enableAjaxValidation' => false,
    'validateOnChange' => false,
    'validateOnBlur' => false
]);
$form->method = 'POST';
?>
<?php
    if (in_array('pk_workshift_id', $generator->getPrimaryKeys())) {
        ?>
        <?= "<?php "?>
        if (Yii::$app->user->can('is_terminal')) {
        ?>
        <div class="pull-left" style="margin-right: 15px;">
            <label class="control-label" for="workshift-id"><?= "<?="; ?> $model->getAttributeLabel('pk_workshift_id')
                ?></label>
            <?= "<?php" ?>

            $field = new \kartik\widgets\Select2([
            'id' => 'workshift-id',
            'attribute' => 'pk_workshift_id',
            'name' => $model->formName().'[pk_workshift_id]',
            'data' => \common\models\Workshift::findForFilter($model->getFilter('pk_workshift_id')),
            'value' => $model->pk_workshift_id,
            'options' => [
            'placeholder' => $model->getAttributeLabel('pk_workshift_id'),
            'multiple' => false,
            ],
            'showToggleAll' => true,
            'pluginOptions' => [
            'allowClear' => true,
            'width' => '135px'
            ],
            ]);

            $field->renderWidget();
            ?>
        </div>
        <?= "<?php " ?>

        }
        ?>
        <?php
    }
    $column = 0;
    $suffix = "";
    $isRelations = false;
    $related = [];
    foreach ($generator->getColumnNames() as $attribute) {
        if (in_array($attribute, $safeAttributes)) {
            if (in_array($attribute, \yii\helpers\ArrayHelper::merge($generator->getPrimaryKeys(), $generator->getOtherKeys()))) {
                if ($generator->isIdModelVirtual($generator->getReplaceString($attribute), $generator->tableName) !== false) {
                    $isRelations = true;
                    $related[$attribute] = $generator->isIdModelVirtual($generator->getReplaceString($attribute), $generator->tableName);
                    continue;
                }
                echo "<div class=\"pull-left\" $suffix>" . PHP_EOL;
                echo "<?= " . $generator->generateActiveField($attribute) . " ?>\n\n";
                echo "</div>" . PHP_EOL;
            }
            $column++;
        }
    }

    if ($isRelations) {
        foreach ($related as $attributeId => $relation) {
            if (in_array($attributeId, ['pk_workshift_id'])) {
                continue;
            }
            $suffix = " style=\"margin-left: 15px;\"";
            if (in_array($attributeId, ['pk_terminal_group_id', 'pk_terminal_id'])) {
                $suffix = " style=\"margin-left: 15px; "
                    . "<?= (Yii::\$app->user->can('cashier') || Yii::\$app->user->can('admin')) ?"
                    . "'display: none;' : '' ?>\"
                ";
            }
            $column++;
            $isUser = false;
            $name = 'name';
            $relationValue = 'name';
            if (in_array(strtolower($relation['table']), array('user'))) {
                $isUser = true;
                $name = 'username';
                $relationValue = 'getName()';
            }
            ?>
<div class="pull-left" <?=$suffix?>>
    <?php
        if (!in_array($attributeId, ['pk_terminal_group_id', 'pk_terminal_id'])) {
            ?>
            <?= '<?=' ?> $form->field($model, '<?= $attributeId ?>')->widget(\kartik\widgets\Select2::classname(), [
            'data' => \<?= $relation['class'] ?>::findForFilter($model->getFilter('<?= $attributeId ?>')),
            'options' => [
            'placeholder' => <?= $generator->generateString("{$relation['label']}") ?>,
            'multiple' => <?php if (in_array($attributeId, ['pk_currency_id', 'currency_id', 'tk_currency)id'])) echo 'false'; else echo 'true'; ?>,
            ],
            'pluginOptions' => [
            'allowClear' => true,
            'width' => '200px'
            ],
            ])->label(<?= $generator->generateString($relation['label']) ?>); ?>
            <?php
        } else {
            ?>
            <?= '<?=' ?>
            $form->field($model, '<?= $attributeId ?>')->widget(\kartik\widgets\DepDrop::classname(), [
                'type' => \kartik\widgets\DepDrop::TYPE_SELECT2,
                'data' => \<?= $relation['class'] ?>::findForFilter($model->getFilter('<?= $attributeId ?>')),
                'options' => [
                    'multiple' => true,
                    'language' => Yii::$app->language,
                ],
                'select2Options' => [
                    'pluginOptions' => [
                        'allowClear' => true,
                        'width' => '200px',
                        'language' => Yii::$app->language,
                    ]
                ],
                'pluginOptions' => [
                    'language' => Yii::$app->language,
                    'placeholder' => false,
                    <?php
                        if ($attributeId == 'pk_terminal_group_id') {
                            ?>'depends' => ['workshift-id'],
                            'url' => \yii\helpers\Url::to(['/statistics/default/workshift-halls'])
                            <?php
                        } else {
                            ?>'depends' => [strtolower($model->formName()) . '-pk_terminal_group_id', 'workshift-id'],
                            'url' => \yii\helpers\Url::to(['/statistics/default/halls-terminals'])
                            <?php
                        }
                    ?>
                ]
            ])->label(<?= $generator->generateString($relation['label']) ?>); ?>
            <?php
        }
    ?>
</div>
<?php
        }
    }
?>

<div class="pull-left"  style="margin-left: 15px;">
    <label class="control-label" for="strategy"><?="<?="; ?> $model->getAttributeLabel('strategy') ?></label>
    <?= '<?php ' ?>
    $field = new \kartik\widgets\Select2([
    'id' => 'strategy',
    'attribute' => 'strategy',
    'name' => $model->formName().'[strategy]',
    'data' => \backend\components\ReportController::getGroupTypeList($model->getGroupFormat($model->getPeriodKey())),
    'value' => $model->strategy,
    'options' => [
    'placeholder' => $model->getAttributeLabel('strategy'),
    'multiple' => false,
    ],
    'showToggleAll' => true,
    'pluginOptions' => [
    'allowClear' => true,
    'width' => '140px'
    ],
    ]);

    $field->renderWidget();
    ?>
</div>

<div class="pull-left" style="margin-left: 15px;">
    <div class="drp-container form-group" style="margin-top:5px;">
        <label class="control-label"></label>
        <div class="input-group">
            <?= '<?= ' ?>Html::submitButton(
                Html::tag('span', '', ['class' => 'glyphicon glyphicon-floppy-disk']) . ' ' .
                Yii::t('reports', 'Построить отчет'),
                ['class' => 'applyBtn btn btn-success']
            )?>
        </div>
    </div>
</div>

<?= '<?php ' ?>
$found = $chart && $countRows > 0;

if ($found) {
    ?>
    <div class="pull-right"  style="margin-left: 15px;">
        <label class="control-label" for="chartType"><?="<?="; ?> Yii::t('chart', 'Тип графика') ?></label>
        <?= '<?php ' ?>
        $field = new \kartik\widgets\Select2([
            'id' => 'chartType',
            'attribute' => 'chartType',
            'name' => 'chartType',
            'data' => \backend\components\ReportController::getChartTypeList(),
            'value' => $model->chartType,
            'options' => [
                'placeholder' => Yii::t('layout', 'Тип графика'),
                'multiple' => false,
            ],
            'showToggleAll' => true,
            'pluginOptions' => [
                'allowClear' => true,
                'width' => '150px'
            ],
        ]);

        $field->renderWidget();
        ?>
    </div>
    <div class="pull-left" style="margin-left: 15px; display: none;">
        <div class="drp-container form-group" style="margin-top:5px;">
            <label class="control-label"></label>
            <div class="input-group">
                <?= '<?= ' ?> Html::button(
                    Html::tag('span', '', ['class' => 'glyphicon']) . ' ' .
                    Yii::t('reports', 'Обновить график'),
                    [
                        'class' => 'btn btn-primary',
                        'name' => 'changeType',
                        'id' => 'changeType',
                    ]
                )?>
            </div>
        </div>
    </div>
    <?= '<?php ' ?>
}

<?php
    if (in_array('pk_workshift_id', $generator->getPrimaryKeys())) {
    ?>if ($model->hasProperty('pk_workshift_id')) {
    $this->registerJs(<<<JS
        $('#workshift-id').change(function() {
            if ($(this).val()) {
                $('.drp-container input[type="text"]').eq(0).val('').trigger('change');
            }
        });

        $('.drp-container input[type="text"]').eq(0).change(function() {
            if ($(this).val()) {
                $('#workshift-id').parent().find('.select2-selection__clear').trigger('mousedown');
            }
        });
JS
);
}<?php
    }
?>
?>
<div class="clearfix"></div>
<?= '<?php ' ?>ActiveForm::end(); ?>