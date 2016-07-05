<?php

namespace app\controllers;

use yii\web\Controller;
use yii\data\Pagination;
use app\models\ItemExtended;
use yii\helpers\ArrayHelper;

class TableController extends Controller
{
    public function actionIndex() {
        $query = ItemExtended::find()->where(['parent_id' => null]);
        $pagination = new Pagination([
            'defaultPageSize' => 5,
            'totalCount' => $query->count(),
        ]);
        $items = $query->orderBy('name')
            ->offset($pagination->offset)
            ->limit($pagination->limit)
            ->all();

        return $this->render('index.twig', [
            'items' => $items,
            'pagination' => $pagination,
        ]);
    }
    public function actionView($id)
    {
        $query = ItemExtended::find(['root_id' => $id]);
        $table = $this->addLeafCountInfo(ArrayHelper::toArray($query->orderBy('level,path')->all()));
        $table = ArrayHelper::index($table, 'id', 'level');
        /*
        echo '<br/><br/><br/><br/><br/>';
        \yii\helpers\VarDumper::dump($table, 5, true);
        */
        $root = array_shift($table);
        $table_title = array_values($root)[0]['name'];

        return $this->render('view.twig', [
            'table_title' => $table_title,
            'table' => $table,
        ]);
    }
    protected function addLeafCountInfo($table) {
        $parents = [];
        $noLeaves = [];
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

        return $table;
    }
}
