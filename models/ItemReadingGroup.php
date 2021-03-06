<?php

namespace app\models;

use yii\db\ActiveRecord;

class ItemReadingGroup extends ActiveRecord {
    public $date_range;
    public function rules() {
        return [
            ['date_range', 'required'],
            [
                'date_range',
                'match',
                'pattern' => '/^\d{4}-\d{2}-\d{2} - \d{4}-\d{2}-\d{2}$/',
                'message' => \Yii::t('app', 'This does not look like a valid date range')
            ]
        ];
    }
    public function getReadings() {
        return $this->hasMany(ItemReading::className(), ['item_reading_group_id' => 'id']);
    }
    public function getItemReadingsExtended() {
        return $this->hasMany(ItemReadingExtended::className(), ['item_reading_group_id' => 'id'])
            ->orderBy(['path' => SORT_ASC]);
    }
    public function afterValidate() {
        parent::afterValidate();
        $this->from = substr($this->date_range, 0, 10);
        $this->to = substr($this->date_range, 12, 22);
        if (!$this->created_at) {
            $this->created_at = date('Y-m-d H:i:s');
            $this->created_by = \Yii::$app->user->id;
        }
        $this->updated_at = date('Y-m-d H:i:s');
        $this->updated_by = \Yii::$app->user->id;
    }
}
