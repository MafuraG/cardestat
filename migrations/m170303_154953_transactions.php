<?php

use yii\db\Migration;

class m170303_154953_transactions extends Migration
{
    public function safeUp() {
        $this->createTable('transaction_type', [
            'name' => $this->string(32) . ' primary key'
        ]);
        $this->batchInsert('transaction_type', ['name'], [['TRADING'], ['RENTAL'], ['ADVICE']]);
        $this->createTable('custom_type', [
            'name' => $this->string(32) . ' primary key'
        ]);
        $this->batchInsert('custom_type', ['name'], [['NON EXCLUSIVE'], ['MULTIEXCLUSIVE'], ['EXCLUSIVE']]);
        $this->createTable('transfer_type', [
            'name' => $this->string(32) . ' primary key'
        ]);
        $this->batchInsert('custom_type', ['name'], [['FIRST HAND'], ['SECOND HAND']]);
        $this->createTable('development_type', [
            'name' => $this->string(32) . ' primary key'
        ]);
        $this->batchInsert('development_type', ['name'], [['PRIVATE']]);
        $this->createTable('partner', [
            'name' => $this->string(32) . ' primary key'
        ]);
        $this->batchInsert('partner', ['name'], [['CANARINVEST'], ['REAL INVEST'], ['DREAM HOMES']]);
        $this->createTable('lead_type', [
            'name' => $this->string(18) . ' primary key'
        ]);
        $this->batchInsert('lead_type', ['name'], [['A'], ['A+'], ['B'], ['B+'], ['C'], ['C+'], ['NC'], ['NC+'], ['500+']]);
        $this->createTable('office', [
            'name' => $this->string(18) . ' primary key'
        ]);
        $this->batchInsert('office', ['name'], [['PUERTO RICO'], ['PUERTO DE MOGÁN'], ['ARGUINEGUÍN']]);
        $this->createTable('attribution_type', [
            'id' => $this->primaryKey(),
            'name' => $this->string(32)->notNull(),
            'attribution_per10000' => $this->integer()->notNull(),
        ]);
        $this->batchInsert('attribution_type', ['name', 'attribution_per10000'], [[
            'ATTRACTION', 3000
        ], [
            'PUERTO DE MOGÁN', 7000 
        ]]);
        $this->createIndex('attribution_type-name-attribution_per10000-uidx', 'attribution_type', ['name', 'attribution_per10000'], true);
        $this->createTable('advisor', [
            'id' => $this->primaryKey(),
            'name' => $this->string(32)->notNull()->unique(),
            'default_office' => $this->string(18) . ' references office(name)',
            'default_attribution_type_id' => $this->integer() . ' references attribution_type(id)',
        ]);
        $attraction3000_id = Yii::$app->db->createCommand('select id from attribution_type where name = \'ATTRACTION\' and attribution_per10000 = 3000')->execute();
        $this->batchInsert('advisor', ['name', 'default_office', 'default_attribution_type_id'], [[
            'RAFA', 'PUERTO DE MOGÁN', $attraction3000_id
        ], [
            'ROBERT', 'PUERTO RICO', $attraction3000_id + 1
        ]]);
        $this->createTable('advisor_tranche', [
            'id' => $this->primaryKey(),
            'from_euc' => $this->integer()->notNull(),
            'commission_per10000' => $this->integer()->notNull(),
            'advisor_id' => $this->integer()->notNull() . ' references advisor(id)'
        ]);
        $this->createIndex('advisor_tranche-from_euc-advisor_id-uidx', 'advisor_tranche', ['from_euc', 'advisor_id'], true);
        $rafa_id = Yii::$app->db->createCommand('select id from advisor where name = \'RAFA\'')->execute();
        $this->batchInsert('advisor_tranche', ['from_euc', 'commission_per10000', 'advisor_id'], [[
            0, 2000, $rafa_id
        ], [
            2000001, 2100, $rafa_id
        ], [
            5000001, 2200, $rafa_id
        ]]);
        $this->createTable('recipient_category', [
            'name' => $this->string(32) . ' primary key',
        ]);
        $this->batchInsert('recipient_category', ['name'], [['BUYER'], ['SELLER'], ['COLLABORATOR']]);
        $this->createTable('invoice', [
            'code' => $this->string(18)->notNull(),
            'issued_at' => $this->date(),
            'amount_euc' => $this->integer(),
            'recipient_category' => $this->string(18)->notNull() . ' references recipient_category(name)'
        ]);
        $this->createTable('transaction', [
            'id' => $this->primaryKey(),
            'transaction_type' => $this->string(18)->notNull()->defaultValue('TRADING') . ' references transaction_type(name)',
            'custom_type' => $this->string(32) . ' references custom_type(name)',
            'transfer_type' => $this->string(32) . ' references transfer_type(name)',
            'development_type' => $this->string(32) . ' references development_type(name)',
            'first_published_at' => $this->date(),
            'first_published_price_euc' => $this->integer(),
            'last_published_at' => $this->date(),
            'last_published_price_euc' => $this->integer(),
            'option_signed_at' => $this->date()->notNull(),
            'sale_price_euc' => $this->integer(),
            'buyer_id' => $this->integer()->notNull() . ' references contact(id)',
            'is_new_buyer' => $this->boolean(),
            'buyer_owner' => $this->string(32) . ' references partner(name)',
            'seller_id' => $this->integer()->notNull() . ' references contact(id)',
            'is_new_seller' => $this->boolean(),
            'seller_owner' => $this->string(32) . ' references partner(name)',
            'lead_type' => $this->string(18) . ' references lead_type(name)',
            'search_started_at' => $this->date(),
            'suggested_sale_price_euc' => $this->integer(),
            'passed_to_sales_by' => $this->integer() . ' references advisor(id)',
            'property_id' => $this->integer()->notNull() . ' references property(id)',
            'is_home_staged' => $this->boolean(),
            'our_fee_euc' => $this->integer(),
            'their_fee_euc' => $this->integer(),
            'payrolled_at' => $this->date(),
            'comments' => $this->text(),
            'approved' => $this->boolean()->notNull()->defaultValue(false),
            'created_at' => $this->timestamp(2)->notNull(),
            'updated_at' => $this->timestamp(2)->notNull()
        ]);
        $this->createTable('attribution', [
            'id' => $this->primaryKey(),
            'advisor_id' => $this->integer()->notNull() . ' references advisor(id)',
            'office' => $this->string(18) . ' references office(name)',
            'attribution_type_id' => $this->integer()->notNull() . ' references attribution_type(id)',
            'attribution_euc' => $this->integer()->notNull(),
            'transaction_id' => $this->integer()->notNull() . ' references transaction(id)',
            'comments' => $this->text()
        ]);
    }

    public function safeDown() {
    }
}
