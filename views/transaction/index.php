<?php
/* @var $this yii\web\View */
use yii\widgets\ListView;
use yii\bootstrap\BootstrapAsset;
use yii\widgets\Pjax;
BootstrapAsset::register($this);
?>
<h1 class="page-header"><?= Yii::t('app', 'Transactions')?></h1>
<?= $this->render('_search', [
    'model' => $searchModel
]) ?>
<?php Pjax::begin() ?>
<?= ListView::widget([
    'id' => 'transactions-list-view',
    'dataProvider' => $dataProvider,
    'itemView' => '_list_item.twig',
    'itemOptions' => function ($model, $key, $index, $widget) {
            return ['class' => 'transaction' . ($index == 0 ? ' first-transaction' : '')];
        }
]); ?>
<?php Pjax::end() ?>
<?php
$script = <<< JS
  $.pjax.defaults.timeout = 6000;
  $('.transaction-list-item-search form').on('submit', function(e) {
      $.pjax.submit(e, '#p0', {scrollTo: false})
      closeAdvancedSearch();
      return false;
  });
  $('.transaction-list-item-search form select[name="direction"]').on('change', function() {
      var d = $(this).val();
      $('.transaction-list-item-search form select[name="sort"] option').each(function(i) {
          $(this).val(d + $(this).data('value'));
      });
  });
  $('.transaction-list-item-search form .btn-reset').on('click', function() {
      \$form = $('.transaction-list-item-search form');
      \$form[0].reset();
      \$form.submit();
  });
  $('#p0').on('pjax:end', function(xhr, options) {
      $('[data-toggle="tooltip"]').tooltip()
  });
  $('[data-toggle="tooltip"]').tooltip()
  $('#advanced-search-caret').on('click', function() {
    $('#advanced-search-box').css('width', $(this).siblings('.form-control').eq(0).outerWidth());
    $('#advanced-search-box').toggleClass('hidden');
    $(document).on('click', closeAdvancedSearch);
    return false;
  });
  $('#advanced-search-box .close').on('click', function() {
    $('#advanced-search-box').addClass('hidden');
    $(document).off('click', closeAdvancedSearch);
  });
  function closeAdvancedSearch(e) {
    if (!e || !$(e.target).closest('#advanced-search-box').length) {
      $('#advanced-search-box').addClass('hidden');
      $(document).off('click', closeAdvancedSearch);
    }
  }
JS;
$this->registerJs($script);
?>
