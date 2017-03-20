<?php

use yii\db\Migration;

class m170317_093446_commissions extends Migration
{
    public function safeUp()
    {
        $this->execute('
            create view transaction_commission as
                select 
                    advisor_id,
                    advisor_name,
                    attribution_comments,
                    t.id as transaction_id,
                    to_char(payrolled_at, \'yyyy-mm\') as payroll_month,
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
                    array_agg(i.issued_at) as invoice_issuance_dates,
                    array_agg(i.code) as invoice_codes,
                    sum(i.amount_euc) as total_invoiced_euc,
                    our_fee_euc,
                    their_fee_euc,
                    attribution_offices,
                    attribution_type_names,
                    attribution_type_bps,
                    total_attributed_euc,
                    total_attributed_sum_euc,
                    total_attributed_sum_corrected_euc
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
                            array_to_string(array_agg(at.comments), \' -- \', \'NULL\') as attribution_comments,
                            array_to_string(array_agg(at.office), \'$$\', \'NULL\') as attribution_offices,
                            array_to_string(array_agg(att.name), \'$$\', \'NULL\') as attribution_type_names,
                            array_to_string(array_agg(att.attribution_bp), \'$$\', \'NULL\') as attribution_type_bps,
                            array_to_string(array_agg(at.amount_euc), \'$$\', \'NULL\') as total_attributed_euc,
                            sum(at.amount_euc) as total_attributed_sum_euc
                        from
                            attribution at
                            join attribution_type att on (att.id = at.attribution_type_id)
                            join advisor ad on (ad.id = at.advisor_id)
                        group by
                            transaction_id,
                            ad.id,
                            advisor_name) a on (a.transaction_id = t.id)
                    left join (
                       select 
                           a.transaction_id,
                           round(sum(at.attribution_bp / 10000. * i.sum)) as total_attributed_sum_corrected_euc
                       from attribution a
                            join attribution_type at on (a.attribution_type_id = at.id)
                            join (
                                select transaction_id, sum(amount_euc)
                                from invoice
                                group by transaction_id) i on (a.transaction_id = i.transaction_id)
                       group by a.transaction_id
                    ) ca on (ca.transaction_id = t.id)
                    left join invoice i on (i.transaction_id = t.id)
                group by
                    advisor_id,
                    advisor_name,
                    t.id,
                    to_char(payrolled_at, \'yyyy-mm\'),
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
                    total_attributed_sum_corrected_euc,
                    attribution_comments
        ');
    }

    public function safeDown()
    {
        $this->execute('drop view transaction_commission');
    }
}
