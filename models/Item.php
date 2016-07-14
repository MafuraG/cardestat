<?php

namespace app\models;

use yii\db\ActiveRecord;

class Item extends ActiveRecord {
    protected $children;
    public static function primaryKey() {
        return ['id'];
    }
    public function getParent() {
        return $this->hasOne(Item::className(), ['id' => 'parent_id']);
    }
    public function getChildren() {
        return $this->hasMany(Item::className(), ['parent_id' => 'id']);
    }
    public function getReadings() {
        return $this->hasMany(ItemReading::className(), ['item_id' => 'id']);
    }
    public function getDescendants() {
        $res = $this->getChildren()->all();
        foreach ($res as &$ch) {
            $ch->children = $ch->getDescendants();
        }
        return $res;
    }
    public static function findLeaves() {
        $t = self::tableName();
        return static::find()
            ->leftJoin('item child', "child.parent_id = $t.id")
            ->where('child.id is null');
    }
}
