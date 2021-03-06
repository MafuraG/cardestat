<?php

namespace app\controllers;

use Yii;
use app\models\Office;
use app\models\Advisor;
use app\models\Invoice;
use app\models\Transaction;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\web\Response;

/**
 */
class ChartController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [[
                    'allow' => true,
                    'roles' => ['accounting']
                ]]
            ],
        ];
    }
    /**
     */
    public function actionTransactions($from = null, $to = null, $interval_months = 1, $transaction_type = null)
    {
        extract($this->getDoubleSumDefaultPeriods($from, $to));
        $aux1 = ArrayHelper::index(Transaction::countAll($from, $to, $interval_months, $transaction_type, 'sum1_eu'), 'period');
        $aux2 = ArrayHelper::index(Transaction::countShared($from, $to, $interval_months, $transaction_type, 'sum2_eu'), 'period');
        $turnover = ArrayHelper::merge($aux1, $aux2);
        $intervals = [
            1 => Yii::t('app', 'Monthly'),
            3 => Yii::t('app', 'Quarterly'),
            12 => Yii::t('app', 'Yearly'),
        ];
        $title= Yii::t('app', 'No. transactions');
        $subtitle = Yii::t('app', 'Number of all our transactions vs. transactions shared with partners');
        $comments = Yii::t('app', 'All transactions: no. those participated by {company} / Shared transactions: those where a partner brings either the seller or the buyer.', ['company' => Yii::$app->params['company']]);
        $data = [
            'sums' => $turnover,
            'from' => $from,
            'to' => $to,
            'period' => $label,
            'interval_months' => $interval_months,
            'intervals' => $intervals,
            'transaction_type' => $transaction_type,
            'label1' => Yii::t('app', 'All transactions'),
            'label2' => Yii::t('app', 'Shared transactions'),
            'title' => $title,
            'subtitle' => $subtitle,
            'comments' => $comments
        ];
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return $data;
        } else return $this->render('double_period_sum', $data);
    }
    /**
     */
    public function actionVolume($from = null, $to = null, $interval_months = 1, $transaction_type = null)
    {
        extract($this->getDoubleSumDefaultPeriods($from, $to));
        $aux1 = ArrayHelper::index(Transaction::getVolume($from, $to, $interval_months, $transaction_type, 'sum1_eu'), 'period');
        $aux2 = ArrayHelper::index(Invoice::getRevenue($from, $to, $interval_months, $transaction_type, 'sum2_eu'), 'period');
        $turnover = ArrayHelper::merge($aux1, $aux2);
        $intervals = [
            1 => Yii::t('app', 'Monthly'),
            3 => Yii::t('app', 'Quarterly'),
            12 => Yii::t('app', 'Yearly'),
        ];
        $title= Yii::t('app', 'Volume vs. Revenues (<em>1st invoice date</em>)');
        $subtitle = Yii::t('app', 'Price of the traded property vs. fees invoiced');
        $comments = Yii::t('app', 'A spread between the curves is normally caused by transactions made in colaboration.');
        $data = [
            'sums' => $turnover,
            'from' => $from,
            'to' => $to,
            'period' => $label,
            'interval_months' => $interval_months,
            'intervals' => $intervals,
            'transaction_type' => $transaction_type,
            'label1' => Yii::t('app', 'Price of properties traded') . ' €',
            'label2' => Yii::t('app', 'Fees invoiced') . ' €',
            'title' => $title,
            'subtitle' => $subtitle,
            'comments' => $comments
        ];
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return $data;
        } else return $this->render('double_period_sum', $data);
    }
    /**
     */
    public function actionAccuVolume($from = null, $to = null, $interval_months = 1, $transaction_type = null)
    {
        extract($this->getDoubleSumDefaultPeriods($from, $to));
        $aux1 = ArrayHelper::index(Transaction::getVolume($from, $to, $interval_months, $transaction_type, 'sum1_eu'), 'period');
        $aux2 = ArrayHelper::index(Invoice::getRevenue($from, $to, $interval_months, $transaction_type, 'sum2_eu'), 'period');
        $turnover = ArrayHelper::merge($aux1, $aux2);
        $intervals = [
            1 => Yii::t('app', 'Monthly'),
            3 => Yii::t('app', 'Quarterly'),
            12 => Yii::t('app', 'Yearly'),
        ];
        $title= Yii::t('app', 'Accumulated Volume vs. Revenues (<em>1st invoice date</em>)');
        $subtitle = Yii::t('app', 'Price of the traded property vs. fees invoiced');
        $comments = Yii::t('app', 'A spread between the curves is normally caused by transactions made in colaboration.');
        $data = [
            'sums' => $turnover,
            'from' => $from,
            'to' => $to,
            'period' => $label,
            'interval_months' => $interval_months,
            'intervals' => $intervals,
            'transaction_type' => $transaction_type,
            'label1' => Yii::t('app', 'Accumulated price of properties traded') . ' €',
            'label2' => Yii::t('app', 'Accumulated fees invoiced') . ' €',
            'title' => $title,
            'subtitle' => $subtitle,
            'comments' => $comments
        ];
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return $data;
        } else return $this->render('double_period_accu_sum', $data);
    }
    /**
     */
    public function actionAvgVolume($from = null, $to = null, $interval_months = 1, $transaction_type = null)
    {
        extract($this->getDoubleSumDefaultPeriods($from, $to));
        $aux1 = ArrayHelper::index(Transaction::getAvgVolume($from, $to, $interval_months, $transaction_type, 'sum1_eu'), 'period');
        $aux2 = ArrayHelper::index(Invoice::getAvgRevenue($from, $to, $interval_months, $transaction_type, 'sum2_eu'), 'period');
        $turnover = ArrayHelper::merge($aux1, $aux2);
        //\yii\helpers\VarDumper::dump($turnover, 6, true); die;
        $intervals = [
            1 => Yii::t('app', 'Monthly'),
            3 => Yii::t('app', 'Quarterly'),
            12 => Yii::t('app', 'Yearly'),
        ];
        $title= Yii::t('app', 'Avg. Volume vs. Avg. Revenue (<em>1st invoice date</em>)');
        $subtitle = Yii::t('app', 'Property price per transaction vs. fees invoiced per transaction');
        $data = [
            'sums' => $turnover,
            'from' => $from,
            'to' => $to,
            'period' => $label,
            'interval_months' => $interval_months,
            'intervals' => $intervals,
            'transaction_type' => $transaction_type,
            'label1' => Yii::t('app', 'Avg. price of properties traded') . ' €',
            'label2' => Yii::t('app', 'Avg. fees invoiced') . ' €',
            'title' => $title,
            'subtitle' => $subtitle,
            'comments' => ''
        ];
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return $data;
        } else return $this->render('double_period_sum', $data);
    }
    /**
     */
    public function actionPrOperationByOffice($from1 = null, $to1 = null, $label1 = null, $from2 = null, $to2 = null, $label2 = null)
    {
        list($period1, $period2) = $this->getSumByCountDefaultPeriods($from1, $to1, $label1, $from2, $to2, $label2);
        $no = mb_strtoupper(Yii::t('app', 'Employees w/o office'));
        $na = mb_strtoupper(Yii::t('app', 'Direction (no office)'));
        $aux1 = ArrayHelper::index(
            Office::getProratedOperationCount($period1['from'], $period1['to'], 'sum1_eu', 'count1', $no, $na), 'name');
        $aux2 = ArrayHelper::index(
            Office::getProratedOperationCount($period2['from'], $period2['to'], 'sum2_eu', 'count2', $no, $na), 'name');
        $offices = ArrayHelper::merge($aux1, $aux2);
        ksort($offices);
        $title= Yii::t('app', 'Prorated Operations by office');
        $subtitle = Yii::t('app', 'Total number of pro-rated operations per office');
        $comments = Yii::t('app', 'A pro-rated number for an operation is the proportion attributed to the office in that operation.');
        $data = [
            'groupings' => $offices,
            'period1' => $period1,
            'period2' => $period2,
            'title' => $title,
            'subtitle' => $subtitle,
            'comments' => $comments,
            'label' => Yii::t('app', 'No. operations')
        ];
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return $data;
        } else return $this->render('sum_by_count', $data);
    }
    /**
     */
    public function actionAttributionByOfficeOnOptionDate($from1 = null, $to1 = null, $label1 = null, $from2 = null, $to2 = null, $label2 = null)
    {
        list($period1, $period2) = $this->getSumByCountDefaultPeriods($from1, $to1, $label1, $from2, $to2, $label2);
        $no = mb_strtoupper(Yii::t('app', 'Employees w/o office'));
        $na = mb_strtoupper(Yii::t('app', 'Direction (no office)'));
        $aux1 = ArrayHelper::index(
            Office::getAttributionSumOnOptionDate($period1['from'], $period1['to'], 'sum1_eu', 'count1', $na, $no), 'name');
        $aux2 = ArrayHelper::index(
            Office::getAttributionSumOnOptionDate($period2['from'], $period2['to'], 'sum2_eu', 'count2', $na, $no), 'name');
        $offices = ArrayHelper::merge($aux1, $aux2);
        ksort($offices);
        $title= Yii::t('app', 'Attribution by office (<em>option date</em>)');
        $subtitle = Yii::t('app', 'From detail using option date');
        $formatter = Yii::$app->formatter;
        $comments = Yii::t('app', 'Available for transactions invoiced after {date}', ['date' => $formatter->asDate('2016-01-01')]);
        $data = [
            'groupings' => $offices,
            'period1' => $period1,
            'period2' => $period2,
            'title' => $title,
            'subtitle' => $subtitle,
            'label' => Yii::t('app', 'Attributed') . ' €',
            'comments' => $comments
        ];
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return $data;
        } else return $this->render('sum_by_count', $data);
    }
    /**
     */
    public function actionAttributionByOfficeOnInvoiceDate($from1 = null, $to1 = null, $label1 = null, $from2 = null, $to2 = null, $label2 = null)
    {
        list($period1, $period2) = $this->getSumByCountDefaultPeriods($from1, $to1, $label1, $from2, $to2, $label2);
        $no = mb_strtoupper(Yii::t('app', 'Employees w/o office'));
        $na = mb_strtoupper(Yii::t('app', 'Direction (no office)'));
        $aux1 = ArrayHelper::index(
            Office::getAttributionSumOnInvoiceDate($period1['from'], $period1['to'], 'sum1_eu', 'count1', $no, $na), 'joint_name');
        $aux2 = ArrayHelper::index(
            Office::getAttributionSumOnInvoiceDate($period2['from'], $period2['to'], 'sum2_eu', 'count2', $no, $na), 'joint_name');
        $offices = ArrayHelper::merge($aux1, $aux2);
        ksort($offices);
        $title= Yii::t('app', 'Attribution by office (<em>invoice date</em>)');
        $subtitle = Yii::t('app', 'Union of detail and archive using invoice date');
        $data = [
            'groupings' => $offices,
            'period1' => $period1,
            'period2' => $period2,
            'title' => $title,
            'subtitle' => $subtitle,
            'label' => Yii::t('app', 'Attributed') . ' €'
        ];
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return $data;
        } else return $this->render('sum_by_count', $data);
    }
    /**
     */
    public function actionAttributionOverOperationByOffice($from1 = null, $to1 = null, $label1 = null, $from2 = null, $to2 = null, $label2 = null)
    {
        list($period1, $period2) = $this->getSumByCountDefaultPeriods($from1, $to1, $label1, $from2, $to2, $label2);
        $no = mb_strtoupper(Yii::t('app', 'Employees w/o office'));
        $na = mb_strtoupper(Yii::t('app', 'Direction (no office)'));
        $aux1 = ArrayHelper::index(
            Office::getAttributionOverOperationCount($period1['from'], $period1['to'], 'sum1_eu', 'count1', $no, $na), 'name');
        $aux2 = ArrayHelper::index(
            Office::getAttributionOverOperationCount($period2['from'], $period2['to'], 'sum2_eu', 'count2', $no, $na), 'name');
        $offices = ArrayHelper::merge($aux1, $aux2);
        ksort($offices);
        $title= Yii::t('app', 'Attributed/Operation by office');
        $subtitle = Yii::t('app', '');
        $subtitle = Yii::t('app', 'Average attribution per operation by office');
        $data = [
            'groupings' => $offices,
            'period1' => $period1,
            'period2' => $period2,
            'title' => $title,
            'subtitle' => $subtitle,
            'label' => Yii::t('app', 'Attributed') . ' €'
        ];
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return $data;
        } else return $this->render('sum_by_count', $data);
    }
    /**
     */
    public function actionPrOperationByAdvisor($from1 = null, $to1 = null, $label1 = null, $from2 = null, $to2 = null, $label2 = null)
    {
        list($period1, $period2) = $this->getSumByCountDefaultPeriods($from1, $to1, $label1, $from2, $to2, $label2);
        $aux1 = ArrayHelper::index(
            Advisor::getProratedOperationCount($period1['from'], $period1['to'], 'sum1_eu', 'count1'), 'name');
        $aux2 = ArrayHelper::index(
            Advisor::getProratedOperationCount($period2['from'], $period2['to'], 'sum2_eu', 'count2'), 'name');
        $advisors = ArrayHelper::merge($aux1, $aux2);
        ksort($advisors);
        $title= Yii::t('app', 'Prorated Operations by advisor');
        $subtitle = Yii::t('app', 'Total number of pro-rated operations per advisor');//Numero de operaciones prorrateadas por asesor');
        $comments = Yii::t('app', 'A pro-rated number for an operation is the proportion attributed to the advisor in that operation.');
        $data = [
            'groupings' => $advisors,
            'period1' => $period1,
            'period2' => $period2,
            'title' => $title,
            'title' => $title,
            'subtitle' => $subtitle,
            'comments' => $comments,
            'label' => Yii::t('app', 'No. operations')
        ];
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return $data;
        } else return $this->render('sum_by_count', $data);
    }
    /**
     */
    public function actionAttributionByAdvisorOnOptionDate($from1 = null, $to1 = null, $label1 = null, $from2 = null, $to2 = null, $label2 = null)
    {
        list($period1, $period2) = $this->getSumByCountDefaultPeriods($from1, $to1, $label1, $from2, $to2, $label2);
        $no = mb_strtoupper(Yii::t('app', 'Direction'));
        $aux1 = ArrayHelper::index(
            Advisor::getAttributionSumOnOptionDate($period1['from'], $period1['to'], 'sum1_eu', 'count1', $no), 'name');
        $aux2 = ArrayHelper::index(
            Advisor::getAttributionSumOnOptionDate($period2['from'], $period2['to'], 'sum2_eu', 'count2', $no), 'name');
        $advisors = ArrayHelper::merge($aux1, $aux2);
        $title= Yii::t('app', 'Attribution by advisor (<em>option date</em>)');
        $subtitle = Yii::t('app', 'From detail using option date');
        $formatter = Yii::$app->formatter;
        $comments = Yii::t('app', 'Available for transactions invoiced after {date}', ['date' => $formatter->asDate('2016-01-01')]);
        ksort($advisors);
        $data = [
            'groupings' => $advisors,
            'period1' => $period1,
            'period2' => $period2,
            'title' => $title,
            'label' => Yii::t('app', 'Attributed') . ' €',
            'subtitle' => $subtitle,
            'comments' => $comments
        ];
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return $data;
        } else return $this->render('sum_by_count', $data);
    }
    /**
     */
    public function actionAttributionByAdvisorOnInvoiceDate($from1 = null, $to1 = null, $label1 = null, $from2 = null, $to2 = null, $label2 = null)
    {
        list($period1, $period2) = $this->getSumByCountDefaultPeriods($from1, $to1, $label1, $from2, $to2, $label2);
        $no = mb_strtoupper(Yii::t('app', 'Direction'));
        $aux1 = ArrayHelper::index(
            Advisor::getAttributionSumOnInvoiceDate($period1['from'], $period1['to'], 'sum1_eu', 'count1', $no), 'joint_name');
        $aux2 = ArrayHelper::index(
            Advisor::getAttributionSumOnInvoiceDate($period2['from'], $period2['to'], 'sum2_eu', 'count2', $no), 'joint_name');
        $advisors = ArrayHelper::merge($aux1, $aux2);
        $title= Yii::t('app', 'Attribution by advisor (<em>invoice date</em>)');
        $subtitle = Yii::t('app', 'Union of detail and archive using invoice date');
        ksort($advisors);
        $data = [
            'groupings' => $advisors,
            'period1' => $period1,
            'period2' => $period2,
            'title' => $title,
            'subtitle' => $subtitle,
            'label' => Yii::t('app', 'Attributed') . ' €'
        ];
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return $data;
        } else return $this->render('sum_by_count', $data);
    }
    /**
     */
    public function actionAttributionOverOperationByAdvisor($from1 = null, $to1 = null, $label1 = null, $from2 = null, $to2 = null, $label2 = null)
    {
        list($period1, $period2) = $this->getSumByCountDefaultPeriods($from1, $to1, $label1, $from2, $to2, $label2);
        $na = mb_strtoupper(Yii::t('app', 'Direction'));
        $aux1 = ArrayHelper::index(
            Advisor::getAttributionOverOperationCount($period1['from'], $period1['to'], 'sum1_eu', 'count1', $na), 'name');
        $aux2 = ArrayHelper::index(
            Advisor::getAttributionOverOperationCount($period2['from'], $period2['to'], 'sum2_eu', 'count2', $na), 'name');
        $advisors = ArrayHelper::merge($aux1, $aux2);
        ksort($advisors);
        $title= Yii::t('app', 'Attributed/Operation by advisor');
        $subtitle = Yii::t('app', 'Average attribution per operation by advisor');
        $data = [
            'groupings' => $advisors,
            'period1' => $period1,
            'period2' => $period2,
            'title' => $title,
            'subtitle' => $subtitle,
            'label' => Yii::t('app', 'Attributed') . ' €'
        ];
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return $data;
        } else return $this->render('sum_by_count', $data);
    }
    /**
     */
    protected function getSumByCountDefaultPeriods($from1 = null, $to1 = null, $label1 = null, $from2 = null, $to2 = null, $label2 = null)
    {
        if (!$label1) $label1 = Yii::t('app', 'Current quarter');
        if (!$label2) $label2 = Yii::t('app', 'Current year');
        $current_quarter = ceil(date('n') / 3);
        $periods = [[
            'label' => $label1,
            'default_from' => date('Y-m-d', mktime(0, 0, 0, $current_quarter * 3 - 2, 1, date('Y'))),
            'default_to' => date('Y-m-d', mktime(0, 0, 0, $current_quarter * 3, date('t', mktime(0, 0, 0, $current_quarter * 3)), date('Y')))
        ], [
            'label' => $label2,
            'default_from' => date('Y-m-d', mktime(0, 0, 0, 1, 1, date('Y'))),
            'default_to' => date('Y-m-d', mktime(0, 0, 0, 12, 31, date('Y')))
        ]];
        if (!$from1) $from1 = $periods[0]['default_from'];
        else if (!$periods[0]['label'] and $from1 != $periods[0]['default_from'])
            $periods[0]['label'] = "$from1 .. $to1";

        if (!$to1) $to1 = $periods[0]['default_to'];
        else if (!$periods[0]['label'] and $to1 != $periods[0]['default_to'])
            $periods[0]['label'] = "$from1 .. $to1";

        $periods[0]['from'] = $from1;
        $periods[0]['to'] = $to1;

        if (!$from2) $from2 = $periods[1]['default_from'];
        else if (!$periods[1]['label'] and $from2 != $periods[1]['default_from'])
            $periods[1]['label'] = "$from2 .. $to2";

        if (!$to2) $to2 = $periods[1]['default_to'];
        else if (!$periods[1]['label'] and $to2 != $periods[1]['default_to'])
            $periods[1]['label'] = "$from2 .. $to2";

        $periods[1]['from'] = $from2;
        $periods[1]['to'] = $to2;

        return $periods;
    }
    protected function getDoubleSumDefaultPeriods($from = null, $to = null, $label = null)
    {
        if (!$label) $label = Yii::t('app', 'Trailing twelve months');

        if (!$from) $from = date('Y-m-d', mktime(0, 0, 0, date('m') + 1, date('d'), date('Y') - 1));
        if (!$to) $to = date('Y-m-d');

        return [
            'from' => $from,
            'to' => $to,
            'label' => $label
        ];
    }
}
