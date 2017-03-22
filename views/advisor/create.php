<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\models\Advisor */

$this->title = Yii::t('app', 'Create Advisor');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Advisors'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="advisor-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
