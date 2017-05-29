<?php
use yii\helpers\Url;
use app\models\Correction;

$formatter = Yii::$app->formatter;

$sellerLbl = Yii::t('app', 'Seller');
$buyerLbl = Yii::t('app', 'Buyer');
$propertyLbl = Yii::t('app', 'Property');
$salePriceLbl = Yii::t('app', 'Sale Price');
$commentsLbl = Yii::t('app', 'Comments');

function mkAttributionComments($tc) {
    $comments = $br = '';
    foreach ($tc['attributions'] as $attr) {
        $comments .= "$br{$attr['comments']}";
        if ($attr['comments']) $br = '<br>';
        else $br = '';
    }
    return $comments;
}
function mkInvoiceTooltip($tc, $formatter) {
    $invoices = $br = '';
    foreach ($tc['invoices'] as $i) {
        $invoices .= "$br{$i['code']} ({$i['issued_at']}): " . $formatter->asDecimal($i['amount_euc'] / 100., 2) . ' €';
        $br = '<br>';
    }
    return $invoices;
}

$zero = $formatter->asDecimal(0, 2);
$reasonLbl = Yii::t('app', 'Reason');
$correctionLbl = Yii::t('app', 'Correction');
$compensationLbl = Yii::t('app', 'Compensation');
$payrollLbl = Yii::t('app', 'Payroll');
$diff_table_begin = <<<EOT
  <table class="table table-condensed">
    <thead><tr>
      <th>{$reasonLbl}</th>
      <th>{$correctionLbl}</th>
      <th>{$compensationLbl}</th>
      <th>{$payrollLbl}</th>
    </tr></thead>
    <tbody>
EOT;
$diff_table_end = <<<EOT
  </tbody></table>
EOT;
$diff_cause_title = [
  Correction::TRANCHES_CHANGED => Yii::t('app', 'Tranches changed'),
  Correction::LATE_INVOICE_PROPAGATION => Yii::t('app', 'Late invoice propagation')
];

if (!isset($expanded)) $expanded = false;

foreach ($data as $advisor => $advisor_data): ?>
  <?php $tt_titles = []; ?>
  <div class="row">
    <div class="col-sm-2 col-xs-3">
      <h4><span class="label label-default col-md-12">
        <?= $formatter->asDate(mktime(0,0,0,1,1,$year), 'short') ?> &ndash;
        <?= $formatter->asDate(mktime(), 'short') ?>
      </span></h4>
    </div>
    <div class="col-sm-8 col-xs-3">
      <h4><span class="label label-primary col-md-12"><?= $advisor ?></span></h4>
      <br>
    </div>
    <div class="col-sm-2 col-xs-2">
      <h4><span class="label label-info col-md-12">
        <?= $formatter->asDecimal(($advisor_data['total_commission_euc'] + $advisor_data['total_compensated_euc'])/ 100., 2) ?> €
      </span></h4>
    </div>
    <?php $prev = null; $br = ''; $advisor_data['tranches_caption'] = '';
    foreach (array_reverse($advisor_data['tranches']) as $i => & $tranche_discard) {
        $tranche = & $advisor_data['tranches'][$i];
        $tranche['commission_pct'] = $formatter->asDecimal($tranche['commission_bp'] / 100., 2);
        $tranche['from_eu'] = $formatter->asDecimal($tranche['from_euc'] / 100., 2);
        if ($prev !== null) {
            $tranche['to_eu'] = $formatter->asDecimal(($prev['from_euc'] - 1) / 100., 2);
            $advisor_data['tranches_caption'] .=
                "{$br}{$tranche['from_eu']} - {$tranche['to_eu']} €: {$tranche['commission_pct']} %";
            $br = '<br>';
        }
        $prev = & $tranche;
    }
    $tranche = & $advisor_data['tranches'][0];
    $tranche['to_eu'] = '&infin;';
    $advisor_data['tranches_caption'] .=
        "{$br}{$tranche['from_eu']} - {$tranche['to_eu']} €: {$tranche['commission_pct']} %"; ?>
    <?php if ($expanded): ?>
      <div class="col-xs-4">
        <table class="table table-condensed table-striped text-right">
          <caption class="text-center"><?= Yii::t('app', 'Commission tranches') ?></caption>
          <thead class="text-center"><tr>
            <th><?= Yii::t('app', 'From') ?></th>
            <th><?= Yii::t('app', 'To') ?></th>
            <th><?= Yii::t('app', 'Rate') ?></th>
          <tr></thead>
          <tbody>
            <?php foreach ($advisor_data['tranches'] as $tranche): ?>
              <tr>
                <td><?= $tranche['from_eu'] ?> €</td>
                <td><?= $tranche['to_eu'] ?> €</td>
                <td><?= $tranche['commission_pct'] ?> %</td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
    <div class="col-xs-12">
      <div class="table-responsive">
        <table class="table table-condensed">
          <thead class="text-center"><tr>
            <th></th>
            <th class="nowrap"><?= Yii::t('app', 'Tx') ?></th>
            <th class="nowrap"><?= Yii::t('app', 'Invoiced') ?></th>
            <th class="nowrap"><?= Yii::$app->params['company'] ?></th>
            <th class="nowrap"><?= Yii::t('app', 'Partner') ?></th>
            <th class="nowrap"><?= Yii::t('app', 'Effective Attribution') ?></th>
            <th class="nowrap"><?= Yii::t('app', 'Total Attrib.') ?></th>
            <th class="nowrap"><?= Yii::t('app', 'Accumulated') ?></th>
            <th class="nowrap"><?= Yii::t('app', 'Tranche') ?></th>
            <th class="nowrap"><?= Yii::t('app', 'Commission') ?></th>
            <th class="nowrap"><?= Yii::t('app', 'Difference') ?></th>
          </tr></thead>
          <tbody>
            <?php foreach ($advisor_data['months'] as $month => $month_data): ?>
              <?php $first = true; $rowspan = count($month_data['transactions']); $fmonth = $formatter->asDate($month, 'MMM') ?>
              <tr>
                <td rowspan="<?= $rowspan ?>">
                  <h4><button role="button" class="btn btn-danger monospace toggle-payroll" <?php if (($month_data['commission_euc'] + $month_data['compensated_euc']) <= 0) echo 'disabled' ?> data-loading-text="<?= Yii::t('app', 'Saving...') ?>"
                    data-payroll_id="<?= $month_data['payroll']['id'] ?>"><?= $fmonth ?>
                  <?php if (isset($month_data['payroll']['commission_bp'])): ?>
                    <span class="glyphicon glyphicon-folder-close"></span> 
                  <?php else: ?>
                    <span class="glyphicon glyphicon-folder-open"></span> 
                  <?php endif; ?></button></h4>
                </td>
                <?php foreach ($month_data['transactions'] as $tc): ?>
                  <?php if (!$first): ?>
                    <tr>
                  <?php endif; ?>
                  <?php $salePriceEu = $formatter->asDecimal($tc['sale_price_euc'] / 100. ,2);
                  $seller_url = Url::to(['contact/view', 'id' => $tc['seller_id']]);
                  $buyer_url = Url::to(['contact/view', 'id' => $tc['buyer_id']]);
                  $property_url = Url::to(['contact/view', 'id' => $tc['property_id']]);
                  $popoverContent = (
                    "<dl class='text-left'> " .
                    " <dt>$sellerLbl</dt> " .
                    " <dd><a href='{$seller_url}'>{$tc['seller_name']}</a></dd> " .
                    " <dt>$buyerLbl</dt> " .
                    " <dd><a href='{$buyer_url}'>{$tc['buyer_name']}</a></dd> " .
                    " <dt>$propertyLbl</dt> " .
                    " <dd><a href='{$property_url}'>{$tc['property_reference']}</a></dd> " .
                    " <dt>$salePriceLbl</dt> " .
                    " <dd>{$salePriceEu} €</dd> " .
                    " <dt>$commentsLbl</dt> " .
                    " <dd>comments !!??</dd></dl>"); ?>
                  <td><a class="btn btn-default btn-xs btn-popover" data-title="<?= Yii::t('app', 'Transaction details') ?>" data-content="<?= $popoverContent ?>" data-toggle="popover" data-placement="bottom" data-html="true" tabindex="0" role="button" data-trigger="focus">
                    <span class="glyphicon glyphicon-info-sign">
                  </span></a> <a href="<?= Url::to(['transaction/view', 'id' => $tc['transaction_id']]) ?>">#<?= $tc['transaction_id']?></a></td>
                  <td class="text-right nowrap">
                    <?php $class = ($tc['total_invoiced_euc'] < $tc['our_fee_euc']) ? 'text-warning' : '' ?>
                    <?php $invoices = mkInvoiceTooltip($tc, $formatter); ?>
                    <span class="badge" data-toggle="tooltip" data-title="<?= $invoices ?>" data-html="true"><?= $tc['n_invoices'] ?></span> <span class="<?= $class ?>"><?= $formatter->asDecimal($tc['total_invoiced_euc'] / 100., 2) ?> €</span></td>
                  <td class="text-right nowrap"><?= $formatter->asDecimal($tc['our_fee_euc'] / 100., 2) ?> €</td>
                  <td class="text-right nowrap"><?= $formatter->asDecimal($tc['their_fee_euc'] / 100., 2) ?> €</td>
                  <td class="text-center"><?php $br = ''; foreach ($tc['attributions'] as $attr): ?>
                    <?= $br ?>
                    <small class="text-muted">
                    <?= $attr['type_name'] . ' ' . $formatter->asDecimal($attr['type_bp'] / 100., 2) . '% ' .
                      "({$attr['office']}): " ?>
                    </small>
                    <?= $formatter->asDecimal($attr['amount_euc'] / 100., 2) ?> €
                    <?php $br = '<br>'; ?>
                  <?php endforeach; ?></small></td>
                  <?php if ($first): ?>
                    <td rowspan="<?= $rowspan ?>" class="text-right text-success nowrap">
                      <?= $formatter->asDecimal($month_data['calculated_attribution_euc'] / 100., 2) ?> €</td>
                    <td rowspan="<?= $rowspan ?>" class="text-right nowrap">
                      <?= $formatter->asDecimal($month_data['calculated_accumulated_attribution_euc'] / 100., 2) ?> €</td>
                    <td rowspan="<?= $rowspan ?>" class="text-center">
                      <span class="label label-success" data-toggle="tooltip" 
                        title="<?= $advisor_data['tranches_caption'] ?>" data-html="true" role="button">
                      <?php echo $formatter->asDecimal($month_data['commission_bp'] / 100., 2) ?> %
                    </span></td>
                    <td rowspan="<?= $rowspan ?>" class="text-right nowrap
                      <?php if ($month_data['compensated_euc']) {
                          echo 'text-warning'; 
                          $tt_title = ''; $br = '';
                          foreach ($month_data['compensations'] as $compensation) {
                              $tt_title .= $br . "{$compensation['reason']} (" .
                                  $formatter->asDate($compensation['payroll']['month'], 'MMM \'\'yy') . '): ' .
                                  $formatter->asDecimal($compensation['compensation_euc'] / 100., 2) . ' €';
                              $br = '<br>';
                          }
                          if ($tt_title) $tt_titles[$month] = $tt_title;
                          $compensation_tooltip = 'data-toggle="tooltip" title="' . $tt_title . '" data-html="true"';
                      } else $compensation_tooltip= ''; ?>">
                      <strong <?= $compensation_tooltip ?>>
                        <?php echo $formatter->asDecimal(($month_data['commission_euc'] + $month_data['compensated_euc'])/ 100., 2) ?> €
                      </strong></td>
                    <td rowspan="<?= $rowspan ?>" class="text-right nowrap">
                    <?php if ($month_data['calculated_commission_euc'] != $month_data['commission_euc']): ?>
                      <?php if ($month_data['calculated_commission_euc'] - $month_data['commission_euc'] < 0)
                        $class = 'text-danger'; else $class = 'text-info'; ?>
                      <a class="pull-left btn btn-default btn-xs correction-popover" role="button" tabindex="0"
                        data-title="<?= Yii::t('app', 'Corrections') ?>&nbsp;
                          <button type='button' class='close'>&times;</button>"
                        data-placement="left" data-html="true" data-trigger="click" data-payroll_id="<?= $month_data['payroll']['id'] ?>">
                        <span class="text-warning glyphicon glyphicon-exclamation-sign"></span></a>
                      <span class="<?= $class ?>">
                        <?= $formatter->asDecimal(($month_data['calculated_commission_euc'] - 
                          $month_data['commission_euc'] + $month_data['payroll']['corrections_sum']) / 100., 2) ?> €
                      </span>
                      <table class="popover-table hidden"><tbody>
                        <?php foreach ($month_data['difference_causes'] as $cause => $diff_amount_euc): ?>
                          <tr>
                            <td><?= $diff_cause_title[$cause] ?></td>
                            <td class="text-right"><?= $formatter->asDecimal($diff_amount_euc / 100., 2) ?>€</td>
                            <td class="text-right"><?= $zero ?> €</td>
                            <td><?= $fmonth ?></td>
                          </tr>
                        <?php endforeach; ?>
                        <?php foreach ($month_data['payroll']['corrections'] as $correction): ?>
                          <tr>
                            <td><?= $correction['reason'] ?></td>
                            <td class="text-right"><?= $formatter->asDecimal($correction['corrected_euc'] / 100., 2) ?>€</td>
                            <td class="text-right"><?= $formatter->asDecimal($correction['compensation_euc'] / 100., 2) ?> €</td>
                            <td><?= $formatter->asDate($correction['compensation_on'], 'MMMM') ?></td>
                          </tr>
                        <?php endforeach; ?>
                      </tbody></table>
                    <?php else: ?><?= $zero ?> €
                    <?php endif; ?>
                    </td>
                  <?php else: ?>
                    </tr>
                  <?php endif; $first = false; ?>
                <?php endforeach; ?>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
        <?php if ($expanded) foreach ($tt_titles as $month => $ttit): ?>
            <p><small><?= "$fmonth: $ttit" ?></small></p>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
  <?php if ($expanded): ?>
    <small>
      <table class="table table-striped table-condensed">
        <caption class="text-center"><?= Yii::t('app', 'Transaction details') ?></caption>
        <thead class="text-center"><tr>
          <th><?= Yii::t('app', 'Tx') ?></th>
          <th><?= Yii::t('app', 'Seller') ?></th>
          <th><?= Yii::t('app', 'Buyer') ?></th>
          <th><?= Yii::t('app', 'Property') ?></th>
          <th><?= Yii::t('app', 'Sale Price') ?></th>
          <th><?= Yii::t('app', 'Invoices') ?></th>
          <th><?= Yii::t('app', 'Comments') ?></th>
        </tr></thead>
        <tbody>
          <?php foreach ($advisor_data['months'] as $month => $month_data): ?>
            <?php foreach ($month_data['transactions'] as $id => $tc): ?>
              <tr>
                <td>#<?= $id ?></td>
                <td><?= $tc['seller_name'] ?></td>
                <td><?= $tc['buyer_name'] ?></td>
                <td><?= $tc['property_reference'] ?></td>
                <td class="nowrap"><?= $formatter->asDecimal($tc['sale_price_euc'] / 100., 2) ?> €</td>
                <?php $invoices = mkInvoiceTooltip($tc, $formatter); ?>
                <td class="break-all"><?= $invoices ?></td>
                <td><?= mkAttributionComments($tc) ?></td>
              </tr>
            <?php endforeach; ?>
          <?php endforeach; ?>
        </tbody>
      </table>
    </small>
  <?php endif; ?>
<?php endforeach; ?>
<div id="correction-form-wrapper" class="hidden">
  <?= $this->render('/correction/_form', ['model' => new Correction()]) ?>
</div>
<div id="correction-table-wrapper" class="hidden">
  <table id="correction-table" class="table table-condensed">
    <thead><tr>
      <th><?= $reasonLbl ?></th>
      <th><?= $correctionLbl ?></th>
      <th><?= $compensationLbl ?></th>
      <th><?= $payrollLbl ?></th>
    </tr></thead>
    <tbody>
    </tbody>
  </table>
</div>
<?php
$correction_create_url = Url::to(['/correction/create']);
$toggle_payroll_url = Url::to(['toggle-payroll']);
$script = <<< JS
  var \$correctionFormWrapper = $('#correction-form-wrapper').detach();
  var togglePayrollUrl = '$toggle_payroll_url';
  $('[data-toggle="tooltip"]').tooltip();
  $('[data-toggle="popover"]').popover();
  $(document).on('hide.bs.popover', '.correction-popover', function() {
      var \$correctionForm = $(this).next('.popover').find('.correction-form').detach();
      \$correctionFormWrapper.append(\$correctionForm);
      \$correctionForm.find('form')[0].reset();
  });
  $(document).on('shown.bs.popover', '.correction-popover', function() {
      $(this).next('.popover').find('.correction-form').replaceWith(\$correctionFormWrapper.find('.correction-form'));
      $(this).next('.popover').find('.correction-form').find('input[name="Correction[payroll_id]"]').val($(this).data('payroll_id'));
  });
  function activatePopovers() {
      $('.correction-popover').popover({
          content: function() {
              return $('#correction-table-wrapper').find('tbody')
                  .html($(this).siblings('.popover-table').find('tbody').html()).end().html() + \$correctionFormWrapper.html();
          }
      });
  };
  activatePopovers();
  $(document).on('submit', '.correction-form form', function() {
      $.ajax({
          url: '$correction_create_url',
          method: 'post',
          data: $(this).serialize(),
          success: function(response) {
              if (response === '') location.reload(true);
              $('input[name="Correction[payroll_id]"]').next('.help-block')
                  .html($(response).find('input[name="Correction[payroll_id]"]').next('.help-block').html())
                  .closest('.form-group').removeClass('has-success').addClass('has-error');
          }
      });
      return false;
  });
  $(document).on('click', '.popover .close', function() {
      $(this).closest('.popover').siblings('.correction-popover').trigger('click');
  });
  $(document).on('click', '.toggle-payroll', function() {
      var \$glyph = $(this).find('.glyphicon');
      \$glyph.removeClass('glyphicon-folder-open glyphicon-folder-close').addClass('glyphicon-refresh gly-spin');
      var payroll_id = $(this).data('payroll_id');
      $.ajax({
         url: togglePayrollUrl,
         data: 'id=' + payroll_id,
         success: function(payroll) {
             if (payroll.commission_bp != null) // closed payroll
                 \$glyph.removeClass('glyphicon-refresh gly-spin').addClass('glyphicon-folder-close');
             else // open payroll
                 \$glyph.removeClass('glyphicon-refresh gly-spin').addClass('glyphicon-folder-open');
         },
         error: function(jqXHR, textStatus, errorThrown) {
             alert(textStatus);
         }
      });
  });
JS;
$this->registerJs($script);
