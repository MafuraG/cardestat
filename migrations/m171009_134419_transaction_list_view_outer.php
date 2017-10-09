<?php

use yii\db\Migration;

class m171009_134419_transaction_list_view_outer extends Migration
{
    public function up()
    {

        $this->execute('
            create or replace view transaction_list_item as
                select t.*,
                       t.option_signed_at - first_published_at as sale_duration,
                       p.location as property_location,
                       p.building_complex as property_building_complex,
                       p.reference as property_reference,
                       s.reference as seller_reference,
                       coalesce(nullif(s.last_name, \'\') || \', \', \'\') ||
                           coalesce(nullif(s.first_name, \'\') || \' \', \'\') as seller_name,
                       b.reference as buyer_reference,
                       coalesce(nullif(b.last_name, \'\') || \', \', \'\') ||
                           coalesce(nullif(b.first_name, \'\') || \' \', \'\') as buyer_name,
                       their_fee_euc is null or their_fee_euc = 0 as cardenas100,
                       array_to_string(array_distinct(array_agg(ad.name)), \', \') as advisors,
                       i.count as n_invoices,
                       fi.first_issued_at as first_invoiced_at,
                       coalesce(our_fee_euc*100. / sale_price_euc, 0) as our_fee_bp,
                       t.buyer_provider is not null or t.seller_provider is not null as with_collaborator,
                       i.count is not null as invoiced,
                       payroll_month is not null as payrolled
                from transaction t
                     left join property p on (p.id = t.property_id)
                     left join contact b on (b.id = t.buyer_id)
                     left join contact s on (s.id = t.seller_id)
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

    public function down()
    {
        echo "m171009_134419_transaction_list_view_outer cannot be reverted.\n";

        return false;
    }

    /*
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
    }

    public function safeDown()
    {
    }
    */
}
