<?php

namespace app\controllers;

use Yii;
use app\models\Invoice;
use app\models\Advisor;
use app\models\AdvisorTranche;
use app\models\Attribution;
use app\models\AttributionType;
use app\models\Transaction;
use app\models\TransactionAttributionSummary;
use app\models\TransactionAttributionCorrectedSummary;
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
    const TRANCHES_CHANGED = 0;
    const LATE_INVOICE_PROPAGATION = 1;
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
        $years = Transaction::find()->select(['to_char(payrolled_at, \'yyyy\')'])
            ->distinct()->where(['not', ['payrolled_at'=> null]])
            ->asArray()->createCommand()->queryColumn();
        $years = array_combine($years, $years);
        $positive_invoiced_euc = Invoice::find()->with(['transaction' => function($q) use ($year) {
            $q->where(['to_char(payrolled_at, \'yyyy\')' => $year]);
        }])->where('amount_euc > 0')->sum('amount_euc');
        $negative_invoiced_euc = Invoice::find()->with(['transaction' => function($q) use ($year) {
            $q->where(['to_char(payrolled_at, \'yyyy\')' => $year]);
        }])->where('amount_euc < 0')->sum('amount_euc');
        $our_fees_euc = Transaction::find()
            ->where(['to_char(payrolled_at, \'yyyy\')' => $year])
            ->sum('our_fee_euc');
        $their_fees_euc = Transaction::find()
            ->where(['to_char(payrolled_at, \'yyyy\')' => $year])
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
                return $this->actionView($model->id);
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
            ->with('correctedSummary')
            ->where(['to_char(payrolled_at, \'yyyy\')' => $year])
            ->orderBy('advisor_name asc, payrolled_at asc, transaction_id asc')->asArray();
        if ($advisor_id) $q->andWhere(['advisor_id' => $advisor_id]);
        $row_set = $q->all();
        $advisor_ids = ArrayHelper::getColumn($row_set, 'advisor_id');
        $tranches = ArrayHelper::index(AdvisorTranche::find()
            ->where(['advisor_id' => $advisor_ids])
            ->orderBy('from_euc desc')
            ->asArray()->all(), null, 'advisor_id');
        $commissions = ArrayHelper::index($row_set, 'transaction_id', ['advisor_name', 'payrolled_at']);
        $data = [];
        foreach ($commissions as $advisor => & $months) {
           $previous_accum_attribution = 0;
           $previous_correction_causes = [];
           $data[$advisor]['total_commission_euc'] = 0;
           $data[$advisor]['corrected_total_commission_euc'] = 0;
           foreach ($months as $month => & $tx_summaries) {
               $mondat = & $data[$advisor]['months'][$month];
               $mondat['transactions'] = & $tx_summaries; 
               $a_tx_summary= reset($tx_summaries);
               $data[$advisor]['tranches'] = $tranches[$a_tx_summary['advisor_id']];
               $an_attribution = Attribution::find()->with('transactionPayroll')
                   ->where(['transaction_id' => $a_tx_summary['transaction_id']])
                   ->andWhere(['advisor_id' => $a_tx_summary['advisor_id']])->one();
               $mondat['commission_bp'] = $an_attribution->transactionPayroll->commission_bp;
               $mondat['accumulated_attribution_euc'] = $an_attribution->transactionPayroll->accumulated_euc;
               $mondat['attribution_euc'] = $mondat['accumulated_attribution_euc'] -
                       $previous_accum_attribution;
               $mondat['commission_euc'] = $mondat['attribution_euc'] * $mondat['commission_bp'] / 10000.;
               $data[$advisor]['total_commission_euc'] += $mondat['attribution_euc'] *
                       $mondat['commission_bp'] / 10000.;
               $previous_accum_attribution = $mondat['accumulated_attribution_euc'];
               $mondat['corrected_attribution_euc'] = 0;
               foreach ($tx_summaries as & $tx_summary) {
                   $tx_summary['corrected_total_attributed_sum_euc'] =
                       $tx_summary['correctedSummary']['corrected_total_attributed_sum_euc'];
                   unset($tx_summary['correctedSummary']);
                   $mondat['corrected_attribution_euc'] += $tx_summary['corrected_total_attributed_sum_euc'];
               }
               $mondat['corrected_accumulated_attribution_euc'] =
                   TransactionAttributionCorrectedSummary::find()
                       ->select(['sum(corrected_total_attributed_sum_euc)'])
                       ->where(['to_char(payrolled_at, \'yyyy\')' => $year])
                       ->andWhere(['advisor_id' => $a_tx_summary['advisor_id']])
                       ->andWhere(['<=', 'payrolled_at', $month])
                       ->groupBy(['to_char(payrolled_at, \'yyyy\')'])->createCommand()->queryColumn()[0];
               $mondat['corrected_commission_bp'] = AdvisorTranche::selectTranche($data[$advisor]['tranches'],
                       $mondat['corrected_accumulated_attribution_euc'])['commission_bp'];
               $mondat['simulated_commission_bp'] = AdvisorTranche::selectTranche($data[$advisor]['tranches'],
                       $mondat['accumulated_attribution_euc'])['commission_bp'];
               $mondat['corrected_commission_euc'] = $mondat['corrected_attribution_euc'] *
                       $mondat['corrected_commission_bp'] / 10000.;
               $data[$advisor]['corrected_total_commission_euc'] += $mondat['corrected_attribution_euc'] *
                       $mondat['corrected_commission_bp'] / 10000.;
               $mondat['correction_causes'] = [];
               if ($mondat['commission_bp'] != $mondat['corrected_commission_bp']) {
                   if ($mondat['accumulated_attribution_euc'] ==
                       $mondat['corrected_accumulated_attribution_euc'])
                       $mondat['correction_causes'] = [self::TRANCHES_CHANGED];
                   else {
                       if ($mondat['simulated_commission_bp'] == $mondat['commission_bp'])
                           $mondat['correction_causes'] = [self::LATE_INVOICE_PROPAGATION];
                       else {
                           $mondat['correction_causes'] = [self::TRANCHES_CHANGED];
                           if (array_search($previous_correction_causes,
                               self::LATE_INVOICE_PROPAGATION) !== false)
                               $mondat['correction_causes'][] = self::LATE_INVOICE_PROPAGATION;
                       }
                   }
               } else if ($mondat['accumulated_attribution_euc'] !=
                   $mondat['corrected_accumulated_attribution_euc']) {
                   if ($mondat['simulated_commission_bp'] == $mondat['commission_bp'])
                       $mondat['correction_causes'] = [self::LATE_INVOICE_PROPAGATION];
                   else {
                       $mondat['correction_causes'] = [self::TRANCHES_CHANGED];
                       if (array_search($previous_correction_causes, self::LATE_INVOICE_PROPAGATION) !== false)
                           $mondat['correction_causes'][] = self::LATE_INVOICE_PROPAGATION;
                   }
               }
               $previous_correction_causes = $mondat['correction_causes'];
           }
        }
        return $data;
    }
}
