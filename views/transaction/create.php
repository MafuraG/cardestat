<?php

use yii\helpers\Html;
use yii\widgets\Pjax;


/* @var $this yii\web\View */
/* @var $model app\models\TransactionListItem */

$this->title = Yii::t('app', 'Create Transaction');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Transactions'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="transaction-form">

  <h1><?= Html::encode($this->title) ?></h1>

  <?php Pjax::begin(['id' => 'p1']) ?>
    <?= $this->render('_simple_form', [
        'model' => $model,
    ]) ?>
  <?php Pjax::end() ?>

</div>
