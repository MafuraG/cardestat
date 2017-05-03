drop table if exists historico_produccion;
select load_csv_file('historico_produccion', '/home/claudio/projects/cardestat/historico_produccion.csv', 6);
drop table if exists historico_atribucion;
select load_csv_file('historico_atribucion', '/home/claudio/projects/cardestat/historico_atribucion.csv', 7);

insert into archived_invoice (year, amount_euc, office, transaction_type, subject, n_operations_c)
    select year::int, amount_euc::int, office, transaction_type, subject, n_operations_c::int
    from historico_produccion;

insert into archived_attribution (archived_invoice_id, attributed_euc, advisor_id, commission_euc, n_operations_c)
    select ai.id,
           cast(attributed_euc::float/ai_s.sum*amount_euc as int),
           ad.id,
           cast(commission_euc::float/ai_s.sum*amount_euc as int),
           case when ai.transaction_type = 'COMPRAVENTA' then 
               cast(n_sales_c::float/ai_op.sum*n_operations_c as int)
           else
               cast(n_rentals_c::float/ai_op.sum*n_operations_c as int)
           end
    from historico_atribucion ha
         join archived_invoice ai on (ha.year::int = ai.year and ha.office = ai.office)
         join (
             select year, office, sum(amount_euc::int)
             from archived_invoice
             where transaction_type in ('COMPRAVENTA', 'ALQUILER')
             group by year, office
         ) ai_s on (ai.year = ai_s.year and ai.office = ai_s.office)
         join (
             select year, office, transaction_type, sum(n_operations_c::int)
             from archived_invoice
             where transaction_type in ('COMPRAVENTA', 'ALQUILER')
             group by year, office, transaction_type
         ) ai_op on (ai.year = ai_op.year and ai.office = ai_op.office and ai.transaction_type = ai_op.transaction_type)
         join advisor ad on (ad.name = ha.advisor);
