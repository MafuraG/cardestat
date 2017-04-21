select round(sum(amount_euc)/100., 2) as atribucion_total_eu
from attribution a
     join transaction t on a.transaction_id = t.id
where option_signed_at between '2016-01-01' and '2016-12-31';

select office, ad.name as advisor, round(sum(amount_euc)/100., 2) as atribucion_oficina_eu
from attribution a
     join advisor ad on a.advisor_id = ad.id
     join transaction t on a.transaction_id = t.id
where option_signed_at between '2016-01-01' and '2016-12-31'
group by office, ad.name
having sum(amount_euc) <> 0 order by office, ad.name;

select ad.name as advisor, round(sum(amount_euc)/100., 2) as atribucion_oficina_eu
from attribution a
     join advisor ad on a.advisor_id = ad.id
     join transaction t on a.transaction_id = t.id
where option_signed_at between '2016-01-01' and '2016-12-31'
group by ad.name
having sum(amount_euc) <> 0 order by ad.name;

select office, round(sum(amount_euc)/100., 2) as atribucion_oficina_eu
from attribution a
     join transaction t on a.transaction_id = t.id
where option_signed_at between '2016-01-01' and '2016-12-31'
group by office
having sum(amount_euc) <> 0;

select round(sum(amount_euc)/100., 2) as facturacion_total_eu
from invoice i
     join transaction t on i.transaction_id = t.id
where option_signed_at between '2016-01-01' and '2016-12-31';
