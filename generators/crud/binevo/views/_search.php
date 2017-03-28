<?php

use yii\helpers\Inflector;
use yii\helpers\StringHelper;

/* @var $this yii\web\View */
/* @var $generator backend\templates\generators\crud\Generator */

echo "<?php\n";

$__relations = array();
foreach ($generator->getTableSchema()->foreignKeys as $i => $key) {
    $__relationTable = array_shift($key);
    $__key = array_keys($key);
    $__relationKey = $__key[0];
    $__relations[$__relationKey] = \yii\helpers\Inflector::camelize($__relationTable);
}

$__columns = $generator->getTableSchema()->columns;
?>

use backend\helpers\Html;
use yii\widgets\ActiveForm;
<?php
foreach ($__relations as $relation) {
?>
use common\models\<?=$relation?>;
<?php
}
?>

/* @var $this yii\web\View */
/* @var $model <?= ltrim($generator->searchModelClass, '\\') ?> */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="<?= Inflector::camel2id(StringHelper::basename($generator->modelClass)) ?>-search">

    <?= "<?php " ?>$form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

<?php
$count = 0;
foreach ($generator->getColumnNames() as $attribute) {
    if ($__columns[$attribute]->dbType === 'tinyint(1) unsigned') {
        echo  "    <?= " . $generator->generateActiveBooleanField($attribute) . " ?>\n\n";
    } else {
        if (array_key_exists($attribute, $__relations)) {
            $isUser = false;
            if (isset($__relations[$attribute]) && in_array($__relations[$attribute], array('user'))) {
                $isUser = true;
            }
            echo  "    <?= " . $generator->generateActiveSelectField($attribute, $isUser, $__relations[$attribute]) . " ?>\n\n";
        } else {
            echo  "    <?= " . $generator->generateActiveField($attribute) . " ?>\n\n";
        }
    }
}
?>
    <div class="form-group">
        <?= "<?= " ?>Html::submitButton(<?= $generator->generateI18N('Искать') ?>, ['class' => 'btn btn-primary']) ?>
        <?= "<?= " ?>Html::resetButton(<?= $generator->generateI18N('Сброс') ?>, ['class' => 'btn btn-default']) ?>
    </div>

    <?= "<?php " ?>ActiveForm::end(); ?>

</div>
