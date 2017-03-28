<?php

use yii\helpers\Inflector;
use yii\helpers\StringHelper;

/* @var $this yii\web\View */
/* @var $generator \backend\templates\generators\crudReport\Generator */

$urlParams = $generator->generateUrlParams();

echo "<?php\n";
?>

use backend\helpers\Html;
use backend\widgets\GridView;
use backend\components\Highcharts;

/* @var $this yii\web\View */
/* @var $searchModel <?= ltrim($generator->searchModelClass, '\\') ?> */
/* @var $data array */
/* @var $seriesData array */
/* @var $xAxis array */
/* @var $yAxisTitle string */

$this->title = $searchModel->getReportTitle();
$this->params['title'] = $this->title;
$this->params['breadcrumbs'][] = ['label' => Yii::t('terminal_group', 'Статистика'), 'url' => ['/statistics/default/index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="box box-primary <?= Inflector::camel2id(StringHelper::basename($generator->modelClass)) ?>-chart">
    <div class="box-body">

        <?= "<?php " . PHP_EOL ?>
            $form = $this->render('_form.php', [
                'model' => $searchModel,
                'countRows' => $countRows,
                'chart' => true,
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
                $widget_content = '<div class="kv-panel-before">' . $form . '</div>';
                if ($charts) {
                    $this->registerJs("Highcharts.setOptions(" . Highcharts::getGlobalOptions() . ");");
                }
                foreach ($charts as $chartKey => $chartTitle) {
                    $widget_content .= Highcharts::widget([
                        'clientOptions' => [
                            'title' => ['text' => $chartTitle],
                            'xAxis' => $searchModel->isDatetimeXAxis()
                                ? [
                                    'type' => 'datetime',
                                    'title' => [
                                        'text' => $searchModel->getAttributeLabel($searchModel->getPeriodKey()),
                                    ],
                                    'labels' => [
                                        'format' => $searchModel->getDateFormat(
                                            $searchModel->getPeriodKey(),
                                            'highcharts'
                                        ),
                                        'rotation' => -45
                                    ],
                                ]
                                : $xAxis,
                            'lang' => Highcharts::getLangOptions(false),
                            'global' => [
                                'useUTC' => false,
                            ],
                            'rangeSelector' => [
                                'enabled' => true
                            ],
                            'yAxis' => [
                                'allowDecimals' => true,
                                'title' => [
                                    'text' => $yAxisTitle
                                ],
                            ],
                            'series' => $seriesData[$chartKey],
                            'chart' => [
                                'type' => $searchModel->getDefaultChartType(),
                                'zoomType' => 'x',
                            ],
                        ]
                    ]);

                    $chartIds[] = Highcharts::$chartId;
                }

                if (isset($chartIds)) {
                    $chartIdsList = implode(',', $chartIds);

                    $widget_js_content = <<<JS
\$('#chartType').change(function () {
    var charts = [$chartIdsList];
    charts.forEach(function(item, i, arr) {
        var chartType = \$('#chartType').val();
        item.options.chart.type = chartType;
        var chart = \$('#' + item.options.chart.renderTo).highcharts();

        \$.each(\$('#' + item.options.chart.renderTo).highcharts().series, function (key, series) {
            var chartType = $('#chartType').val();
            series.update(
                {type: chartType},
                true
            );
        });
    });
});
JS;

                    $this->registerJs($widget_js_content);
                }
            } else {
                $widget_content = '<div class="kv-panel-before">' . $form . '</div>';
            }
        <?= "?>" ?>

        <?= "<?= " ?>$this->render('<?=$generator->viewPath?>/_tabs.php', [
            'widget_content' => $widget_content,
            'active_tab' => ['chart' => true],
        ]) <?= "?>" ?>
    </div>
</div>