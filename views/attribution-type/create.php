<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\models\AttributionType */

$this->title = Yii::t('app', 'Create Attribution Type');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Attribution Types'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="attribution-type-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

    <?php echo \yii\helpers\VarDumper::dump($model->errors, 6, true) ?>
</div>