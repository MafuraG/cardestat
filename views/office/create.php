<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\models\Office */

$this->title = Yii::t('app', 'Create Office');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Offices'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="office-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
