-- dado nombre de vendedor y de comprador, averiguar la referencia de la propiedad
-- busca transacciones donde coincidan vendedor y comprador con los dados
select prop_refs, sell_ref, w.vendedor, buy_ref, w.comprador
from (
    select trim((regexp_split_to_array(operacion, ' - ')::text[])[1]) as vendedor, trim((regexp_split_to_array(operacion, ' - ')::text[])[2]) as comprador, * from testigos_14_15) w
left join (
    select s.last_name as seller_last_name, s.reference as sell_ref, b.last_name as buyer_last_name, b.reference as buy_ref, string_agg(p.reference, ', ') as prop_refs 
    from transaction t join contact s on t.seller_id = s.id join contact b on t.buyer_id = b.id join property p on p.id = t.property_id 
    group by seller_last_name, sell_ref, buyer_last_name, buy_ref) r on r.seller_last_name = w.vendedor and r.buyer_last_name = w.comprador;

-- transacciones para exportar
select option_signed_at, round(sale_price_euc/100., 2) as sale_price_eu, s.last_name as seller, s.reference as sel_ref, b.last_name as buyer, b.reference as buy_ref, p.location, p.reference as prop_ref from transaction t join contact s on (s.id = t.seller_id) join contact b on(b.id = t.buyer_id) join property p on (p.id = t.property_id) where option_signed_at between '2016-01-01' and '2016-12-31';

-- distribucion mensual de volumen de transacciones:
select extract(month from option_signed_at) as month, round(sum(sale_price_euc)/total.yearly*100, 2) || '%' monthly_ratio from transaction cross join (select sum(sale_price_euc) as yearly from transaction where option_signed_at between '2016-01-01' and '2016-12-31') total where option_signed_at between '2016-01-01' and '2016-12-31' group by month, total.yearly order by month;

-- firmas importadas vs. firmas para importar, comprobación por ID de firma
select f17.id, t.external_id, f17.fecha_firma::date, t.option_signed_at, f17.ref_vendedor, s.reference, f17.ref_comprador, b.reference, f17.ref_prop, p.reference, f17.precio, round(t.sale_price_euc/100) from firmas17 f17 left join transaction t on t.external_id = f17.id left join contact s on (t.seller_id = s.id) left join contact b on (b.id = t.buyer_id) left join property p on (p.id = t.property_id) where t.id is not null and (ref_vendedor <> s.reference or ref_comprador <> b.reference or coalesce(ref_prop, '') <> coalesce(p.reference, '') or round(precio::numeric) <> round(t.sale_price_euc/100) );

-- firmas importadas vs. firmas para importar, comprobación por vendedor y comprador
select f17.id, t.id as tx_id, t.external_id, f17.fecha_firma::date, t.option_signed_at, f17.ref_vendedor, s.reference, f17.ref_comprador, b.reference, f17.ref_prop, f17.precio, round(t.sale_price_euc/100) from firmas17 f17 left join contact s on s.reference = ref_vendedor left join contact b on b.reference = ref_comprador left join transaction t on t.buyer_id = b.id and t.seller_id = s.id where t.external_id <> f17.id;
