<?php

namespace app\controllers;

use yii\data\Pagination;
use yii\data\ActiveDataProvider;
use yii\web\Response;
use app\models\Item;
use app\models\ItemValue;
use app\models\ItemExtended;
use app\models\ItemReading;
use app\models\ItemReadingExtended;
use app\models\ItemReadingGroup;
use app\models\ItemReadingGroupExtended;
use yii\helpers\ArrayHelper;
use yii\web\HttpException;
use yii\filters\AccessControl;
use yii\data\ArrayDataProvider;

class TableController extends Controller {
    public function behaviors() {
        return ArrayHelper::merge([
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [[
                    'allow' => true,
                    'actions' => ['ajax-delete-group', 'ajax-delete-item', 'action-create-table', 'ajax-update-item'],
                    'roles' => ['admin']
                ], [
                    'allow' => false,
                    'actions' => ['ajax-delete-group', 'ajax-delete-item', 'action-create-table', 'ajax-update-item'],
                    'roles' => ['@']
                ], [
                    'allow' => true,
                    'roles' => ['@']
                ]]
            ],
            'verbs' => [
                'class' => \yii\filters\VerbFilter::className(),
                'actions' => [
                    'view'   => ['get', 'post'],
                    'ajax-create-table'  => ['post'],
                    'ajax-save-readings'  => ['post'],
                    'ajax-create-item'  => ['post'],
                    'ajax-update-item'  => ['post'],
                    'ajax-delete-item'  => ['post'],
                    'ajax-delete-group'  => ['post'],
                ],
            ],
        ], parent::behaviors());
    }
    public function actionIndex() {
        $query = ItemExtended::find()->where(['parent_id' => null]);
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

        return $this->render('index.twig', [
            'dataProvider' => $dataProvider,
        ]);
    }
    public function actionAjaxCreateTable() {
        if (!\Yii::$app->user->can('admin')) throw new HttpException(403);
        \Yii::$app->response->format = Response::FORMAT_JSON;
        $request = \Yii::$app->request;
        $item = new Item();
        $item->attributes = $request->post('Item', []);
        if (!$item->save())
            throw new HttpException(422, \yii\helpers\Json::encode($item->errors));
        $this->layout = false;
        return $this->actionIndex();;
    }
    public function actionAjaxDeleteGroup($id) {
        if (!\Yii::$app->user->can('admin')) throw new HttpException(403);
        \Yii::$app->response->format = Response::FORMAT_JSON;
        $group = ItemReadingGroup::findOne($id);
        $connection = \Yii::$app->db;
        $transaction = $connection->beginTransaction();
        try {
            $res = $group->delete();
            $transaction->commit();
            return $res;
        } catch (Exception $e) {
            $transaction->rollback();
            throw $e;
        }
    }
    public function actionAjaxDeleteItem() {
        if (!\Yii::$app->user->can('admin')) throw new HttpException(403);
        \Yii::$app->response->format = Response::FORMAT_JSON;
        $request = \Yii::$app->request;
        $item = Item::findOne($request->post('id'));
        $connection = \Yii::$app->db;
        $transaction = $connection->beginTransaction();
        try {
            if ($item->parent and $item->parent->children and count($item->parent->children) == 1) {
                $aLeaf = ItemExtended::findLeaves($item->id)->one();
                foreach ($aLeaf->readings as $reading) {
                    $reading->item_id = $item->parent_id;
                    $reading->save(false);
                }
            }// else foreach ($item->readings as $reading) $reading->delete();
            $item->delete();
            $transaction->commit();
            
            $this->layout = false;
            return $this->actionIndex();
        } catch (Exception $e) {
            $transaction->rollback();
            throw $e;
        }
    }
    public function actionAjaxUpdateItem($id) {
        if (!\Yii::$app->user->can('admin')) throw new HttpException(403);
        \Yii::$app->response->format = Response::FORMAT_JSON;
        $request = \Yii::$app->request;
        $item = Item::findOne($id);
        $item->name = $request->post('name');
        $connection = \Yii::$app->db;
        $transaction = $connection->beginTransaction();
        try {
            if (!$item->save())
                throw new HttpException(422, \yii\helpers\Json::encode($item->errors));
            if ($item->values > 0) foreach ($item->values as $value) $value->delete();
            foreach ($request->post('values') as $value) {
                $itemValue = new ItemValue();
                $itemValue->value = $value;
                $itemValue->item_id = $item->id;
                $itemValue->save(false);
            }
            $itemX = ItemExtended::findOne($id);
            $transaction->commit();
            return ArrayHelper::toArray($itemX, [
                'app\models\ItemExtended' => [
                    'id',
                    'name',
                    'parent_id',
                    'path',
                    'values'
                ]
            ]);
        } catch (Exception $e) {
            $transaction->rollback();
            throw $e;
        }
    }
    public function actionAjaxCreateItem() {
        if (!\Yii::$app->user->can('admin')) throw new HttpException(403);
        \Yii::$app->response->format = Response::FORMAT_JSON;
        $request = \Yii::$app->request;
        $item = new Item();
        $sibling = Item::findOne(['parent_id' => $request->post('parent_id', null)]);
        $item->name = $request->post('name', null);
        $item->parent_id = $request->post('parent_id', null);
        $connection = \Yii::$app->db;
        $transaction = $connection->beginTransaction();
        try {
            if (!$item->save())
                throw new HttpException(422, \yii\helpers\Json::encode($item->errors));
            if ($sibling) {
                $sql = 'insert into item_reading (item_id, item_reading_group_id) values ';
                $i = 0;
                foreach (ItemExtended::findLeaves($sibling->id)->one()->readings as $reading) {
                    $i++;
                    $sql .= "($item->id, $reading->item_reading_group_id),";
                }
                if ($i > 0) {
                    $sql = substr($sql, 0, strlen($sql) - 1);
                    $command = $connection->createCommand($sql);
                    $res = $command->execute();
                    if ($res !== $i)
                        throw new HttpException(500, \Yii::t('app', 'Could not save all items'));
                }
            } else if ($item->parent) foreach ($item->parent->readings as $reading) {
                $reading->item_id = $item->id;
                $reading->save(false);
            }
            $item = ItemExtended::findOne(['id' => $item->id]);
            $transaction->commit();
        } catch (Exception $e) {
            $transaction->rollback();
            throw $e;
        }
        return $item;
    }
    public function actionAjaxSaveReadings($id = null) {
        \Yii::$app->response->format = Response::FORMAT_JSON;
        $request = \Yii::$app->request;
        if (!$id) $group = new ItemReadingGroup();
        else $group = ItemReadingGroup::findOne(['id' => $id]);
        if (!$group) throw new HttpException(404);
        $group->date_range = $request->post('Group_date_range', null);
        if (!$group->validate())
            throw new HttpException(422, \yii\helpers\Json::encode($group->errors));
        $connection = \Yii::$app->db;
        $transaction = $connection->beginTransaction();
        try {
            $sql = 'insert into item_reading (item_reading_group_id, value, item_id) values ';
            if (!$group->save(false))
                throw new HttpException(500, \Yii::t('app', 'Could not save row'));
            $readings = $request->post('ItemReading', []);
            if ($id) ItemReading::deleteAll("item_reading_group_id = $id");
            $i = 0;
            foreach ($readings as $item_id => $item_value) {
                $i++;
                $sql .= "($group->id, '$item_value', $item_id),";
            }
            if ($i > 0) {
                $sql = substr($sql, 0, strlen($sql) - 1);
                $command = $connection->createCommand($sql);
                $res = $command->execute();
                if ($res !== $i)
                    throw new HttpException(500, \Yii::t('app', 'Could not save all readings'));
            }
            $transaction->commit();
            return $res;
        } catch (Exception $e) {
            $transaction->rollback();
            throw $e;
        }
    }
    public function actionUpdate($id) {
        if (!\Yii::$app->user->can('admin')) throw new HttpException(403);
        $item = ItemExtended::findOne($id);
        $item_tree = ArrayHelper::toArray($item, [
            'app\models\ItemExtended' => [
                'id',
                'name',
                'parent_id',
                'path',
                'children',
                'values'
            ]
        ]);
        return $this->render('edit.twig', [
            'item_tree' => $item_tree
        ]);
    }
    public function actionView($id) {
        $query = ItemExtended::find()->where(['root_id' => $id])->asArray();
        $table = $this->addTreeInfo(ArrayHelper::index($query->orderBy('level,path')->all(), 'id'));
        $query = ItemReadingGroupExtended::find()->asArray() // Yii2 bug forces to do this!?
            ->innerJoin(['ire' =>
                ItemReadingExtended::find()
                    ->select(['item_reading_group_id'])
                    ->where(['root_id' => $id])
                    ->groupBy('item_reading_group_id')
            ], 'ire.item_reading_group_id = item_reading_group_extended.id')
            ->with(['itemReadingsExtended' => function($q) use ($id) {
                $q->where(['root_id' => $id]);
            }]);
        $request = \Yii::$app->request;
        if ($request->post('export_type')) {
            $pagination = null;
            $offset = 0;
            $limit = null;
        } else {
            $pagination = new Pagination([
                'defaultPageSize' => 10,
                'totalCount' => $query->count()
            ]);
            $offset = $pagination->offset;
            $limit = $pagination->limit;
        }
        $groups = $query->orderBy('from')->offset($offset)
            ->limit($limit)
            ->all();
        //
        $rawGroups = [];
        foreach ($groups as $group) {
            $rawGroups[$group['id']] = ArrayHelper::map($group['itemReadingsExtended'], 'path', 'value');
            $rawGroups[$group['id']]['from'] = $group['from'];
            $rawGroups[$group['id']]['to'] = $group['to'];
        }
        $dataProvider = new ArrayDataProvider([
            'allModels' => $rawGroups
        ]);
        $table = ArrayHelper::index($table, 'id', 'level');
        $root = array_shift($table);
        $table_title = array_values($root)[0]['name'];

        $group_model = new ItemReadingGroup();
        $items = ItemExtended::findLeaves($id)->orderBy('path')->asArray()->with('values')->all();

        return $this->render('view.twig', [
            'table_title' => $table_title,
            'dataProvider' => $dataProvider,
            'groups' => $groups,
            'items' => $items,
            'n_levels' => reset($root)['n_levels'],
            'group_model' => $group_model,
            'pagination' => $pagination,
            'table' => $table,
        ]);
    }
    protected function addTreeInfo($table) {
        $parents = [];
        $noLeaves = [];
        $levelsBelow = [];
        $maxLevel = 0;
        foreach (array_reverse($table) as $v) {
            $parents[] = $v['parent_id'];
            if ($maxLevel < $v['level']) $maxLevel = $v['level'];
            if (array_search($v['id'], $parents, true) !== false) {
                if (!isset($noLeaves[$v['parent_id']])) $noLeaves[$v['parent_id']] = $noLeaves[$v['id']];
                else $noLeaves[$v['parent_id']] += $noLeaves[$v['id']];
                $levelsBelow[$v['parent_id']] = $levelsBelow[$v['id']] + 1;
            } else {
                $noLeaves[$v['id']] = 1;
                if (!isset($noLeaves[$v['parent_id']])) $noLeaves[$v['parent_id']] = 1;
                else $noLeaves[$v['parent_id']] += 1;
                $levelsBelow[$v['parent_id']] = 1;
                $levelsBelow[$v['id']] = 0;
            }
        }
        foreach ($table as &$v) {
            $v['no_leaves'] = $noLeaves[$v['id']];
            if (array_search($v['id'], $parents, true) === false)
                $v['span_levels'] = $maxLevel - $levelsBelow[$v['id']] - $v['level'] + 1;
        }
        reset($table);
        $table[key($table)]['n_levels'] = $maxLevel;

        return $table;
    }
}
