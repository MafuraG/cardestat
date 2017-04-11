<?php

namespace app\controllers;

use Yii;
use app\models\Invoice;
use app\models\Advisor;
use app\models\Payroll;
use app\models\AdvisorTranche;
use app\models\Attribution;
use app\models\AttributionType;
use app\models\Correction;
use app\models\Transaction;
use app\models\TransactionAttributionSummary;
use app\models\TransactionAttributionCalculatedSummary;
use app\models\TransactionAttribution;
use app\models\TransactionListItem;
use app\models\TransactionListItemSearch;
use yii\helpers\ArrayHelper;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use kartik\mpdf\Pdf;

/**
 * TransactionController implements the CRUD actions for TransactionListItem model.
 */
class TransactionController extends Controller
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
                    'roles' => ['@']
                ]]
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }
    /**
     */
    public function actionCommissions($year = '', $advisor_id = '')
    {
        if (!$year) $year = date('Y');
        $data = $this->mkCommissionsViewData($year, $advisor_id);
        $years = Transaction::find()->select(['to_char(payroll_month, \'yyyy\')'])
            ->distinct()->where(['not', ['payroll_month'=> null]])
            ->asArray()->createCommand()->queryColumn();
        $years = array_combine($years, $years);
        $positive_invoiced_euc = Invoice::find()->with(['transaction' => function($q) use ($year) {
            $q->where(['to_char(payroll_month, \'yyyy\')' => $year]);
        }])->where('amount_euc > 0')->sum('amount_euc');
        $negative_invoiced_euc = Invoice::find()->with(['transaction' => function($q) use ($year) {
            $q->where(['to_char(payroll_month, \'yyyy\')' => $year]);
        }])->where('amount_euc < 0')->sum('amount_euc');
        $our_fees_euc = Transaction::find()
            ->where(['to_char(payroll_month, \'yyyy\')' => $year])
            ->sum('our_fee_euc');
        $their_fees_euc = Transaction::find()
            ->where(['to_char(payroll_month, \'yyyy\')' => $year])
            ->sum('their_fee_euc');
        return $this->render('commissions', [
            'data' => $data,
            'year' => $year,
            'advisor_id' => $advisor_id,
            'years' => $years,
            'positive_invoiced_euc' => $positive_invoiced_euc,
            'negative_invoiced_euc' => $negative_invoiced_euc,
            'our_fees_euc' => $our_fees_euc,
            'their_fees_euc' => $their_fees_euc
        ]);
    }

    public function actionPrintCommissions($year = '', $advisor_id = '') {
        $this->layout = 'print';
        if (!$year) $year = date('Y');
        $data = $this->mkCommissionsViewData($year, $advisor_id);
        $content = $this->render('_commission_tables', [
            'data' => $data,
            'year' => $year,
            'expanded' => true
        ]);
        $pdf = new Pdf([
            'mode' => Pdf::MODE_CORE, 
            'format' => Pdf::FORMAT_A4, // width 210mm
            'orientation' => Pdf::ORIENT_LANDSCAPE, 
            'destination' => Pdf::DEST_BROWSER, 
            'marginTop' => 10,
            'marginBottom' => 6,
            'marginLeft' => 3,
            'marginRight' => 2,
            'cssInline' => '.col-xs-3 { width: 22.0% } .col-xs-2 { width: 12.87% } .col-xs-8 { width: 62.87%; } .col-xs-4 { width: 29.55%; } ',
            'content' => $content,   
        ]);
        return $pdf->render();
    }
    /**
     * Lists all TransactionListItem models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new TransactionListItemSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider
        ]);
    }

    /**
     * Displays a single TransactionListItem model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        return $this->render('view', $this->mkFormData($this->findListModel($id)));
    }

    /**
     * Creates a new TransactionListItem model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Transaction();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     */
    protected function mkFormData($model) {
        $id = $model->id;
        $invoice = new Invoice(['transaction_id' => $id]);
        $query = Invoice::find()->where(['transaction_id' => $id]);
        $invoiceDataProvider = new ActiveDataProvider([
            'query' => $query
        ]);
        $total_invoiced_eu = $query->sum('amount_euc') / 100.;
        $attribution = new Attribution(['transaction_id' => $id]);
        $transaction_attribution = new TransactionAttribution(['transaction_id' => $id]);
        $attributionDataProvider = new ActiveDataProvider([
            'query' => $transaction_attribution->find()->where(['transaction_id' => $id])
        ]);
        $advisor_defaults = ArrayHelper::index(Advisor::find()->with('defaultAttributionType')->asArray()->all(), 'id');
        $attribution_types = ArrayHelper::map(AttributionType::find()->all(), 'id', 'attribution_bp');
        return [
            'model' => $model,
            'invoice' => $invoice,
            'attribution' => $attribution,
            'invoiceDataProvider' => $invoiceDataProvider,
            'attributionDataProvider' => $attributionDataProvider,
            'attribution_types' => $attribution_types,
            'advisor_defaults' => $advisor_defaults,
            'total_invoiced_eu' => $total_invoiced_eu
        ];
    }
    public function actionUpdate($id)
    {
        $model = $this->findModel($id, true);
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            if (Yii::$app->request->isAjax)
                return 'ok';
            else return $this->redirect(['view', 'id' => $model->id]);
        } else {
            $data = $this->mkFormData($model);
            return $this->render('update', $data);
        }
    }

    /**
     * Deletes an existing TransactionListItem model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the TransactionListItem model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return TransactionListItem the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findListModel($id)
    {
        if (($model = TransactionListItem::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    protected function findModel($id, $with_related = false)
    {
        if ($with_related) {
            if (($model = Transaction::find()
                ->with(['invoices', 'attributions.advisor', 'attributions.attributionType'])
                ->where(['id' => $id])
                ->one()) !== null) return $model;
            else throw new NotFoundHttpException('The requested page does not exist.');
        } else if (($model = Transaction::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    /**
     */
    protected function mkCommissionsViewData($year, $advisor_id) {
        $q = TransactionAttributionSummary::find()
            ->with('calculatedSummary')
            ->where(['to_char(payroll_month, \'yyyy\')' => $year])
            ->orderBy('advisor_name asc, payroll_month asc, transaction_id asc')->asArray();
        if ($advisor_id) $q->andWhere(['advisor_id' => $advisor_id]);
        $row_set = $q->all();
        $advisor_ids = ArrayHelper::map($row_set, 'advisor_name', 'advisor_id');
        $tranches = ArrayHelper::index(AdvisorTranche::find()
            ->where(['advisor_id' => $advisor_ids])
            ->orderBy('from_euc desc')
            ->asArray()->all(), null, 'advisor_id');
        $commissions = ArrayHelper::index($row_set, 'transaction_id', ['advisor_name', 'payroll_month']);
        $data = [];
        foreach ($commissions as $advisor => & $months) {
           $previous_accum_attribution = 0;
           $previous_difference_causes = [];
           $data[$advisor]['total_commission_euc'] = 0;
           $data[$advisor]['calculated_total_commission_euc'] = 0;
           $data[$advisor]['tranches'] = $tranches[$advisor_ids[$advisor]];
           $calc_accum_attrib = 0;
           foreach ($months as $month => $tx_summaries) {
               $data[$advisor]['months'][$month] = [];
               $mondat = & $data[$advisor]['months'][$month];
               $mondat['transactions'] = & $months[$month]; 
               $mondat['accumulated_attribution_euc'] = 0;
               $mondat['commission_bp'] = 0;
               $mondat['compensated_euc'] = 0;
               $mondat['compensations'] = Correction::find()->with('payroll')->asArray()->where([
                   'compensation_on' => $month
               ])->all();
               foreach ($mondat['compensations'] as $compensation) $mondat['compensated_euc'] += $compensation['compensation_euc'];
               $payroll = Payroll::find()->with('corrections')->where([
                   'advisor_id' => $advisor_ids[$advisor],
                   'month' => $month
               ])->one();
               $mondat['payroll_id'] = $payroll->id;
               $mondat['accumulated_attribution_euc'] = $previous_accum_attribution;
               foreach ($tx_summaries as $tx_summary) {
                   $mondat['commission_bp'] = $payroll->commission_bp;
                   $mondat['accumulated_attribution_euc'] += $tx_summary['total_attributed_sum_euc'];
               }
               $mondat['corrections'] = ['sum' => 0, 'rows' => ArrayHelper::toArray($payroll->corrections)];
               foreach ($mondat['corrections']['rows'] as $correction) $mondat['corrections']['sum'] += $correction['corrected_euc'];
               $mondat['attribution_euc'] = $mondat['accumulated_attribution_euc'] - $previous_accum_attribution;
               $previous_accum_attribution = $mondat['accumulated_attribution_euc'];
               $mondat['calculated_attribution_euc'] = 0;
               foreach ($mondat['transactions'] as $tx_id => $tx_summary) {
                   $tx_summary['calculated_total_attributed_sum_euc'] =
                       $tx_summary['calculatedSummary']['calculated_total_attributed_sum_euc'];
                   unset($mondat['transactions'][$tx_id]['calculatedSummary']);
                   $mondat['calculated_attribution_euc'] += $tx_summary['calculated_total_attributed_sum_euc'];
               }
               $calc_accum_attrib += $mondat['calculated_attribution_euc'];
               $mondat['calculated_accumulated_attribution_euc'] = $calc_accum_attrib;
               $mondat['calculated_commission_bp'] = AdvisorTranche::selectTranche($data[$advisor]['tranches'],
                       $mondat['calculated_accumulated_attribution_euc'])['commission_bp'];
               if (!$mondat['commission_bp']) $mondat['commission_bp'] = $mondat['calculated_commission_bp'];
               $mondat['commission_euc'] = $mondat['attribution_euc'] * $mondat['commission_bp'] / 10000.;
               $data[$advisor]['total_commission_euc'] += $mondat['attribution_euc'] * $mondat['commission_bp'] / 10000.;
               $mondat['simulated_commission_bp'] = AdvisorTranche::selectTranche($data[$advisor]['tranches'],
                       $mondat['accumulated_attribution_euc'])['commission_bp'];
               $mondat['calculated_commission_euc'] = $mondat['calculated_attribution_euc'] *
                       $mondat['calculated_commission_bp'] / 10000.;
               $data[$advisor]['calculated_total_commission_euc'] += $mondat['calculated_attribution_euc'] *
                       $mondat['calculated_commission_bp'] / 10000.;
               $mondat['difference_causes'] = [];
               $all_diff = $mondat['calculated_commission_euc'] - $mondat['commission_euc'];
               if ($mondat['commission_bp'] != $mondat['calculated_commission_bp']) {
                   if ($mondat['accumulated_attribution_euc'] == $mondat['calculated_accumulated_attribution_euc'])
                       $mondat['difference_causes'][Correction::TRANCHES_CHANGED] = $all_diff;
                   else {
                       if ($mondat['simulated_commission_bp'] == $mondat['commission_bp'])
                           $mondat['difference_causes'][Correction::LATE_INVOICE_PROPAGATION] = $all_diff;
                       else {
                           $mondat['difference_causes'][Correction::TRANCHES_CHANGED] =
                               $all_diff - $mondat['calculated_accumulated_attribution_euc'] * $mondat['commission_bp'] / 10000.;
                           if (empty($previous_difference_causes) or
                               array_search(Correction::LATE_INVOICE_PROPAGATION, $previous_difference_causes) !== false)
                               $mondat['difference_causes'][Correction::LATE_INVOICE_PROPAGATION] =
                                   $all_diff - $mondat['difference_causes'][Correction::TRANCHES_CHANGED];
                       }
                   }
               } else if ($mondat['accumulated_attribution_euc'] != $mondat['calculated_accumulated_attribution_euc']) {
                   if ($mondat['simulated_commission_bp'] == $mondat['commission_bp'])
                       $mondat['difference_causes'][Correction::LATE_INVOICE_PROPAGATION] = $all_diff;
                   else {
                       $mondat['difference_causes'][Correction::TRANCHES_CHANGED] =
                           $all_diff - $mondat['calculated_accumulated_attribution_euc'] * $mondat['commission_bp'] / 10000.;
                       if (empty($previous_difference_causes) or
                           array_search(Correction::LATE_INVOICE_PROPAGATION, $previous_difference_causes) !== false)
                           $mondat['difference_causes'][Correction::LATE_INVOICE_PROPAGATION] =
                                   $all_diff - $mondat['difference_causes'][Correction::TRANCHES_CHANGED];
                   }
               }
               $previous_difference_causes = $mondat['difference_causes'];
           }
        }
        //\yii\helpers\VarDumper::dump($data, 7, true); die;
        return $data;
    }
}
