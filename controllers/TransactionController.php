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
use app\models\EffectiveAttribution;
use app\models\TransactionListItem;
use app\models\TransactionListItemSearch;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\Response;
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
                    'actions' => ['toggle-payroll'],
                    'allow' => true,
                    'roles' => ['admin']
                ], [
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
    public function actionTogglePayroll($id)
    {
        $model = Payroll::find()->where(['id' => $id])->with('advisor.tranches')->one();
        if (isset($model->commission_bp)) $model->commission_bp = null;
        else {
            $calculated_accumulated_attribution_euc = TransactionAttributionCalculatedSummary::find()
                ->where(['advisor_id' => $model->advisor_id])
                ->andWhere(['<=', 'payroll_month', $model->month])
                ->andWhere(['>=', 'to_char(payroll_month, \'yyyy\')', substr($model->month, 0, 4)])
                ->sum('calculated_total_attributed_sum_euc');
            $commission_bp = AdvisorTranche::selectTranche(ArrayHelper::toArray($model->advisor->tranches),
                $calculated_accumulated_attribution_euc)['commission_bp'];
            $model->commission_bp = $commission_bp;
        }
        Yii::$app->response->format = Response::FORMAT_JSON; 
        if ($model->save(false)) return $model;
        else throw HttpException(500, \Yii::t('app', 'Could not toggle the payroll state'));
    }
    /**
     */
    public function actionCommissions($year = '', $advisor_id = '')
    {
        if (!Yii::$app->user->can('accounting')) throw new ForbiddenHttpException();
        if (!$year) $year = date('Y');
        $data = $this->mkCommissionsViewData($year, $advisor_id);
        $years = Payroll::find()->select(['to_char(month, \'yyyy\')'])
            ->distinct()->asArray()->createCommand()->queryColumn();
        $years = array_combine($years, $years);
        $positive_invoiced_euc = Invoice::find()->joinWith(['transaction' => function($q) use ($year) {
            $q->where(['to_char(payroll_month, \'yyyy\')' => $year]);
        }])->where('amount_euc > 0')->sum('amount_euc');
        $negative_invoiced_euc = Invoice::find()->joinWith(['transaction' => function($q) use ($year) {
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
        $data = $this->mkCommissionsViewData($year, $advisor_id, true);
        if (!$data) return null;
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
            'cssInline' => 'body { font-size: 12px; } .col-xs-3 { width: 22.0% } .col-xs-2 { width: 12.87% } .col-xs-8 { width: 62.87%; } .col-xs-4 { width: 29.55%; } ',
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
        return $this->render('view', $this->mkFormData($this->findModel($id)));
    }

    /**
     * Creates a new TransactionListItem model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        if (!Yii::$app->user->can('contracts')) throw new ForbiddenHttpException();
        $model = new Transaction();
        if (Yii::$app->user->can('admin')) $model->scenario = 'admin';
        else if (Yii::$app->user->can('accounting')) $model->scenario = 'accounting';

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            if (Yii::$app->request->isAjax)
                return 'ok';
            else return $this->redirect(['view', 'id' => $model->id]);
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
        $transaction_attribution = new EffectiveAttribution(['transaction_id' => $id]);
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
        if (Yii::$app->user->can('admin')) $model->scenario = 'admin';
        else if (Yii::$app->user->can('accounting')) $model->scenario = 'accounting';
        if (!Yii::$app->user->can('contracts') or 
            !Yii::$app->user->can('accounting') and $model->approved_by_accounting or 
            !Yii::$app->user->can('admin') and $model->approved_by_direction) throw new ForbiddenHttpException();
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
        if (!Yii::$app->user->can('contracts') or 
            !Yii::$app->user->can('accounting') and $model->approved_by_accounting or 
            !Yii::$app->user->can('admin') and $model->approved_by_direction) throw new ForbiddenHttpException();

        $model = $this->findModel($id);
        if (count($model->invoices)) return Yii::t('app', 'This transaction has invoices associated; please remove them first');
        if (count($model->attributions)) return Yii::t('app', 'This transaction has attributions associated; please remove them first');
        $model->delete();

        if (Yii::$app->request->isAjax) return 'ok';
        else return $this->redirect(['index']);
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
    protected function mkCommissionsViewData($year, $advisor_id, $only_closed = false) {
        $q = TransactionAttributionSummary::find()
            ->with('calculatedSummary')
            ->where(['to_char(payroll_month, \'yyyy\')' => $year])
            ->orderBy('advisor_name asc, payroll_month asc, transaction_id asc')->asArray();
        if ($advisor_id) $q->andWhere(['transaction_attribution_summary.advisor_id' => $advisor_id]);
        if ($only_closed) $q->innerJoinWith(['payroll' => function($q) {
            $q->where(['not', ['commission_bp' => null]]);
        }]);
        $row_set = $q->all();
        $advisor_ids = ArrayHelper::map($row_set, 'advisor_name', 'advisor_id');
        $tranches = ArrayHelper::index(AdvisorTranche::find()
            ->where(['advisor_id' => $advisor_ids])
            ->orderBy('from_euc desc')
            ->asArray()->all(), null, 'advisor_id');
        $commissions = ArrayHelper::index($row_set, 'transaction_id', ['advisor_name', 'payroll_month']);
        $data = [];
        foreach ($commissions as $advisor => & $months) {
           $advisor_id = $advisor_ids[$advisor];
           $previous_accum_attribution = 0;
           $data[$advisor]['total_commission_euc'] = 0;
           $data[$advisor]['total_compensated_euc'] = 0;
           $data[$advisor]['calculated_total_commission_euc'] = 0;
           $data[$advisor]['tranches'] = $tranches[$advisor_id];
           $calc_accum_attrib = 0;
           foreach ($months as $month => $tx_summaries) {
               $data[$advisor]['months'][$month] = [];
               $mondat = & $data[$advisor]['months'][$month];
               $mondat['transactions'] = & $months[$month]; 
               $mondat['accumulated_attribution_euc'] = 0;
               $mondat['commission_bp'] = 0;
               $mondat['compensated_euc'] = 0;
               $mondat['compensations'] = Correction::find()->joinWith(['payroll' => function($q) use ($advisor_id) {
                   $q->where(['advisor_id' => $advisor_id]);
               }])->asArray()->where([
                   'compensation_on' => $month
               ])->all();
               foreach ($mondat['compensations'] as $compensation) $mondat['compensated_euc'] += $compensation['compensation_euc'];
               $data[$advisor]['total_compensated_euc'] += $mondat['compensated_euc'];
               $payroll = Payroll::find()->with('corrections')->where([
                   'advisor_id' => $advisor_id,
                   'month' => $month
               ])->asArray()->one();
               $mondat['payroll'] = $payroll;
               $mondat['accumulated_attribution_euc'] = $previous_accum_attribution;
               foreach ($tx_summaries as $tx_summary) {
                   $mondat['commission_bp'] = $payroll['commission_bp'];
                   $mondat['accumulated_attribution_euc'] += $tx_summary['total_attributed_sum_euc'];
               }
               $mondat['payroll']['corrections_sum'] = 0;
               foreach ($mondat['payroll']['corrections'] as $correction)
                   $mondat['payroll']['corrections_sum'] += $correction['corrected_euc'];
               $mondat['attribution_euc'] = $mondat['accumulated_attribution_euc'] - $previous_accum_attribution;
               $previous_accum_attribution = $mondat['accumulated_attribution_euc'];
               $mondat['calculated_attribution_euc'] = 0;
               foreach ($mondat['transactions'] as $tx_id => $tx_summary) {
                   $mondat['transactions'][$tx_id]['attributions'] = Json::decode($mondat['transactions'][$tx_id]['attributions_json']);
                   unset($mondat['transactions'][$tx_id]['attributions_json']);
                   $mondat['transactions'][$tx_id]['invoices'] = Json::decode($mondat['transactions'][$tx_id]['invoices_json']);
                   unset($mondat['transactions'][$tx_id]['invoices_json']);
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
                               $all_diff - $mondat['attribution_euc'] * ($mondat['calculated_commission_bp'] - $mondat['simulated_commission_bp']) / 10000.;
                           $mondat['difference_causes'][Correction::LATE_INVOICE_PROPAGATION] =
                               $all_diff - $mondat['difference_causes'][Correction::TRANCHES_CHANGED];
                       }
                   }
               } else if ($mondat['accumulated_attribution_euc'] != $mondat['calculated_accumulated_attribution_euc']) {
                   if ($mondat['simulated_commission_bp'] == $mondat['commission_bp'])
                       $mondat['difference_causes'][Correction::LATE_INVOICE_PROPAGATION] = $all_diff;
                   else {
                       if ($mondat['commission_bp'] != $mondat['calculated_commission_bp'])
                           $mondat['difference_causes'][Correction::TRANCHES_CHANGED] =
                               $all_diff - $mondat['attribution_euc'] * ($mondat['calculated_commission_bp'] - $mondat['simulated_commission_bp']) / 10000.;
                       else $mondat['difference_causes'][Correction::TRANCHES_CHANGED] = 0;
                       $mondat['difference_causes'][Correction::LATE_INVOICE_PROPAGATION] =
                           $all_diff - $mondat['difference_causes'][Correction::TRANCHES_CHANGED];
                   }
               }
               if (empty($mondat['difference_causes'][Correction::TRANCHES_CHANGED]))
                   unset($mondat['difference_causes'][Correction::TRANCHES_CHANGED]);
               if (empty($mondat['difference_causes'][Correction::LATE_INVOICE_PROPAGATION]))
                   unset($mondat['difference_causes'][Correction::LATE_INVOICE_PROPAGATION]);
           }
        }
        //\yii\helpers\VarDumper::dump($data, 9, true); die;
        return $data;
    }
}
