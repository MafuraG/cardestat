<?php

function mkInvoiceTooltip($tc) {
    $icodes = explode(',', substr($tc['invoice_codes'], 1, strlen($tc['invoice_codes']) - 2));
    $idates = explode(',', substr($tc['invoice_issuance_dates'], 1, strlen($tc['invoice_issuance_dates']) - 2));
    $icd = array_combine($icodes, $idates);
    $invoices = $br = '';
    foreach ($icd as $code => $date) {
        $invoices .= "$br$code ($date)";
        $br = '<br>';
    }
    return $invoices;
}

if (!isset($expanded)) $expanded = false;
$formatter = Yii::$app->formatter;

foreach ($data as $advisor => $advisor_data): ?>
  <div class="row">
    <div class="col-xs-2">
      <h4><span class="label label-default col-md-12">
        <?= $formatter->asDate(mktime(0,0,0,1,1,$year), 'short') ?> &ndash;
        <?= $formatter->asDate(mktime(), 'short') ?>
      </span></h4>
    </div>
    <div class="col-xs-8">
      <h4><span class="label label-primary col-md-12"><?= $advisor ?></span></h4>
      <br>
    </div>
    <div class="col-xs-2">
      <h4><span class="label label-info col-md-12"><?= $formatter->asDecimal($advisor_data['total_commission_euc'] / 100., 2) ?> €</span></h4>
    </div>
    <div class="col-xs-12">
      <div class="table-responsive">
        <table class="table table-condensed">
          <thead class="text-center"><tr>
            <th></th>
            <th><?= Yii::t('app', 'Tx') ?></th>
            <th><?= Yii::t('app', 'Invoiced') ?></th>
            <th><?= Yii::t('app', 'Fees Cárdenas') ?></th>
            <th><?= Yii::t('app', 'Fees Partner') ?></th>
            <th><?= Yii::t('app', 'Office') ?></th>
            <th><?= Yii::t('app', 'Attr. type') ?></th>
            <th><?= Yii::t('app', 'Attribution') ?></th>
            <th><?= Yii::t('app', 'Total Attrib.') ?></th>
            <th><?= Yii::t('app', 'Accumulated') ?></th>
            <th><?= Yii::t('app', 'Tranche') ?></th>
            <th><?= Yii::t('app', 'Commission') ?></th>
            <th><?= Yii::t('app', 'Correction') ?></th>
          </tr></thead>
          <tbody>
            <?php foreach ($advisor_data['months'] as $month => $month_data): ?>
              <?php $first = true; $rowspan = count($month_data['transactions']); ?>
              <tr>
                <td rowspan="<?= $rowspan ?>">
                  <h4><span class="label label-danger monospace"><?= $formatter->asDate($month, 'MMM') ?></span></h4>
                </td>
                <?php foreach ($month_data['transactions'] as $tc): ?>
                  <?php if (!$first): ?>
                    <tr>
                  <?php endif; ?>
                  <td><button class="btn btn-default btn-xs btn-popover">
                    <span class="glyphicon glyphicon-info-sign">
                  </span></button> <a href="#">#<?= $tc['transaction_id']?></a></td>
                  <td class="text-right nowrap">
                    <?php $class = ($tc['total_invoiced_euc'] < $tc['our_fee_euc']) ? 'text-danger' : '' ?>
                    <?php $invoices = mkInvoiceTooltip($tc); ?>
                    <span class="badge" data-toggle="tooltip" data-title="<?= $invoices ?>" data-html="true"><?= $tc['n_invoices'] ?></span> <span class="<?= $class ?>"><?= $formatter->asDecimal($tc['total_invoiced_euc'] / 100., 2) ?> €</span></td>
                  <td class="text-right nowrap"><?= $formatter->asDecimal($tc['our_fee_euc'] / 100., 2) ?> €</td>
                  <td class="text-right nowrap"><?= $formatter->asDecimal($tc['their_fee_euc'] / 100., 2) ?> €</td>
                  <td><small><?= str_replace('$$', '<br>', $tc['attribution_offices']) ?></small></td>
                  <td class="text-center"><small>
                    <?php
                        $attrtt = explode('$$', $tc['attribution_type_names']);
                        $attrbps = explode('$$', $tc['attribution_type_bps']);
                        for ($i = 0; $i < count($attrtt); $i++) {
                            echo $attrtt[$i] . ' ' . $formatter->asDecimal($attrbps[$i] / 100., 2) . '%' . '<br>';
                        } ?>
                  </small></td>
                  <td class="text-right nowrap">
                    <?php $tas = explode('$$', $tc['total_attributed_euc']);
                      foreach ($tas as $ta)
                          echo $formatter->asDecimal($ta / 100., 2) . ' €<br>'; ?>
                  </td>
                  <?php if ($first): ?>
                    <td rowspan="<?= $rowspan ?>" class="text-right text-success nowrap"><?= $formatter->asDecimal($month_data['month_attribution_euc'] / 100., 2) ?> €</td>
                    <td rowspan="<?= $rowspan ?>" class="text-right nowrap"><?= $formatter->asDecimal($month_data['accumulated_attribution_euc'] / 100., 2) ?> €</td>
                    <td rowspan="<?= $rowspan ?>" class="text-center"><span class="label label-success">
                      <?php if (isset($month_data['tranche_bp']))
                          echo $formatter->asDecimal($month_data['tranche_bp'] / 100., 2);
                      else echo '?'; ?> %
                    </span></td>
                    <td rowspan="<?= $rowspan ?>" class="text-right nowrap"><strong>
                      <?php if (isset($month_data['commission_euc']))
                          echo $formatter->asDecimal($month_data['commission_euc'] / 100., 2);
                      else echo '?'; ?> €
                    </strong></td>
                    <?php if (isset($month_data['commission_corrected_euc'])) {
                        if ($month_data['commission_corrected_euc'] - $month_data['commission_euc'] < 0)
                            $class = 'text-danger';
                        else $class = 'text-info';
                    } else $class = ''; ?>
                    <td rowspan="<?= $rowspan ?>" class="text-right nowrap <?= $class ?>">
                      <?php if (isset($month_data['commission_corrected_euc'])) 
                          echo $formatter->asDecimal(($month_data['commission_corrected_euc'] -
                          $month_data['commission_euc']) / 100., 2);
                      else echo '?'; ?> €
                    </td>
                  <?php else: ?>
                    </tr>
                  <?php endif; $first = false; ?>
                <?php endforeach; ?>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
  <?php if ($expanded): ?>
  <div class="row">
    <small>
      <div class="col-xs-8">
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
                  <td class="break-all"><?= $tc['invoice_codes'] ?></td>
                  <td><?= $tc['attribution_comments'] ?></td>
                </tr>
              <?php endforeach; ?>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <div class="col-xs-4">
        <?php if (count($advisor_data['months']) > 0 and
            ($month_data = reset($advisor_data['months'])) and 
            count($month_data['transactions']) > 0 and
            ($tc = reset($month_data['transactions']))): ?>
          <table class="table table-condensed table-striped text-right">
            <caption class="text-center"><?= Yii::t('app', 'Commission tranches') ?></caption>
            <thead class="text-center"><tr>
              <th><?= Yii::t('app', 'From') ?></th>
              <th><?= Yii::t('app', 'To') ?></th>
              <th><?= Yii::t('app', 'Rate') ?></th>
            <tr></thead>
            <tbody>
              <?php $tranches = []; $prev = null;
              foreach (array_reverse($tc['tranches']) as $i => $tranche) {
                  $tranches[$i] = $tranche;
                  if ($prev !== null) $tranches[$prev]['to_euc'] = $tranche['from_euc'] - 1;
                  $prev = $i;
              } if ($prev) $tranches[$prev]['to_euc'] = '&infin;' ?>
              <?php foreach ($tranches as $tranche): ?>
                <tr>
                  <td><?= $formatter->asDecimal($tranche['from_euc'] / 100., 2) ?> €</td>
                  <td><?= is_numeric($tranche['to_euc']) ? ($formatter->asDecimal($tranche['to_euc'] / 100., 2) . ' €') : $tranche['to_euc'] ?> </td>
                  <td><?= $formatter->asDecimal($tranche['commission_bp'] / 100., 2) ?> %</td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php endif; ?>
      </div>
    </small>
  </div>
  <?php endif; ?>
<?php endforeach; ?>
<?php
$script = <<< JS
  $('[data-toggle="tooltip"]').tooltip();
JS;
$this->registerJs($script);
