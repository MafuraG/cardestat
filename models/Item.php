<?php

namespace app\models;

use yii\db\ActiveRecord;

class Item extends ActiveRecord {
    protected $children;
    public function rules() {
        return [
            ['name', 'required'],
            ['name', 'string', 'max' => 48],
        ];
    }
    public static function primaryKey() {
        return ['id'];
    }
    public function getParent() {
        return $this->hasOne(Item::className(), ['id' => 'parent_id']);
    }
    public function getChildren() {
        return $this->hasMany(Item::className(), ['parent_id' => 'id']);
    }
    public function getValues() {
        return $this->hasMany(ItemValue::className(), ['item_id' => 'id']);
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
    
}
