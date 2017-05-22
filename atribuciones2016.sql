drop table if exists atribuciones2016;
select load_csv_file('atribuciones2016', '/home/claudio/projects/cardestat/atribuciones2016.csv', 23);

create or replace function getAttributionTypeId(_name varchar, _attribution_bp integer, _attribution_type_category smallint)
returns integer as $$
declare
    at attribution_type;
    at_id integer;
begin
   select * into at from attribution_type where name = _name and attribution_bp = _attribution_bp;
   if at.id is not null then
       return at.id;
   else
       insert into attribution_type (name, attribution_bp, category)
           values (_name, _attribution_bp, _attribution_type_category)
           returning id into at_id;
       return at_id;
   end if;
end;
$$ language plpgsql;

create or replace function import2016Attributions() 
returns void as $$
declare
    a16 atribuciones2016;
    i invoice;
    adv advisor;
    at_id integer;
    bp integer;
    attr_amount_euc integer;
    inv_net_amount_euc integer;
    attributions varchar[][];
    transaction_net_invoiced_euc integer;
    j integer;
begin
    for a16 in select * from atribuciones2016 loop
        attributions := array[
             -- [amount, adviser, office, attribution_type, attribution_type_category]
            array[a16.prod_rk_ar, 'ROBERT KORTLANG', 'ARGUINEGUÍN', 'CAPTACIÓN', '0'],
            array[a16.prod_pb_ar, 'PAOLA BUSCEMI', 'ARGUINEGUÍN', 'CAPTACIÓN', '0'],
            array[a16.prod_dt_ar, 'DEBORAH TESCH', 'ARGUINEGUÍN', 'VENTA', '0'],
            array[a16.prod_sb_ar, 'STEPHAN BERGONJE', 'ARGUINEGUÍN', 'VENTA', '0'],
            array[a16.prod_te_ar, 'THOMAS EKBLOM', 'ARGUINEGUÍN', 'VENTA', '0'],
            array[a16.prod_yw_ar, 'YVONNE WEERTS', 'ARGUINEGUÍN', 'VENTA', '0'],
            array[a16.te_ar, 'TINA FREDTOFT', null, 'GESTIÓN DE LEADS', null],
            array[a16.cg_ar, 'CARLOS GALÁN', null, 'GESTIÓN DE FIRMAS', null],
            array[a16.prod_rk_pm, 'ROBERT KORTLANG', 'PUERTO DE MOGÁN', 'CAPTACIÓN', '0'],
            array[a16.prod_pb_pm, 'PAOLA BUSCEMI', 'PUERTO DE MOGÁN', 'CAPTACIÓN', '0'],
            array[a16.prod_ih_pm, 'INGE HILDEBRANDT', 'PUERTO DE MOGÁN', 'VENTA', '0'],
            array[a16.prod_rk_pr, 'ROBERT KORTLANG', 'PUERTO RICO', 'CAPTACIÓN', '0'],
            array[a16.prod_pb_pr, 'PAOLA BUSCEMI', 'PUERTO RICO', 'CAPTACIÓN', '0'],
            array[a16.prod_lm_pr, 'LEONOR MARTÍN', 'PUERTO RICO', 'VENTA', '0'],
            array[a16.prod_ra_pr, 'RAFAEL ALZOLA', 'PUERTO RICO', 'VENTA', '0'],
            array[a16.prod_cm_pr, 'CARINA MAEHLE', 'PUERTO RICO', 'VENTA', '0']
        ];
        select * into i from invoice where code = a16.factura;
        if i is null then
            raise notice 'invoice not found: %', a16.factura;
            continue;
        end if;
        delete from attribution where transaction_id = i.transaction_id and attribution_type_id = 1; -- delete 'DESCONOCIDO' attribution
        inv_net_amount_euc := round(regexp_replace(a16.total_neto, '[, €]', '', 'g')::float*100);
        select sum(amount_euc) into transaction_net_invoiced_euc
            from invoice
            where transaction_id = i.transaction_id
            group by transaction_id;
        for j in 1 .. array_upper(attributions, 1) loop
            continue when attributions[j][1] is null;
            attr_amount_euc := round(regexp_replace(attributions[j][1], '[, €]', '', 'g')::float*100);
            continue when attr_amount_euc = 0;
            bp := round((attr_amount_euc::float / transaction_net_invoiced_euc * 10000)::numeric, 2);
            at_id := getAttributionTypeId(attributions[j][4], bp, attributions[j][5]::smallint);
            select * into adv from advisor where name = attributions[j][2];
            insert into attribution (advisor_id, office, attribution_type_id, amount_euc, transaction_id)
                values (adv.id, attributions[j][3], at_id, null, i.transaction_id);
        end loop;
    end loop;
end;
$$ language plpgsql;

select import2016Attributions();
drop function getAttributionTypeId(_name varchar, _attribution_bp integer, _attribution_type_category smallint);
drop function import2016Attributions();
