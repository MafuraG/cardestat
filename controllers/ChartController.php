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
                    'roles' => ['admin']
                ]]
            ],
        ];
    }
    /**
     */
    public function actionVolume($from = null, $to = null, $interval_months = 1, $transaction_type = null)
    {
        extract($this->getDoubleSumDefaultPeriods($from, $to));
        $aux1 = ArrayHelper::index(Transaction::getVolume($from, $to, $interval_months, $transaction_type, 'sum1_eu'), 'period');
        $aux2 = ArrayHelper::index(Invoice::getRevenue($from, $to, $interval_months, $transaction_type, 'sum2_eu'), 'period');
        $turnover = ArrayHelper::merge($aux1, $aux2);
        //\yii\helpers\VarDumper::dump($turnover, 6, true); die;
        $intervals = [
            1 => Yii::t('app', 'Monthly'),
            3 => Yii::t('app', 'Quarterly'),
            12 => Yii::t('app', 'Yearly'),
        ];
        $title= Yii::t('app', 'Transactions vs. Revenue');
        $data = [
            'sums' => $turnover,
            'from' => $from,
            'to' => $to,
            'period' => $label,
            'interval_months' => $interval_months,
            'intervals' => $intervals,
            'transaction_type' => $transaction_type,
            'label1' => Yii::t('app', 'Transactions'),
            'label2' => Yii::t('app', 'Revenue'),
            'title' => $title
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
        $aux1 = ArrayHelper::index(
            Office::getProratedOperationCount($period1['from'], $period1['to'], 'sum1_eu', 'count1'), 'name');
        $aux2 = ArrayHelper::index(
            Office::getProratedOperationCount($period2['from'], $period2['to'], 'sum2_eu', 'count2'), 'name');
        $offices = ArrayHelper::merge($aux1, $aux2);
        ksort($offices);
        $title= Yii::t('app', 'Prorated Operations by office');
        $data = [
            'groupings' => $offices,
            'period1' => $period1,
            'period2' => $period2,
            'title' => $title
        ];
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return $data;
        } else return $this->render('sum_by_count', $data);
    }
    /**
     */
    public function actionAttributionByOffice($from1 = null, $to1 = null, $label1 = null, $from2 = null, $to2 = null, $label2 = null)
    {
        list($period1, $period2) = $this->getSumByCountDefaultPeriods($from1, $to1, $label1, $from2, $to2, $label2);
        $aux1 = ArrayHelper::index(
            Office::getAttributionSum($period1['from'], $period1['to'], 'sum1_eu', 'count1'), 'name');
        $aux2 = ArrayHelper::index(
            Office::getAttributionSum($period2['from'], $period2['to'], 'sum2_eu', 'count2'), 'name');
        $offices = ArrayHelper::merge($aux1, $aux2);
        ksort($offices);
        $title= Yii::t('app', 'Attributions by office');
        $data = [
            'groupings' => $offices,
            'period1' => $period1,
            'period2' => $period2,
            'title' => $title
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
        $aux1 = ArrayHelper::index(
            Office::getAttributionOverOperationCount($period1['from'], $period1['to'], 'sum1_eu', 'count1'), 'name');
        $aux2 = ArrayHelper::index(
            Office::getAttributionOverOperationCount($period2['from'], $period2['to'], 'sum2_eu', 'count2'), 'name');
        $offices = ArrayHelper::merge($aux1, $aux2);
        ksort($offices);
        $title= Yii::t('app', 'Attributions/Operation by office');
        $data = [
            'groupings' => $offices,
            'period1' => $period1,
            'period2' => $period2,
            'title' => $title
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
        $data = [
            'groupings' => $advisors,
            'period1' => $period1,
            'period2' => $period2,
            'title' => $title
        ];
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return $data;
        } else return $this->render('sum_by_count', $data);
    }
    /**
     */
    public function actionAttributionByAdvisor($from1 = null, $to1 = null, $label1 = null, $from2 = null, $to2 = null, $label2 = null)
    {
        list($period1, $period2) = $this->getSumByCountDefaultPeriods($from1, $to1, $label1, $from2, $to2, $label2);
        $aux1 = ArrayHelper::index(
            Advisor::getAttributionSum($period1['from'], $period1['to'], 'sum1_eu', 'count1'), 'name');
        $aux2 = ArrayHelper::index(
            Advisor::getAttributionSum($period2['from'], $period2['to'], 'sum2_eu', 'count2'), 'name');
        $advisors = ArrayHelper::merge($aux1, $aux2);
        $title= Yii::t('app', 'Attributions by advisor');
        ksort($advisors);
        $data = [
            'groupings' => $advisors,
            'period1' => $period1,
            'period2' => $period2,
            'title' => $title
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
        $aux1 = ArrayHelper::index(
            Advisor::getAttributionOverOperationCount($period1['from'], $period1['to'], 'sum1_eu', 'count1'), 'name');
        $aux2 = ArrayHelper::index(
            Advisor::getAttributionOverOperationCount($period2['from'], $period2['to'], 'sum2_eu', 'count2'), 'name');
        $advisors = ArrayHelper::merge($aux1, $aux2);
        ksort($advisors);
        $title= Yii::t('app', 'Attributions/Operation by advisor');
        $data = [
            'groupings' => $advisors,
            'period1' => $period1,
            'period2' => $period2,
            'title' => $title
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
        if (!$label1) $label1 = Yii::t('app', 'Current month');
        if (!$label2) $label2 = Yii::t('app', 'Current year');
        $periods = [[
            'label' => $label1,
            'default_from' => date('Y-m-d', mktime(0, 0, 0, date('m'), 1, date('Y'))),
            'default_to' => date('Y-m-d', mktime(0, 0, 0, date('m'), date('t'), date('Y')))
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
