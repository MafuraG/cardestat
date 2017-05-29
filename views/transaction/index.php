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
$user = Yii::$app->user;
?>

<h1 class="page-header"><?= Yii::t('app', 'Transactions')?></h1>

<div class="transaction-index">
  <?php if ($user->can('contracts')): ?>
  <p><?= Html::a(Yii::t('app', 'Create Transaction'), ['transaction/create'], [
      'class' => 'btn btn-success btn-create',
  ]) ?></p>
  <?php endif; ?>

  <?= $this->render('_search', [
      'model' => $searchModel
  ]) ?>
  <div class="loading-indicator hidden"><?= Yii::t('app', 'Reloading...') ?></div>
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
$loadingLbl = Yii::t('app', 'Loading...');
$newTransactionLbl = Yii::t('app', 'Create Transaction');
$transactionLbl = Yii::t('app', 'Transaction');
$txUpdateUrl = Url::to(['transaction/update', 'id' => '_id_']);
$txViewUrl = Url::to(['transaction/view', 'id' => '_id_']);
$txRemoveUrl = Url::to(['transaction/delete', 'id' => '_id_']);
$areYouSure = Yii::t('app', 'Are you sure you want to delete this transaction?');
$script = <<< JS
  var txUpdateUrl = '$txUpdateUrl';
  var txViewUrl = '$txViewUrl';
  var txRemoveUrl = '$txRemoveUrl';
  var areYouSure = '$areYouSure';
  var \$transactionModal = $('#transaction-modal');
  var \$newTxBtn = $('.transaction-index .btn-create');
  $.pjax.defaults.timeout = 12000;
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
      var \$txSubmitBtn = $('.transaction-form button[type="submit"]');
      \$txSubmitBtn.button('loading');
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
  var last_id;
  function modalDataLoaded() {
      if (last_id)
          \$transactionModal.find('.modal-header h3').html('{$transactionLbl} #' + last_id);
      else \$transactionModal.find('.modal-header h3').html('{$newTransactionLbl}');
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
  $('#p0').on('click', 'a.transaction-remove', function() {
    if (!confirm(areYouSure)) return false;
    var id = $(this).closest('.transaction').data('key');
    $.ajax({
        url: txRemoveUrl.replace('_id_', id),
        method: 'post',
        success: function(data) {
            if (data == 'ok') $.pjax.reload('#p0');
            else alert(data);
        }, error: function(jqXHR, textStatus, errorThrown) {
            alert(textStatus + ': ' + errorThrown);
        }
    });
    return false;
  });
  $('#p0').on('click', 'a.transaction-edit', function() {
    var id = $(this).closest('.transaction').data('key');
    \$transactionModal.find('.modal-header h3').html('{$loadingLbl}');
    \$transactionModal.find('.modal-body #p1').html('');
    \$transactionModal.modal('show');
    $.pjax({container: '#p1', url: txUpdateUrl.replace('_id_', id), scrollTo: false, push: false});
    last_id = id;
  });
  $('#p0').on('click', 'a.transaction-details', function() {
    var id = $(this).closest('.transaction').data('key');
    $.pjax({container: '#p1', url: txViewUrl.replace('_id_', id), scrollTo: false, push: false});
    last_id = id;
  });
  var \$loadingIndicator = $('.loading-indicator');
  $('#p0').on('pjax:start', function(xhr, options) {
      \$loadingIndicator.toggleClass('hidden', false);
      $('#transactions-list-view').css('opacity', 0.3);
  });
  $('#p0').on('pjax:success', function(xhr, options) {
      \$loadingIndicator.toggleClass('hidden', true);
      $('#transactions-list-view').css('opacity', 1);
  });
  $('#p0').on('pjax:error', function(e, xhr) {
      alert(xhr.statusText);
  });
  $('#p1').on('pjax:success', function() {
      \$newTxBtn.button('reset');
  });
  $('.transaction-index .btn-create').on('click', function() {
      last_id = null;
      \$transactionModal.find('.modal-header h3').html('{$loadingLbl}');
      \$transactionModal.find('.modal-body #p1').html('');
      \$transactionModal.modal('show');
      $.pjax({container: '#p1', url: $(this).attr('href'), scrollTo: false, push: false});
      return false;
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
