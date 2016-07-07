<?php

namespace app\models;

use yii\db\ActiveRecord;

class ItemReading extends ActiveRecord {
    public function rules() {
        return [
            [['item_reading_group_id', 'item_id', 'count'], 'integer', 'min' => 0],
            [['item_reading_group_id', 'item_id'], 'required'],
            [
                'item_reading_group_id',
                'exist',
                'targetClass' => ItemReadingGroup::className(),
                'targetAttribute' => 'id'
            ],
            [
                'item_id',
                'exist',
                'targetClass' => Item::className(),
                'targetAttribute' => 'id'
            ]
        ];
    }
    public function getItemExtended() {
        return $this->hasOne(ItemExtended::className(), ['id' => 'item_id']);
    }
    public function getItemGroup() {
        return $this->hasOne(ItemGroup::className(), ['id' => 'item_group_id']);
    }
}
