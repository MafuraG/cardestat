<?php
/* @var $this yii\web\View */
use yii\widgets\ListView;
use yii\bootstrap\BootstrapAsset;
use yii\widgets\Pjax;
use yii\bootstrap\Modal;
use yii\helpers\Html;
use yii\helpers\Url;
use kartik\select2\Select2;
BootstrapAsset::register($this);
// force loading Select2 CSS for later use by pjax loaded modal:
Select2::widget([
    'name' => 'foo',
    'value' => '',
]);
?>

<h1 class="page-header"><?= Yii::t('app', 'Transactions')?></h1>

<div class="transaction-index">
  <p>
  <?= Html::a(Yii::t('app', 'Create Transaction'), ['transaction/create'], [
      'class' => 'btn btn-success',
  ]) ?>
  </p>
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
  <?php Modal::begin([
      'options' => ['tabindex' => false],
      'header' => '<h3 class="modal-title"></h3>',
      'size' => Modal::SIZE_LARGE,
      'id' => 'transaction-modal'
  ]) ?>
    <div class="container-fluid">
    <?php Pjax::begin() ?>
    <?php Pjax::end() ?>
    </div>
  <?php Modal::end() ?>
</div>
<?php
$transactionLbl = Yii::t('app', 'Transaction');
$txUpdateUrl = Url::to(['transaction/update', 'id' => '_id_']);
$txViewUrl = Url::to(['transaction/view', 'id' => '_id_']);
$script = <<< JS
  var txUpdateUrl = '$txUpdateUrl';
  var txViewUrl = '$txViewUrl';
  var \$transactionModal = $('#transaction-modal');
  $.pjax.defaults.timeout = 6000;
  $('.transaction-index').on('submit', '.transaction-list-item-search form', function(e) {
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
  \$transactionModal.on('submit', '.transaction-form form', function(e) {
      var \$form = $(this);
      $.pjax.submit(e, '#p1', {push: false});
      return false;
  });
  $('.transaction-list-item-search form .btn-reset').on('click', function() {
      \$form = $('.transaction-list-item-search form');
      \$form.find(':input')
          .filter(':not([name="direction"])')
          .filter(':not([name="sort"])').val(null);
      \$form.submit();
  });
  $('#p0').on('pjax:end', function(xhr, options) {
      $('[data-toggle="tooltip"]').tooltip()
  });
  $('#p1').on('pjax:end', function(xhr, options) {
      if (options.responseText === 'ok') {
          reload_list = true;
          \$transactionModal.modal('hide');
      } else modalDataLoaded();
  });
  var last_id = -1;
  function modalDataLoaded() {
      \$transactionModal.find('.modal-header h3').html('{$transactionLbl} #' + last_id);
      \$transactionModal.modal('show');
  }
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
  $('#p0').on('click', 'a.transaction-edit', function() {
    var id = $(this).closest('.transaction').data('key');
    $.pjax({container: '#p1', url: txUpdateUrl.replace('_id_', id), scrollTo: false, push: false});
    last_id = id;
  });
  $('#p0').on('click', 'a.transaction-details', function() {
    var id = $(this).closest('.transaction').data('key');
    $.pjax({container: '#p1', url: txViewUrl.replace('_id_', id), scrollTo: false, push: false});
    last_id = id;
  });
  var reload_list = false;
  \$transactionModal.on('pjax:end', '#invoice-index-p0, #attribution-index-p0', function() {
    reload_list = true;
  });
  \$transactionModal.on('hide.bs.modal', function() {
    if (reload_list) $.pjax.reload('#p0');
    reload_list = false;
    return true;
  });
JS;
$this->registerJs($script);
?>
