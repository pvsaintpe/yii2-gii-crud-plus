<?php

use yii\helpers\Inflector;
use yii\helpers\StringHelper;

/* @var $this yii\web\View */
/* @var $generator \backend\templates\generators\crudReport\Generator */

$urlParams = $generator->generateUrlParams();
$nameAttribute = $generator->getNameAttribute();

echo "<?php\n";
?>

use backend\helpers\Html;
use backend\widgets\GridView;
<?php
$tableSchema = $generator->getTableSchema();
foreach ($tableSchema->foreignKeys as $i => $key) {
?>
use common\models\<?=\yii\helpers\Inflector::camelize(array_shift($key), true)?>;
<?php
}
?>

/* @var $this yii\web\View */
<?= !empty($generator->searchModelClass) ? "/* @var \$searchModel " . ltrim($generator->searchModelClass, '\\') . " */\n" : '' ?>
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = $searchModel->getGridTitle();
$this->params['breadcrumbs'][] = ['label' => Yii::t('terminal_group', 'Статистика'), 'url' => ['/statistics/default/index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="box box-primary <?= Inflector::camel2id(StringHelper::basename($generator->modelClass)) ?>-index">
    <div class="box-body">
        <?= '<?php ' . PHP_EOL; ?>
            $form = $this->render('_form.php', [
                'model' => $searchModel,
                'chart' => false,
            ]);

            if (<?php
        $requiredPrimaryKeys = $generator->getRequiredPrimaryKeys();
        if (count($requiredPrimaryKeys)) {
            $conditions = [];
            foreach ($requiredPrimaryKeys as $key) {
                $conditions[] = "!empty(\$searchModel->$key)";
            }
            echo implode(' && ', $conditions);
        } else {
            echo 'true';
        }

        if (in_array('pk_workshift_id', $generator->getPrimaryKeys())) {
            echo " || !empty(\$searchModel->pk_workshift_id)";
        }
               ?>) {
                $widget_content = GridView::widget([
                    'dataProvider' => $dataProvider,
                    'disableColumns' => $searchModel->getDisableColumns(),
                    'columns' => $searchModel->getGridColumns(),
                    'toolbar' => $searchModel->getGridToolbar(),
                    'pjax' => false,
                    'panelBeforeTemplate' => '
                        ' . $form . '
                        <div class="pull-right">
                            <div class="btn-toolbar kv-grid-toolbar" role="toolbar" style="padding-top:30px;">{toolbar}</div>
                        </div>
                        <div class="clearfix"></div>',
                ]);

            } else {
                $widget_content = '<div class="kv-panel-before">' . $form . '</div>';
            }
        ?>

        <?= '<?= '; ?>$this->render('<?=$generator->viewPath?>/_tabs.php', [
            'widget_content' => $widget_content,
            'active_tab' => ['table' => true],
        ]) ?>
    </div>
</div>