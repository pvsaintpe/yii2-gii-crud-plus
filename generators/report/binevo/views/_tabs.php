<?php

use yii\helpers\Inflector;
use yii\helpers\StringHelper;

/* @var $this yii\web\View */
/* @var $generator \backend\templates\generators\crudReport\Generator */

echo "<?php\n";
?>
    use backend\widgets\Tabs;

    if (!isset($active_tab)) {
        $active_tab['table'] = true;
    }

    $tabs = [
        'table' => [
            'label' => Yii::t('reports','Табличная форма'),
            'url' => \yii\helpers\ArrayHelper::merge(['<?=$generator->generateUrlPath()?>/index'], Yii::$app->request->get()),
        ],
        'chart' => [
            'label' => Yii::t('reports','Графическая форма'),
            'url' => \yii\helpers\ArrayHelper::merge(['<?=$generator->generateUrlPath()?>/chart'], Yii::$app->request->get()),
        ],
    ];
?>

<?= '<?= ' ?> Tabs::initWidgetTabs($tabs, $active_tab, $widget_content);