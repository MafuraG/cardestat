drop table if exists facturas;
select load_csv_file('facturas', '/home/claudio/projects/cardestat/facturas2016.csv', 15);
drop table if exists firmas_facturas;
select load_csv_file('firmas_facturas', '/home/claudio/projects/cardestat/firmas_facturas2016.csv', 2);

insert into invoice(
    code,
    amount_euc,
    issued_at,
    recipient_category,
    transaction_id)
--select distinct on (num) -- there are repeated invoices at the moment
select
     num,
     round(regexp_replace(r1, '[, € ]', '', 'g')::float*100),
     fecha::date,
     'VENDEDOR',
     t.id
from facturas f 
     join firmas_facturas ff on (f.num = ff.factura)
     join transaction t on (ff.firma = t.external_id);

insert into invoice(
    code,
    amount_euc,
    issued_at,
    recipient_category,
    transaction_id)
--select distinct on (num) -- there are repeated invoices at the moment
select
     num || '-comprador',
     round(regexp_replace(r100, '[, € ]', '', 'g')::float*100),
     fecha::date,
     'COMPRADOR',
     t.id
from facturas f 
     join firmas_facturas ff on (f.num = ff.factura)
     join transaction t on (ff.firma = t.external_id)
where r100 is not null;

insert into invoice(
    code,
    amount_euc,
    issued_at,
    recipient_category,
    transaction_id)
--select distinct on (num) -- there are repeated invoices at the moment
select
     num || '-colab',
     -round(regexp_replace(colab1, '[, € ]', '', 'g')::float*100),
     fecha::date,
     'COLABORADOR',
     t.id
from facturas f 
     join firmas_facturas ff on (f.num = ff.factura)
     join transaction t on (ff.firma = t.external_id)
where colab1 is not null;

insert into invoice(
    code,
    amount_euc,
    issued_at,
    recipient_category,
    transaction_id)
--select distinct on (num) -- there are repeated invoices at the moment
select
     num || '-banco',
     round(regexp_replace(com_banc, '[, € ]', '', 'g')::float*100),
     fecha::date,
     'BANCO',
     t.id
from facturas f 
     join firmas_facturas ff on (f.num = ff.factura)
     join transaction t on (ff.firma = t.external_id)
where com_banc is not null;

update transaction
    set their_fee_euc = colab.sum
    from (
        select transaction_id, sum(amount_euc)
        from invoice
        where recipient_category = 'COLABORADOR'
        group by transaction_id) colab
    where colab.transaction_id = transaction.id;

update transaction
    set our_fee_euc = net.sum
    from (
        select transaction_id, sum(amount_euc)
        from invoice
        group by transaction_id) net
    where net.transaction_id = transaction.id;
