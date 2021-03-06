<?php

/* @var $this \yii\web\View */
/* @var $content string */

use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\widgets\Breadcrumbs;
use app\assets\AppAsset;

AppAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="shortcut icon" href="<?= Url::to('@web/favicon.ico') ?>" type="image/x-icon">
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
</head>
<body>
<?php $this->beginBody() ?>

<div class="wrap">
    <?php
    NavBar::begin([
        'brandLabel' => 'Cárdenas::CardeStat',
        'brandUrl' => Yii::$app->homeUrl,
        'options' => [
            'class' => 'navbar-inverse navbar-fixed-top',
        ],
        'innerContainerOptions' => [
            'class' => 'container-fluid',
        ]
    ]);
    echo Nav::widget([
        'options' => ['class' => 'navbar-nav'],
        'encodeLabels' => false, 
        'items' => [
            ['label' => \Yii::t('app', 'Home'), 'url' => ['/'], 'active' => false],
            ['label' => \Yii::t('app', 'Transactions'), 'url' => ['/transaction/index']],
            ['label' => \Yii::t('app', 'Commissions'), 'url' => ['/transaction/commissions'], 'visible' => \Yii::$app->user->can('accounting')],
            ['label' => \Yii::t('app', 'Misc'), 'items' => [
                ['label' => \Yii::t('app', 'Contacts'), 'url' => ['/contact/index']],
                ['label' => \Yii::t('app', 'Properties'), 'url' => ['/property/index']],
                ['label' => \Yii::t('app', 'Advisors'), 'url' => ['/advisor/index'], 'visible' => \Yii::$app->user->can('admin')],
                ['label' => \Yii::t('app', 'Offices'), 'url' => ['/office/index'], 'visible' => \Yii::$app->user->can('admin')],
                ['label' => \Yii::t('app', 'Partners'), 'url' => ['/partner/index'], 'visible' => \Yii::$app->user->can('admin')],
                ['label' => \Yii::t('app', 'Attribution types'), 'url' => ['/attribution-type/index'], 'visible' => \Yii::$app->user->can('admin')],
                ['label' => \Yii::t('app', 'Users'), 'url' => ['/user/index'], 'visible' => \Yii::$app->user->can('admin')]
            ]],
            ['label' => \Yii::t('app', 'Charts'), 'active' => Yii::$app->controller->id == 'chart', 'visible' => Yii::$app->user->can('accounting'), 'items' => [
                ['label' => Yii::t('app', '<em><strong>All charts on option date by default</strong></em>'), 'options' => ['class' => 'text-center dropdown-header']],
                ['label' => Yii::t('app', 'Trading volume'), 'options' => ['class' => 'dropdown-header']],
                ['label' => \Yii::t('app', 'Volume vs. Revenues'), 'url' => ['/chart/volume']],
                ['label' => \Yii::t('app', 'Accumulated Volume vs. Revenues'), 'url' => ['/chart/accu-volume']],
                ['label' => \Yii::t('app', 'Avg. Volume vs. Avg. Revenue'), 'url' => ['/chart/avg-volume']],
                ['label' => Yii::t('app', 'Attributions'), 'options' => ['class' => 'dropdown-header']],
                ['label' => \Yii::t('app', 'Attribution by advisor (<em>option date</em>)'), 'url' => ['/chart/attribution-by-advisor-on-option-date']],
                ['label' => \Yii::t('app', 'Attribution by advisor (<em>invoice date</em>)'), 'url' => ['/chart/attribution-by-advisor-on-invoice-date']],
                ['label' => \Yii::t('app', 'Attribution by office (<em>option date</em>)'), 'url' => ['/chart/attribution-by-office-on-option-date']],
                ['label' => \Yii::t('app', 'Attribution by office (<em>invoice date</em>)'), 'url' => ['/chart/attribution-by-office-on-invoice-date']],
                ['label' => Yii::t('app', 'Transactions'), 'options' => ['class' => 'dropdown-header']],
                ['label' => \Yii::t('app', 'No. transactions'), 'url' => ['/chart/transactions']],
                //['label' => \Yii::t('app', 'Prorated Operations by advisor'), 'url' => ['/chart/pr-operation-by-advisor']],
                //['label' => \Yii::t('app', 'Prorated Operations by office'), 'url' => ['/chart/pr-operation-by-office']],
                //['label' => Yii::t('app', 'Ratios'), 'options' => ['class' => 'dropdown-header']],
                //['label' => \Yii::t('app', 'Attributed/Operation by advisor'), 'url' => ['/chart/attribution-over-operation-by-advisor']],
                //['label' => \Yii::t('app', 'Attributed/Operation by office'), 'url' => ['/chart/attribution-over-operation-by-office']],
            ]],
            ['label' => \Yii::t('app', 'Presentations'), 'visible' => \Yii::$app->user->can('admin'), 'items' => [
                ['label' => \Yii::t('app', 'Salesmeter'), 'url' => ['/presentation/n-sales']],
            ]]
    ]]);
    echo Nav::widget([
        'options' => ['class' => 'navbar-nav navbar-right'],
        'items' => [[
            'label' => (\Yii::$app->language === 'es') ? 'Español' : 'English',
            'url' => '#',
            'items' => [[
                'label' => (\Yii::$app->language === 'es') ? 'English' : 'Español',
                'url' => ['/site/chlan']
            ]]],
            Yii::$app->user->isGuest ? (
                ['label' => Yii::t('app', 'Login'), 'url' => ['/site/login']]
            ) : (
                '<li>'
                . Html::beginForm(['/site/logout'], 'post', ['class' => 'navbar-form'])
                . Html::submitButton(
                    \Yii::t('app', 'Logout') . ' (' . Yii::$app->user->identity->username . ')',
                    ['class' => 'btn btn-link']
                )
                . Html::endForm()
                . '</li>'
            )
        ]
    ]);
    NavBar::end(); ?>

    <div class="container-fluid" style="padding-top: 72px">
        <?= Breadcrumbs::widget([
            'encodeLabels' => false,
            'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
        ]) ?>
        <?= $content ?>
    </div>
</div>

<footer class="footer">
    <div class="container-fluid">
        <p>&copy; Inmobiliaria Cárdenas <?= date('Y') ?></p>
    </div>
</footer>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
