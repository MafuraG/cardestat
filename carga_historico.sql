drop table if exists historico_produccion;
select load_csv_file('historico_produccion', '/home/claudio/projects/cardestat/historico_produccion.csv', 6);
drop table if exists historico_atribucion;
select load_csv_file('historico_atribucion', '/home/claudio/projects/cardestat/historico_atribucion.csv', 7);

insert into archived_invoice (month, amount_euc, office, transaction_type, subject, n_operations_c)
    select make_date(year::int, mw.month, 1), round(amount_euc::int*weight), office, transaction_type, subject, round(n_operations_c::int*weight)
    from historico_produccion 
         cross join (
             select 1 as month, 0.1 as weight union
             select 2, 0.1 union
             select 3, 0.1 union
             select 4, 0.07 union
             select 5, 0.07 union
             select 6, 0.06 union
             select 7, 0.06 union
             select 8, 0.07 union
             select 9, 0.07 union
             select 10, 0.1 union
             select 11, 0.1 union
             select 12, 0.1
         ) mw;

insert into archived_attribution (archived_invoice_id, attributed_euc, advisor_id, commission_euc, n_operations_c)
    select ai.id,
           cast(attributed_euc::float/ai_s.sum*amount_euc*weight as int),
           ad.id,
           cast(commission_euc::float/ai_s.sum*amount_euc*weight as int),
           case when ai.transaction_type = 'COMPRAVENTA' then 
               cast(n_sales_c::float/ai_op.sum*n_operations_c*weight as int)
           else
               cast(n_rentals_c::float/ai_op.sum*n_operations_c*weight as int)
           end
    from historico_atribucion ha 
         cross join (
             select 1 as month, 0.1 as weight union
             select 2, 0.1 union
             select 3, 0.1 union
             select 4, 0.07 union
             select 5, 0.07 union
             select 6, 0.06 union
             select 7, 0.06 union
             select 8, 0.07 union
             select 9, 0.07 union
             select 10, 0.1 union
             select 11, 0.1 union
             select 12, 0.1
         ) mw
         join archived_invoice ai on (make_date(ha.year::int, mw.month, 1) = ai.month and ha.office = ai.office)
         join (
             select month, office, sum(amount_euc::int)
             from archived_invoice
             where transaction_type in ('COMPRAVENTA', 'ALQUILER')
             group by month, office
         ) ai_s on (ai.month = ai_s.month and ai.office = ai_s.office)
         join (
             select month, office, transaction_type, sum(n_operations_c::int)
             from archived_invoice
             where transaction_type in ('COMPRAVENTA', 'ALQUILER')
             group by month, office, transaction_type
         ) ai_op on (ai.month = ai_op.month and ai.office = ai_op.office and ai.transaction_type = ai_op.transaction_type)
         join advisor ad on (ad.name = ha.advisor);
