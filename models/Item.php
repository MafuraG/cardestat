<?php

namespace app\models;

use yii\db\ActiveRecord;

class Item extends ActiveRecord {
    private $descendants;
    public function getChildren() {
        return $this->hasMany(Item::className(), ['parent_id' => 'id']);
    }
    public function getReadings() {
        return $this->hasMany(ItemReading::className());
    }
    public function getDescendants() {
        $res = $this->getChildren()->all();
        foreach ($res as &$ch) {
            $ch->descendants = $ch->getDescendants();
        }
        return $res;
    }
    public static function findLeaves() {
        return static::find()
            ->leftJoin('item child', 'child.parent_id = item.id')
            ->where('child.id is null');
    }
}
