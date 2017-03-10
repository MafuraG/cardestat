<?php
/* @var $this yii\web\View */
use yii\widgets\ListView;
use yii\bootstrap\BootstrapAsset;
use yii\widgets\Pjax;
use yii\bootstrap\Modal;
use yii\helpers\Html;
use yii\helpers\Url;
BootstrapAsset::register($this);
?>

<h1 class="page-header"><?= Yii::t('app', 'Transactions')?></h1>

<div class="transaction-index">
  <p>
  <?= Html::a(Yii::t('app', 'Create'), ['transaction/create'], [
      'class' => 'btn btn-primary',
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
      'header' => '<h4 class="modal-title"></h4>',
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
$tranFormUrl = Url::to(['transaction/update', 'id' => '_id_']);
$script = <<< JS
  var detailsUrl = '$tranFormUrl';
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
  \$transactionModal.on('submit', '.transaction-form form', function() {
      var \$form = $(this);
      $.ajax({
          url: \$form.attr('action'),
          type: \$form.attr('method'),
          data: \$form.serialize(),
          success: function (response) {                  
              \$transactionModal.modal('hide');
              $.pjax.reload('#p0');
          }, error: function () {
              console.log('internal server error');
          }
      });
      return false;
  });
  $('.transaction-list-item-search form .btn-reset').on('click', function() {
      \$form = $('.transaction-list-item-search form');
      \$form[0].reset();
      \$form.submit();
  });
  $('#p0').on('pjax:end', function(xhr, options) {
      $('[data-toggle="tooltip"]').tooltip()
  });
  $('#p1').on('pjax:end', function(xhr, options) {
      modalDataLoaded();
  });
  var editing = false;
  var last_id = -1;
  function modalDataLoaded() {
      \$transactionModal.find('.modal-header h4').html('{$transactionLbl} #' + last_id);
      if (editing) \$transactionModal.find('.btn-primary, .edit-mode').removeClass('hidden');
      else \$transactionModal.find('.btn-primary, .edit-mode').addClass('hidden');
      \$transactionModal.find('input, select, textarea, checkbox, .btn-danger')
          .attr('disabled', !editing);
      \$transactionModal.find('.kv-date-remove').toggleClass('hidden', !editing);
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
    editing = true;
    var id = $(this).closest('.transaction').data('key');
    if (id != last_id)
        $.pjax({container: '#p1', url: detailsUrl.replace('_id_', id), scrollTo: false, push: false});
    else modalDataLoaded();
    last_id = id;
  });
  $('#p0').on('click', 'a.transaction-details', function() {
    editing = false;
    var id = $(this).closest('.transaction').data('key');
    if (id != last_id)
        $.pjax({container: '#p1', url: detailsUrl.replace('_id_', id), scrollTo: false, push: false});
    else modalDataLoaded();
    last_id = id;
  });
JS;
$this->registerJs($script);
?>
