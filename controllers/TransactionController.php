<?php

namespace app\controllers;

use Yii;
use app\models\Invoice;
use app\models\Advisor;
use app\models\Attribution;
use app\models\AttributionType;
use app\models\Transaction;
use app\models\TransactionCommission;
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
        //\yii\helpers\VarDumper::dump($data, 8, true); die;
        $years = Transaction::find()->select(['to_char(payrolled_at, \'yyyy\')'])
            ->distinct()->where(['not', ['payrolled_at'=> null]])
            ->asArray()->createCommand()->queryColumn();
        $years = array_combine($years, $years);
        $invoiced_euc = Invoice::find()->with(['transaction' => function($q) use ($year) {
            $q->where(['to_char(payrolled_at, \'yyyy\')' => $year]);
        }])->sum('amount_euc');
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
            'invoiced_euc' => $invoiced_euc,
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
            'cssInline' => '.col-xs-2 { width: 12.87% } .col-xs-8 { width: 62.87%; } .col-xs-4 { width: 29.55%; } ',
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
        return $this->render('view', [
            'model' => $this->findListModel($id),
        ]);
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
     * Updates an existing TransactionListItem model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id, true);
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

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            if (Yii::$app->request->isAjax)
                return $this->actionView($model->id);
            else return $this->redirect(['view', 'id' => $model->id]);
        } else {
            $data = [
                'model' => $model,
                'invoice' => $invoice,
                'attribution' => $attribution,
                'invoiceDataProvider' => $invoiceDataProvider,
                'attributionDataProvider' => $attributionDataProvider,
                'attribution_types' => $attribution_types,
                'advisor_defaults' => $advisor_defaults,
                'total_invoiced_eu' => $total_invoiced_eu
            ];
            if (Yii::$app->request->isAjax) return $this->renderAjax('_form', $data);
            else return $this->render('update', $data);
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
        $q = TransactionCommission::find()
            ->with(['tranches' => function($q) {
                $q->orderBy('from_euc desc');
            }])->where(['like', 'payroll_month', $year]);
        if ($advisor_id) $q->where(['advisor_id' => $advisor_id]);
        $commissions = $q->orderBy('advisor_name asc, payroll_month asc, transaction_id asc')
            ->asArray()->all();
        $commissions = ArrayHelper::index($commissions, 'transaction_id', ['advisor_name', 'payroll_month']);
        $data = [];
        foreach ($commissions as $advisor => & $months) {
            $data[$advisor]['months'] = [];
            $accu[$advisor] = [];
            $accu_corrected[$advisor] = [];
            $prev = null;
            foreach ($months as $month => & $transactions) {
                $tranches = [];
                foreach ($transactions as $tr) {
                    $attr = $tr['total_attributed_sum_euc'];
                    $attr_corrected = $tr['total_attributed_sum_corrected_euc'];
                    if ($attr_corrected === null) $attr_corrected = 0;
                    if (isset($accu[$advisor][$month])) {
                        $accu[$advisor][$month] += $attr;
                        $accu_corrected[$advisor][$month] += $attr_corrected;
                    } else if ($prev !== null) {
                        $accu[$advisor][$month] = $accu[$advisor][$prev] + $attr;
                        $accu_corrected[$advisor][$month] = $accu_corrected[$advisor][$prev] + $attr_corrected;
                    } else {
                        $accu[$advisor][$month] = $attr;
                        $accu_corrected[$advisor][$month] = $attr_corrected;
                    }
                    if (!$tranches) $tranches = $tr['tranches'];
                }
                if ($prev !== null) {
                    $attr = $accu[$advisor][$month] - $accu[$advisor][$prev];
                    $attr_corrected = $accu_corrected[$advisor][$month] - $accu_corrected[$advisor][$prev];
                } else {
                    $attr = $accu[$advisor][$month];
                    $attr_corrected = $accu_corrected[$advisor][$month];
                }
                $data[$advisor]['months'][$month] = [
                    'transactions' => $transactions,
                    'accumulated_attribution_euc' => $accu[$advisor][$month],
                    'accumulated_attribution_corrected_euc' => $accu_corrected[$advisor][$month],
                    'month_attribution_euc' => $attr,
                    'month_attribution_corrected_euc' => $attr_corrected
                ];
                unset($transactions);
                $prev = $month;
                $selected_tranche = null;
                $selected_tranche_corrected = null;
                foreach ($tranches as $tranche) {
                    if ($selected_tranche === null &&
                        $tranche['from_euc'] <= $data[$advisor]['months'][$month]['accumulated_attribution_euc']) {
                        $selected_tranche = $tranche;
                    }
                    if ($selected_tranche_corrected === null &&
                        $tranche['from_euc'] <= $data[$advisor]['months'][$month]['accumulated_attribution_corrected_euc']) {
                        $selected_tranche_corrected = $tranche;
                    }
                }
                if ($selected_tranche) {
                    $data[$advisor]['months'][$month]['tranche_bp'] = $selected_tranche['commission_bp'];
                    $data[$advisor]['months'][$month]['commission_euc'] = 
                        $selected_tranche['commission_bp'] / 10000. * $data[$advisor]['months'][$month]['month_attribution_euc'];
                }
                if ($selected_tranche_corrected) {
                    $data[$advisor]['months'][$month]['tranche_corrected_bp'] = $selected_tranche_corrected['commission_bp'];
                    $data[$advisor]['months'][$month]['commission_corrected_euc'] = 
                        $selected_tranche_corrected['commission_bp'] / 10000. * $data[$advisor]['months'][$month]['month_attribution_corrected_euc'];
                }
            }
            $data[$advisor]['total_commission_euc'] = 0;
            $data[$advisor]['total_commission_corrected_euc'] = 0;
            foreach ($data[$advisor]['months'] as $month) {
                if (isset($month['commission_euc']))
                    $data[$advisor]['total_commission_euc'] += $month['commission_euc'];
                if (isset($month['commission_corrected_euc']))
                    $data[$advisor]['total_commission_corrected_euc'] += $month['commission_corrected_euc'];
            }
        }
        return $data;
    }
}
