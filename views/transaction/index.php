<?php
/* @var $this yii\web\View */
use yii\widgets\ListView;
use yii\bootstrap\BootstrapAsset;
BootstrapAsset::register($this);
?>
<h1 class="page-header"><?= Yii::t('app', 'Transactions')?></h1>
<?= ListView::widget([
    'dataProvider' => $dataProvider,
    'itemView' => '_list_item.twig',
    'itemOptions' => function ($model, $key, $index, $widget) {
            return ['class' => 'transaction' . ($index == 0 ? ' first-transaction' : '')];
        }
]); ?>
<?php
$script = <<< JS
  $('[data-toggle="tooltip"]').tooltip()
JS;
$this->registerJs($script);
?>
