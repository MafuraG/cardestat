drop table if exists resumen_historico_produccion;
select load_csv_file('resumen_historico_produccion', '/home/claudio/projects/cardestat/resumen_historico_produccion.csv', 6);
drop table if exists resumen_historico_atribucion;
select load_csv_file('resumen_historico_atribucion', '/home/claudio/projects/cardestat/resumen_historico_atribucion.csv', 7);

insert into archived_invoice (month, amount_euc, office, transaction_type, subject, n_operations_c)
    select make_date(year::int, mw.month, 1), round(amount_euc::int*weight), office, transaction_type, subject, round(n_operations_c::int*weight)
    from resumen_historico_produccion 
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
           case when ha_s.sum <> 0 then
               cast(attributed_euc::float/ha_s.sum*amount_euc as int)
           else
               0
           end,
           ad.id,
           cast(commission_euc::float*mw.weight as int),
           case when ha_sop.sum <> 0 and ai.transaction_type = 'COMPRAVENTA' then 
               cast(n_sales_c::float/ha_sop.sum*n_operations_c as int)
           when ha_rop.sum <> 0 and ai.transaction_type = 'ALQUILER' then 
               cast(n_rentals_c::float/ha_rop.sum*n_operations_c as int)
           else
               0
           end
    from resumen_historico_atribucion ha 
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
         join advisor ad on (ad.name = ha.advisor)
         join archived_invoice ai on (make_date(ha.year::int, mw.month, 1) = ai.month and coalesce(ha.office, '') = coalesce(ai.office, ''))
         join (
             select year, office, sum(attributed_euc::int)
             from resumen_historico_atribucion ha
             group by year, office
         ) ha_s on (make_date(ha_s.year::int, mw.month, 1) = ai.month and coalesce(ha_s.office, '') = coalesce(ai.office, ''))
         join (
             select year, office, sum(n_rentals_c::int)
             from resumen_historico_atribucion ha
             group by year, office
         ) ha_rop on (make_date(ha_rop.year::int, mw.month, 1) = ai.month and coalesce(ha_rop.office, '') = coalesce(ai.office, ''))
         join (
             select year, office, sum(n_sales_c::int)
             from resumen_historico_atribucion ha
             group by year, office
         ) ha_sop on (make_date(ha_sop.year::int, mw.month, 1) = ai.month and coalesce(ha_sop.office, '') = coalesce(ai.office, ''))
     where (ha_s.sum <> 0 or ha_rop.sum <> 0 or ha_sop.sum <> 0) and (attributed_euc::int <> 0 or commission_euc::int <> 0);
