<?php

use yii\db\Migration;

class m170221_133733_configuration extends Migration
{
    public function safeUp() {
        $this->createTable('configuration', [
            'id' => $this->primaryKey(),
            'category' => $this->string(32) . ' not null',
            'name' => $this->string(32) . ' not null',
            'value' => $this->string(256),
        ]);
        $this->createIndex('idx-unique-category-name', 'configuration', ['category', 'name']);
        $this->batchInsert('configuration', ['category', 'name', 'value'], [[
            'NSALES_ACCU_GOAL',
            'january',
            '7',
        ], [
            'NSALES_ACCU_GOAL',
            'february',
            '9',
        ], [
            'NSALES_ACCU_GOAL',
            'march',
            '11',
        ], [
            'NSALES_ACCU_GOAL',
            'april',
            '10',
        ], [
            'NSALES_ACCU_GOAL',
            'may',
            '5',
        ], [
            'NSALES_ACCU_GOAL',
            'june',
            '3',
        ], [
            'NSALES_ACCU_GOAL',
            'july',
            '0',
        ], [
            'NSALES_ACCU_GOAL',
            'august',
            '0',
        ], [
            'NSALES_ACCU_GOAL',
            'september',
            '0',
        ], [
            'NSALES_ACCU_GOAL',
            'october',
            '0',
        ], [
            'NSALES_ACCU_GOAL',
            'november',
            '0',
        ], [
            'NSALES_ACCU_GOAL',
            'december',
            '0',
        ]]);
        $this->batchInsert('configuration', ['category', 'name', 'value'], [[
            'NSALES_ACCU_ACTUAL',
            'january',
            '7',
        ], [
            'NSALES_ACCU_ACTUAL',
            'february',
            '9',
        ], [
            'NSALES_ACCU_ACTUAL',
            'march',
            '3',
        ], [
            'NSALES_ACCU_ACTUAL',
            'april',
            '8',
        ], [
            'NSALES_ACCU_ACTUAL',
            'may',
            '9',
        ], [
            'NSALES_ACCU_ACTUAL',
            'june',
            '10',
        ], [
            'NSALES_ACCU_ACTUAL',
            'july',
            '2',
        ], [
            'NSALES_ACCU_ACTUAL',
            'august',
            '1',
        ], [
            'NSALES_ACCU_ACTUAL',
            'september',
            '0',
        ], [
            'NSALES_ACCU_ACTUAL',
            'october',
            '0',
        ], [
            'NSALES_ACCU_ACTUAL',
            'november',
            '0',
        ], [
            'NSALES_ACCU_ACTUAL',
            'december',
            '0',
        ]]);
    }

    public function safeDown() {
        $this->dropTable('configuration');
    }
}
