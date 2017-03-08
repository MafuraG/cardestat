<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Invoices');
$this->params['breadcrumbs'][] = $this->title;
$dataProvider->sort = false;
?>
<?php Pjax::begin() ?>
<div class="invoice-index">

  <h1 class="page-header"><?= Html::encode($this->title) ?> <small><?= Yii::t('app', 'Transaction #{transaction_id}', [
      'transaction_id' => $model->transaction_id
  ]) ?></small></h1>

  <?= GridView::widget([
      'dataProvider' => $dataProvider,
      'summary' => false,
      'columns' => [
          'id',
          'code',
          'issued_at:date',
          ['attribute' => 'amount_euc', 'format' => ['currency', 'EUR']],
          'recipient_category',
          ['class' => 'yii\grid\ActionColumn', 'buttons' => [
              'view' => function ($url, $model) {},
              'update' => function ($url, $model) {},
          ]]
      ]
  ]); ?>

  <?= $this->render('_form', [
      'model' => $model
  ]) ?>
</div>

<?php Pjax::end() ?>
