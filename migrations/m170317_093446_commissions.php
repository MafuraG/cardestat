<?php

use yii\db\Migration;

class m170317_093446_commissions extends Migration
{
    public function safeUp()
    {
        $this->createTable('transaction_payroll', [
            'id' => $this->primaryKey(),
            'commission_bp' => $this->integer()->notNull(),
            'accumulated_euc' => $this->integer()->notNull(),
            'created_at' => $this->timestamp(2)->notNull(),
            'updated_at' => $this->timestamp(2)->notNull()
        ]);
        $this->createTable('correction', [
            'id' => $this->primaryKey(),
            'transaction_payroll_id' => $this->integer()->notNull() . ' references transaction_payroll(id)',
            'corrected_euc' => $this->integer()->notNull(),
            'compensated_euc' => $this->integer()->notNull()->defaultValue(0),
            'compensated_at' => $this->date()->notNull(),
            'reason' => $this->string(32),
            'created_at' => $this->timestamp(2)->notNull()->defaultExpression('now()'),
            'updated_at' => $this->timestamp(2)->notNull()->defaultExpression('now()')
        ]);
        $this->addColumn('attribution', 'transaction_payroll_id', 'integer references transaction_payroll(id)');
        $this->execute('
            create view transaction_attribution_summary as
                select 
                    advisor_id,
                    advisor_name,
                    attribution_comments,
                    t.id as transaction_id,
                    payrolled_at,
                    buyer_id,
                    coalesce(nullif(b.last_name, \'\') || \', \', \'\') || 
                        coalesce(nullif(b.first_name, \'\') || \' \', \'\') as buyer_name,
                    seller_id,
                    coalesce(nullif(s.last_name, \'\') || \', \', \'\') || 
                        coalesce(nullif(s.first_name, \'\') || \' \', \'\') as seller_name,
                    property_id,
                    p.location as property_location,
                    p.building_complex as property_building_complex,
                    p.reference as property_reference,
                    sale_price_euc,
                    case 
                        when array_agg(i.issued_at) = \'{NULL}\' then 0
                        else count(*)
                    end as n_invoices,
                    array_to_string(array_agg(i.issued_at), \', \') as invoice_issuance_dates,
                    array_to_string(array_agg(i.code), \', \') as invoice_codes,
                    sum(i.amount_euc) as total_invoiced_euc,
                    our_fee_euc,
                    their_fee_euc,
                    attribution_offices,
                    attribution_type_names,
                    attribution_type_bps,
                    total_attributed_euc,
                    total_attributed_sum_euc
                from 
                    transaction t
                    join contact b on (b.id = t.buyer_id)
                    join contact s on (s.id = t.seller_id)
                    join property p on (p.id = t.property_id)
                    join (
                        select 
                            at.transaction_id,
                            ad.id as advisor_id,
                            ad.name as advisor_name,
                            array_to_string(array_agg(at.comments), \' -- \') as attribution_comments,
                            array_to_string(array_agg(at.office), \', \', \'NULL\') as attribution_offices,
                            array_to_string(array_agg(att.name), \', \', \'NULL\') as attribution_type_names,
                            array_to_string(array_agg(att.attribution_bp), \', \', \'NULL\') as attribution_type_bps,
                            array_to_string(array_agg(at.amount_euc), \', \', \'NULL\') as total_attributed_euc,
                            sum(at.amount_euc) as total_attributed_sum_euc
                        from
                            transaction_attribution at
                            join attribution_type att on (att.id = at.attribution_type_id)
                            join advisor ad on (ad.id = at.advisor_id)
                        group by
                            transaction_id,
                            ad.id,
                            advisor_name) a on (a.transaction_id = t.id)
                    left join invoice i on (i.transaction_id = t.id)
                group by
                    advisor_id,
                    advisor_name,
                    t.id,
                    payrolled_at,
                    buyer_id,
                    b.first_name,
                    b.last_name,
                    s.first_name,
                    s.last_name,
                    p.location,
                    p.building_complex,
                    p.reference,
                    sale_price_euc,
                    our_fee_euc,
                    their_fee_euc,
                    attribution_offices,
                    attribution_type_names,
                    attribution_type_bps,
                    total_attributed_euc,
                    total_attributed_sum_euc,
                    attribution_comments
        ');
        $this->execute('
            create view transaction_attribution_calculated_summary as
                select 
                    a.transaction_id,
                    a.advisor_id,
                    payrolled_at,
                    round(sum(at.attribution_bp / 10000. * i.sum)) as calculated_total_attributed_sum_euc
                from transaction t
                     join attribution a on (t.id = a.transaction_id)
                     join attribution_type at on (a.attribution_type_id = at.id)
                     join (
                         select transaction_id, sum(amount_euc)
                         from invoice
                         group by transaction_id) i on (a.transaction_id = i.transaction_id)
                group by a.transaction_id, advisor_id, payrolled_at
        ');
    }

    public function safeDown()
    {
        $this->execute('drop view transaction_attribution_calculated_summary');
        $this->execute('drop view transaction_attribution_summary');
        $this->dropColumn('attribution', 'transaction_payroll_id');
        $this->dropTable('correction');
        $this->dropTable('transaction_payroll');
    }
}
