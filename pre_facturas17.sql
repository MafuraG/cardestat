drop table if exists firmas_facturas17;
select load_csv_file('firmas_facturas17', '/home/claudio/projects/cardestat/firmas_facturas2017.csv', 6);

update firmas_facturas17 set ref_vendedor = v.reference
    from contact v
    where upper(coalesce(unaccent(last_name), '') || coalesce(', ' || unaccent(first_name), '')) = upper(trim(unaccent(vendedor)));

update firmas_facturas17 set ref_comprador = s.reference
    from contact s
    where upper(coalesce(unaccent(last_name), '') || coalesce(', ' || unaccent(first_name), '')) = upper(trim(unaccent(comprador)));

update firmas_facturas17 set firma = ids 
    from (
        select array_agg(t.id) ids, b.reference ref_comprador, s.reference ref_vendedor 
        from transaction t
             join contact b on (t.buyer_id = b.id)
             join contact s on (t.seller_id = s.id)
        group by ref_comprador, ref_vendedor) t
    where firmas_facturas17.ref_comprador = t.ref_comprador and
          firmas_facturas17.ref_vendedor = t.ref_vendedor

