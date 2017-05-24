drop table if exists atribuciones2017;
select load_csv_file('atribuciones2017', '/home/claudio/projects/cardestat/atribuciones2017.csv', 15);

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

create or replace function import2017Attributions() 
returns void as $$
declare
    a17 atribuciones2017;
    a attribution;
    at attribution_type;
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
    for a17 in select * from atribuciones2017 loop
        attributions := array[
             -- [amount, adviser, office, attribution_type, attribution_type_category]
            array[a17.rk_ar, 'ROBERT KORTLANG', 'ARGUINEGUÍN', 'CAPTACIÓN', '0'],
            array[a17.ak_ar, 'ANNA KORPALA', 'ARGUINEGUÍN', 'VENTA', '0'],
            array[a17.sb_ar, 'STEPHAN BERGONJE', 'ARGUINEGUÍN', 'VENTA', '0'],
            array[a17.tek_ar, 'THOMAS EKBLOM', 'ARGUINEGUÍN', 'VENTA', '0'],
            array[a17.yw_ar, 'YVONNE WEERTS', 'ARGUINEGUÍN', 'VENTA', '0'],
            array[a17.te_na, 'TINA FREDTOFT', null, 'GESTIÓN DE LEADS', null],
            array[a17.cg_na, 'CARLOS GALÁN', null, 'GESTIÓN DE FIRMAS', null],
            array[a17.rk_pm, 'ROBERT KORTLANG', 'PUERTO DE MOGÁN', 'CAPTACIÓN', '0'],
            array[a17.ih_pm, 'INGE HILDEBRANDT', 'PUERTO DE MOGÁN', 'VENTA', '0'],
            array[a17.rk_pr, 'ROBERT KORTLANG', 'PUERTO RICO', 'CAPTACIÓN', '0'],
            array[a17.lm_pr, 'LEONOR MARTÍN', 'PUERTO RICO', 'VENTA', '0'],
            array[a17.ra_pr, 'RAFAEL ALZOLA', 'PUERTO RICO', 'VENTA', '0']
        ];
        select * into i from invoice where code = a17.factura;
        if i is null then
            raise notice 'invoice not found: %', a17.factura;
            continue;
        end if;
        delete from attribution where transaction_id = i.transaction_id and attribution_type_id = 1; -- delete 'DESCONOCIDO' attribution
        inv_net_amount_euc := round(regexp_replace(a17.total_neto, '[, €]', '', 'g')::float*100);
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
                values (adv.id, attributions[j][3], at_id, attr_amount_euc, i.transaction_id);
        end loop;
    end loop;
    -- recalculate attribution bp after previous 2016 and 2017 import, and set amount_euc to null to set coherent not-payrolled state 
    for a in select attribution.* 
             from attribution join attribution_type on (attribution.attribution_type_id = attribution_type.id) 
             where amount_euc is not null and attribution_bp > 0 loop
        select sum(amount_euc) into transaction_net_invoiced_euc
            from invoice
            where transaction_id = a.transaction_id
            group by transaction_id;
        bp := round((a.amount_euc::float / transaction_net_invoiced_euc * 10000)::numeric, 2);
        select * into at from attribution_type where id = a.attribution_type_id;
        at_id := getAttributionTypeId(at.name, bp, at.category);
        update attribution set amount_euc = null, attribution_type_id = at_id where id = a.id;
    end loop;
    delete from attribution_type where id not in (
        select distinct attribution_type_id from attribution union select default_attribution_type_id from advisor);
end;
$$ language plpgsql;

select import2017Attributions();
drop function getAttributionTypeId(_name varchar, _attribution_bp integer, _attribution_type_category smallint);
drop function import2017Attributions();
