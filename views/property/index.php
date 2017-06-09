<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Properties');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="property-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a(Yii::t('app', 'Create Property'), ['create'], ['class' => 'btn btn-success']) ?>
        <?= Html::a(Yii::t('app', 'Sync with onOffice'), ['sync-onoffice'], ['class' => 'btn btn-danger', 'data-loading-text' => Yii::t('app', 'Synchronizing...')]) ?>
    </p>
<?php Pjax::begin(); ?>    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            'id',
            'reference',
            'entry_date:date',
            'property_type',
            'location',
            'n_bedrooms',
            ['attribute' => 'plot_area_dm2', 'value' => function($model) { return $model->plot_area_m2; }],
            ['attribute' => 'built_area_dm2', 'value' => function($model) { return $model->built_area_m2; }],
            'updated_at:datetime',
            ['class' => 'yii\grid\ActionColumn'],
        ]
    ]) ?>
<?php Pjax::end(); ?></div>
<?php
$confirmMsg = Yii::t('app', 'Syncronizing is a resource consuming task, please, use it carefully.\n\nAre you sure you want to sync now?');
$syncSuccess = Yii::t('app', 'The synchronization completed successfully.\n\nIf you still cannot see some contact, please make sure that the onOffice CSV file has been exported correctly from onOffice.');
$script = <<< JS
    $('.btn-danger').on('click', function() {
        if (!confirm('{$confirmMsg}')) return false;
        \$btn = $(this);
        \$btn.button('loading');
        $.ajax({
            url: \$btn.attr('href'),
            method: 'post',
            success: function(data) {
                $.pjax.reload('#p0', {push: false, replace: false, timeout: 6000});
            }, error: function(jqXHR, textStatus, errorThrown) {
                \$btn.button('reset');
                alert(jqXHR.responseText);
            }
        });
        return false;
    });
    $('#p0').on('pjax:success', function(xhr, options) {
        \$btn.button('reset');
        alert('{$syncSuccess}');
    });
JS;
$this->registerJs($script);
?>
