\! echo duplicate transactions from origin
select id::int, count(*) from firmas group by id having count(*) > 1 order by id::int desc;

\! echo invoices without operation
-- select f.* from facturas f left join firmas_facturas ff on (f.num = ff.factura) where ff.firma is null; --alternative
select '???' as firma, num as factura, fecha, vendedor, nombre as comprador, r1, colab1, total1neto, total_neto from facturas f left join firmas_facturas ff on (f.num = ff.factura) where ff.firma is null;

\! echo duplicated invoices
select num as factura, count(*) from facturas group by num having count(*) > 1;

\! echo invoices of multiple operations
select factura, count(*) from firmas_facturas group by factura having count(*) > 1;

\! echo operations with more than one invoice
--select f.id as firma, facturas, fecha_firma as fecha, f.ref_vendedor, substring(f.apellido_nombre_vendedor, 0, 18) || '...' as vendedor, ref_comprador, substring(apellido_nombre_comprador, 0, 18) || '...' as comprador, operacion, ref_prop, precio from firmas f join (select firma, array_to_string(array_distinct(array_agg(factura)), ', ') as facturas from firmas_facturas group by firma having count(*) > 1) ff on f.id = ff.firma; --alternative 
select firma, array_distinct(array_agg(factura)) from firmas_facturas group by firma having count(*) > 1 order by firma;

\! echo operations not inserted
select f.id as firma, f.ref_vendedor, f.apellido_nombre_vendedor as vendedor, ref_comprador, apellido_nombre_comprador as comprador, operacion, ref_prop, direccion_prop as vivienda, precio, fecha_firma from firmas f left join transaction t on f.id = t.external_id where t.id is null;

\! echo total attributions 2016
select round(sum(amount_euc)/100., 2) as atribucion_total_eu
from effective_attribution a
     join transaction t on a.transaction_id = t.id
where option_signed_at between '2016-01-01' and '2016-12-31';

\! echo total attributions 2016 by adviser
select office, ad.name as advisor, round(sum(amount_euc)/100., 2) as atribucion_asesor_eu
from effective_attribution a
     join advisor ad on a.advisor_id = ad.id
     join transaction t on a.transaction_id = t.id
where option_signed_at between '2016-01-01' and '2016-12-31'
group by office, ad.name
having sum(amount_euc) <> 0 order by office, ad.name;

\! echo total attributions 2016 by advisor acording to Pilar
select office, ad.name as advisor, round(sum(amount_euc)/100., 2) as atribucion_asesor_pilar_eu
from effective_attribution a
     join advisor ad on a.advisor_id = ad.id
     join transaction t on a.transaction_id = t.id
     join (
         select min(issued_at) as issued_at, transaction_id
         from invoice
         group by transaction_id
     ) oldest_invoice on t.id = oldest_invoice.transaction_id
where oldest_invoice.issued_at between '2016-01-01' and '2016-12-31'
group by office, ad.name
having sum(amount_euc) <> 0 order by office, ad.name;

select ad.name as advisor, round(sum(amount_euc)/100., 2) as atribucion_oficina_eu
from effective_attribution a
     join advisor ad on a.advisor_id = ad.id
     join transaction t on a.transaction_id = t.id
where option_signed_at between '2016-01-01' and '2016-12-31'
group by ad.name
having sum(amount_euc) <> 0 order by ad.name;

select office, round(sum(amount_euc)/100., 2) as atribucion_oficina_eu
from effective_attribution a
     join transaction t on a.transaction_id = t.id
where option_signed_at between '2016-01-01' and '2016-12-31'
group by office
having sum(amount_euc) <> 0;

select office, round(sum(amount_euc)/100., 2) as atribucion_oficina_pilar_eu
from effective_attribution a
     join transaction t on a.transaction_id = t.id
     join (
         select min(issued_at) as issued_at, transaction_id
         from invoice
         group by transaction_id
     ) oldest_invoice on t.id = oldest_invoice.transaction_id
where oldest_invoice.issued_at between '2016-01-01' and '2016-12-31'
group by office
having sum(amount_euc) <> 0;

select round(sum(amount_euc)/100., 2) as facturacion_total_eu
from invoice i
     join transaction t on i.transaction_id = t.id
where option_signed_at between '2016-01-01' and '2016-12-31';

-- select 'Facturación total' as Concepto, '1.004.308,62 €' as Pilar, '1.273.938,91 €' as Aplicación union select 'Atribución Arguineguín', '414.783,75 €', '421.361,19 €' union select 'Atribución Puerto Rico', '378.584,78 €', '377.815,22 €' union select 'Atribución Puerto de Mogán', '210.940,09 €', '210.940,18 €';
