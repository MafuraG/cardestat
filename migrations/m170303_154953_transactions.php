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
        $this->batchInsert('transfer_type', ['name'], [['FIRST HAND'], ['SECOND HAND']]);
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
            'attribution_bp' => $this->integer()->notNull(),
        ]);
        $this->batchInsert('attribution_type', ['name', 'attribution_bp'], [[
            'UNKNOWN', 0
        ], [
            'ATTRACTION', 3000
        ]]);
        $this->createIndex('attribution_type-name-attribution_bp-uidx', 'attribution_type', ['name', 'attribution_bp'], true);
        $this->createTable('advisor', [
            'id' => $this->primaryKey(),
            'name' => $this->string(32)->notNull()->unique(),
            'default_office' => $this->string(18) . ' references office(name)',
            'default_attribution_type_id' => $this->integer() . ' references attribution_type(id)',
        ]);
        $unknown0_id = Yii::$app->db->createCommand('select id from attribution_type where attribution_bp = 0')->execute();
        $this->batchInsert('advisor', ['name', 'default_office', 'default_attribution_type_id'], [[
            'RAFAEL ALZOLA', 'ARGUINEGUÍN', $unknown0_id
        ], [
            'GILBERTO GIL', 'ARGUINEGUÍN', $unknown0_id
        ], [
            'LONNIE LINDQUIST', 'ARGUINEGUÍN', $unknown0_id
        ], [
            'LEONOR MARTÍN', 'ARGUINEGUÍN', $unknown0_id
        ], [
            'CAROLINA GARCÍA', 'ARGUINEGUÍN', $unknown0_id
        ], [
            'AXEL KUBISCH', 'ARGUINEGUÍN', $unknown0_id
        ], [
            'DANIEL GARCÍA', 'ARGUINEGUÍN', $unknown0_id
        ], [
            'STEPHAN BERGONJE', 'ARGUINEGUÍN', $unknown0_id
        ], [
            'CARINA MAEHLE', 'ARGUINEGUÍN', $unknown0_id
        ], [
            'KENT BERGSTEN', 'ARGUINEGUÍN', $unknown0_id
        ], [
            'INGE HILDEBRANDT', 'ARGUINEGUÍN', $unknown0_id
        ], [
            'DEBORAH TESCH', 'ARGUINEGUÍN', $unknown0_id
        ], [
            'YVONNE WEERTS', 'ARGUINEGUÍN', $unknown0_id
        ], [
            'THERESA BONA', 'ARGUINEGUÍN', $unknown0_id
        ], [
            'THOMAS EKBLOM', 'ARGUINEGUÍN', $unknown0_id
        ], [
            'CRISTINA CARUSO', 'ARGUINEGUÍN', $unknown0_id
        ]]);
        $this->createTable('advisor_tranche', [
            'id' => $this->primaryKey(),
            'from_euc' => $this->integer()->notNull(),
            'commission_bp' => $this->integer()->notNull(),
            'advisor_id' => $this->integer()->notNull() . ' references advisor(id)'
        ]);
        $this->createIndex('advisor_tranche-from_euc-advisor_id-uidx', 'advisor_tranche', ['from_euc', 'advisor_id'], true);
        $rafa_id = Yii::$app->db->createCommand('select id from advisor where name = \'RAFAEL ALZOLA\'')->execute();
        $this->batchInsert('advisor_tranche', ['from_euc', 'commission_bp', 'advisor_id'], [[
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
        $this->createTable('transaction', [
            'id' => $this->primaryKey(),
            'external_id' => $this->string(12)->unique(),
            'transaction_type' => $this->string(18)->notNull()->defaultValue('TRADING') . ' references transaction_type(name)',
            'custom_type' => $this->string(32) . ' references custom_type(name)',
            'transfer_type' => $this->string(32) . ' references transfer_type(name)',
            'development_type' => $this->string(32) . ' references development_type(name)',
            'first_published_at' => $this->date(),
            'first_published_price_euc' => $this->integer(),
            'last_published_at' => $this->date(),
            'last_published_price_euc' => $this->integer(),
            'option_signed_at' => $this->date()->notNull(),
            'sale_price_euc' => $this->integer()->notNull(),
            'buyer_id' => $this->integer()->notNull() . ' references contact(id)',
            'is_new_buyer' => $this->boolean(),
            'buyer_provider' => $this->string(32) . ' references partner(name)',
            'seller_id' => $this->integer()->notNull() . ' references contact(id)',
            'is_new_seller' => $this->boolean(),
            'seller_provider' => $this->string(32) . ' references partner(name)',
            'lead_type' => $this->string(18)->notNull()->defaultValue('NC') . ' references lead_type(name)',
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
        $this->createTable('invoice', [
            'id' => $this->primaryKey(),
            'code' => $this->string(18)->notNull()->unique(),
            'issued_at' => $this->date()->notNull(),
            'amount_euc' => $this->integer()->notNull(),
            'transaction_id' => $this->integer()->notNull() . ' references transaction(id)',
            'recipient_category' => $this->string(18)->notNull() . ' references recipient_category(name)'
        ]);
        $this->createTable('attribution', [
            'id' => $this->primaryKey(),
            'advisor_id' => $this->integer()->notNull() . ' references advisor(id)',
            'office' => $this->string(18) . ' references office(name)', // null means all/no offices
            'attribution_type_id' => $this->integer()->notNull() . ' references attribution_type(id)',
            'amount_euc' => $this->integer()->notNull(),
            'transaction_id' => $this->integer()->notNull() . ' references transaction(id)',
            'comments' => $this->text()
        ]);
        $this->execute('
            create view transaction_list_item as
                select t.*,
                       t.option_signed_at - first_published_at as sale_duration,
                       p.location as property_location, 
                       p.building_complex as property_building_complex, 
                       p.reference as property_reference,
                       s.reference as seller_reference,
                       coalesce(nullif(s.first_name, \'\') || \' \', \'\') ||
                           coalesce(nullif(s.last_name, \'\') || \' \', \'\') as seller_name,
                       b.reference as buyer_reference,
                       coalesce(nullif(b.first_name, \'\') || \' \', \'\') ||
                           coalesce(nullif(b.last_name, \'\') || \' \', \'\') as buyer_name,
                       buyer_provider is null and seller_provider is null as cardenas100,
                       string_agg(ad.name, \', \') as advisors,
                       i.count as n_invoices,
                       fi.first_issued_at as first_invoiced_at,
                       coalesce(our_fee_euc*100. / sale_price_euc, 0) as our_fee_bp,
                       t.buyer_provider is not null or t.seller_provider is not null as with_collaborator,
                       i.count is not null as invoiced,
                       payrolled_at is not null as payrolled
                from transaction t
                     join property p on (p.id = t.property_id)
                     join contact b on (b.id = t.buyer_id)
                     join contact s on (s.id = t.seller_id)
                     left join attribution at on (t.id = at.transaction_id)
                     left join advisor ad on (at.advisor_id = ad.id)
                     left join (select transaction_id, count(*)
                           from invoice
                           group by transaction_id) i on (i.transaction_id = t.id)
                     left join (select transaction_id, min(issued_at) as first_issued_at
                           from invoice
                           group by transaction_id) fi on (fi.transaction_id = t.id)
                group by t.id, p.reference, p.location, p.building_complex, s.reference, s.first_name, s.last_name, b.reference, b.first_name, b.last_name, i.count, fi.first_issued_at, t.buyer_provider, t.seller_provider');
    }

    public function safeDown() {
        $this->execute('drop view transaction_list_item');
        $this->dropTable('attribution');
        $this->dropTable('invoice');
        $this->dropTable('transaction');
        $this->dropTable('recipient_category');
        $this->dropTable('advisor_tranche');
        $this->dropTable('advisor');
        $this->dropTable('attribution_type');
        $this->dropTable('office');
        $this->dropTable('lead_type');
        $this->dropTable('partner');
        $this->dropTable('development_type');
        $this->dropTable('transfer_type');
        $this->dropTable('custom_type');
        $this->dropTable('transaction_type');
    }
}
