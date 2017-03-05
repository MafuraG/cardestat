create or replace function load_csv_file
(
    target_table text,
    csv_path text,
    col_count integer
)
returns void as $$

declare

iter integer; -- dummy integer to iterate columns with
col text; -- variable to keep the column name at each iteration
col_first text; -- first column name, e.g., top left corner on a csv file or spreadsheet

begin
    set schema 'public';

    create table temp_table ();

    -- add just enough number of columns
    for iter in 1..col_count
    loop
        execute format('alter table temp_table add column col_%s text;', iter);
    end loop;

    -- copy the data from csv file
    execute format('copy temp_table from %L with delimiter '','' quote ''"'' csv ', csv_path);

    iter := 1;
    col_first := (select col_1 from temp_table limit 1);

    -- update the column names based on the first row which has the column names
    for col in execute format('select unnest(string_to_array(trim(temp_table::text, ''()''), '','')) from temp_table where col_1 = %L', col_first)
    loop
        execute format('alter table temp_table rename column col_%s to %s', iter, col);
        iter := iter + 1;
    end loop;

    -- delete the columns row
    execute format('delete from temp_table where %s = %L', col_first, col_first);

    -- change the temp table name to the name given as parameter, if not blank
    if length(target_table) > 0 then
        execute format('alter table temp_table rename to %I', target_table);
    end if;

end;
$$ language plpgsql;

create or replace function array_distinct(anyarray) returns anyarray as $f$
    select array_agg(distinct x) from unnest($1) t(x);
$f$ language sql immutable;

--

select load_csv_file('firmas', '/home/claudio/Downloads/Firmas_incompleto.csv', 12);
select load_csv_file('sellers', '/home/claudio/Downloads/Agents Book - Buyers a 070217.csv', 10);
select load_csv_file('buyers', '/home/claudio/Downloads/Agents Book - Sellers a 070217.csv', 10);
alter table firmas add column step integer;
alter table firmas add column fill_price boolean not null default false;
update firmas set precio = null::text[] where trim(precio) = '';
update firmas set fill_price = true where precio is null;
create index firmas_comprador_vendedor_vivienda on firmas(comprador,vendedor,vivienda);
create index sellers_name_number_property on sellers (contact_name, contact_number, external_property_number);
create index sellers_adress_name_number_property on sellers (property_adress, contact_name, contact_number, external_property_number);
create index buyers_name_number_property on buyers (contact_name, contact_number, external_property_number);
create index buyers_adress_name_number_property on buyers (property_adress, contact_name, contact_number, external_property_number);

update firmas set vendedor = replace(vendedor, 'Aptos.', 'Apartamentos') where vendedor like '%Aptos.%';
update firmas set vendedor = replace(vendedor, 'Aptos', 'Apartamentos') where vendedor like '%Aptos%';
update firmas set vendedor = replace(vendedor, 'Glez.', 'González') where vendedor like '%Glez.%';
update firmas set vendedor = replace(vendedor, 'Glez', 'González') where vendedor like '%Glez%';
update firmas set vendedor = replace(vendedor, 'Rdgz.', 'González') where vendedor like '%Rdgz.%';
update firmas set vendedor = replace(vendedor, 'Rdgz', 'González') where vendedor like '%Rdgz%';
update firmas set vendedor = replace(vendedor, 'Fdez.', 'Fernández') where vendedor like '%Fdez.%';
update firmas set vendedor = replace(vendedor, 'Fdez', 'Fernández') where vendedor like '%Fdez%';
update firmas set vendedor = replace(vendedor, 'Ø', 'Oe') where vendedor like '%Ø%';
update firmas set vendedor = replace(vendedor, 'ø', 'Oe') where vendedor like '%ø%';
update firmas set vendedor = replace(vendedor, 'Venezia', 'Venecia') where vendedor like '%Venezia%';
update firmas set ref_vendedor = '{20342}' where id = '366';
update firmas set ref_comprador = '{10785}' where id = '25';
update firmas set vendedor = 'Malibú' where id = '210';

--CREATE EXTENSION unaccent;

-------------
-- vendedor
-------------

update firmas 
    set ref_vendedor = array_distinct(contact_number_agg), ref_prop = array_distinct(ref_prop_agg), step = 0
    from (
        select f.vendedor, f.comprador, f.vivienda, array_agg(v.contact_number) contact_number_agg, array_agg(v.external_property_number) as ref_prop_agg
            from (
                select property_adress, contact_name, contact_number, string_agg(comment, ' ') as comment, external_property_number
                from sellers
                group by property_adress, contact_name, contact_number, external_property_number) v, 
                firmas f 
            where unaccent(v.contact_name) ~* ('(^|\W)' || replace(unaccent(f.vendedor), ' ', '.') || '($|\W)') and
                  unaccent(v.comment) ~* ('Comprador[^\r\w][^\r]*' || replace(unaccent(f.comprador), ' ', '.') || '\W') and
                  unaccent(v.comment) ~* ('Vendedor[^\r\w][^\r]*' || replace(unaccent(f.vendedor), ' ', '.') || '\W') and
                  unaccent(v.comment) ~* ('Firma[^\r\w][^\r]*' || regexp_replace(unaccent(f.operacion), '\W', '.', 'g') || '\W') and
                  unaccent(v.property_adress) ~* ('\W' || replace(unaccent(f.vivienda), ' ', '.') || '\W')
            group by f.comprador, f.vendedor, f.vivienda) t
    where t.comprador = firmas.comprador and t.vendedor = firmas.vendedor and t.vivienda = firmas.vivienda and ref_vendedor is null;

update firmas 
    set ref_vendedor = array_distinct(contact_number_agg), ref_prop = array_distinct(ref_prop_agg), step = 1
    from (
        select f.vendedor, f.comprador, f.vivienda, array_agg(v.contact_number) contact_number_agg, array_agg(v.external_property_number) as ref_prop_agg
            from (
                select property_adress, contact_name, contact_number, string_agg(comment, ' ') as comment, external_property_number
                from sellers
                group by property_adress, contact_name, contact_number, external_property_number) v, 
                firmas f 
            where unaccent(v.contact_name) ~* ('(^|\W)' || replace(unaccent(f.vendedor), ' ', '.') || '($|\W)') and
                  unaccent(v.comment) ~* ('Comprador[^\r\w][^\r]*' || replace(unaccent(f.comprador), ' ', '.') || '\W') and
                  unaccent(v.comment) ~* ('Vendedor[^\r\w][^\r]*' || replace(unaccent(f.vendedor), ' ', '.') || '\W') and
                  unaccent(v.property_adress) ~* ('\W' || replace(unaccent(f.vivienda), ' ', '.') || '\W')
            group by f.comprador, f.vendedor, f.vivienda) t
    where t.comprador = firmas.comprador and t.vendedor = firmas.vendedor and t.vivienda = firmas.vivienda and ref_vendedor is null;

update firmas 
    set ref_vendedor = array_distinct(contact_number_agg), ref_prop = array_distinct(ref_prop_agg), step = 2
    from (
        select f.comprador, f.vendedor, f.vivienda, array_agg(v.contact_number) contact_number_agg, array_agg(v.external_property_number) as ref_prop_agg
            from (
                select contact_name, contact_number, string_agg(comment, ' ') as comment, external_property_number
                from sellers
                group by contact_name, contact_number, external_property_number) v, 
                firmas f 
            where unaccent(v.contact_name) ~* ('(^|\W)' || replace(unaccent(f.vendedor), ' ', '.') || '($|\W)') and
                  unaccent(v.comment) ~* ('Comprador[^\r\w][^\r]*' || replace(unaccent(f.comprador), ' ', '.') || '\W') and
                  unaccent(v.comment) ~* ('Firma[^\r\w][^\r]*' || regexp_replace(unaccent(f.operacion), '\W', '.', 'g') || '\W') and
                  unaccent(v.comment) ~* ('Vendedor[^\r\w][^\r]*' || replace(unaccent(f.vendedor), ' ', '.') || '\W')
            group by f.comprador, f.vendedor, f.vivienda) t
    where t.comprador = firmas.comprador and t.vendedor = firmas.vendedor and t.vivienda = firmas.vivienda and ref_vendedor is null;

update firmas 
    set ref_vendedor = array_distinct(contact_number_agg), ref_prop = array_distinct(ref_prop_agg), step = 2
    from (
        select f.comprador, f.vendedor, f.vivienda, array_agg(v.contact_number) contact_number_agg, array_agg(v.external_property_number) as ref_prop_agg
            from (
                select contact_name, contact_number, string_agg(comment, ' ') as comment, external_property_number
                from sellers
                group by contact_name, contact_number, external_property_number) v, 
                firmas f 
            where unaccent(v.contact_name) ~* ('(^|\W)' || replace(unaccent(f.vendedor), ' ', '.') || '($|\W)') and
                  unaccent(v.comment) ~* ('Comprador[^\r\w][^\r]*' || replace(unaccent(f.comprador), ' ', '.') || '\W') and
                  unaccent(v.comment) ~* ('Vendedor[^\r\w][^\r]*' || replace(unaccent(f.vendedor), ' ', '.') || '\W')
            group by f.comprador, f.vendedor, f.vivienda) t
    where t.comprador = firmas.comprador and t.vendedor = firmas.vendedor and t.vivienda = firmas.vivienda and ref_vendedor is null;

update firmas 
    set ref_vendedor = array_distinct(contact_number_agg), ref_prop = array_distinct(ref_prop_agg), step = 3
    from (
        select f.comprador, f.vendedor, f.vivienda, array_agg(v.contact_number) contact_number_agg, array_agg(v.external_property_number) as ref_prop_agg
            from (
                select property_adress, contact_name, contact_number, external_property_number
                from sellers
                group by property_adress, contact_name, contact_number, external_property_number) v, 
                firmas f 
            where unaccent(v.contact_name) ~* ('(^|\W)' || replace(unaccent(f.vendedor), ' ', '.') || '($|\W)') and
                  unaccent(v.property_adress) ~* ('(^|\W)' || replace(unaccent(f.vivienda), ' ', '.') || '\W')
            group by f.comprador, f.vendedor, f.vivienda) t
    where t.comprador = firmas.comprador and t.vendedor = firmas.vendedor and t.vivienda = firmas.vivienda and ref_vendedor is null;

update firmas 
    set ref_vendedor = array_distinct(contact_number_agg), ref_prop = array_distinct(ref_prop_agg), step = 4
    from (
        select f.comprador, f.vendedor, f.vivienda, array_agg(v.contact_number) contact_number_agg, array_agg(v.external_property_number) as ref_prop_agg
            from (
                select contact_name, contact_number, string_agg(comment, ' ') as comment, external_property_number
                from sellers
                group by contact_name, contact_number, external_property_number) v, 
                firmas f 
            where unaccent(v.comment) ~* ('Propiedad[^\r\w][^\r]*' || replace(unaccent(f.vivienda), ' ', '.') || '\W') and
                  unaccent(v.comment) ~* ('Firma[^\r\w][^\r]*' || regexp_replace(unaccent(f.operacion), '\W', '.', 'g') || '\W') and
                  unaccent(v.comment) ~* ('Comprador[^\r\w][^\r]*' || replace(unaccent(f.comprador), ' ', '.') || '\W')
            group by f.comprador, f.vendedor, f.vivienda) t
    where t.comprador = firmas.comprador and t.vendedor = firmas.vendedor and t.vivienda = firmas.vivienda and ref_vendedor is null;

update firmas 
    set ref_vendedor = array_distinct(contact_number_agg), ref_prop = array_distinct(ref_prop_agg), step = 4
    from (
        select f.comprador, f.vendedor, f.vivienda, array_agg(v.contact_number) contact_number_agg, array_agg(v.external_property_number) as ref_prop_agg
            from (
                select contact_name, contact_number, string_agg(comment, ' ') as comment, external_property_number
                from sellers
                group by contact_name, contact_number, external_property_number) v, 
                firmas f 
            where unaccent(v.comment) ~* ('Propiedad[^\r\w][^\r]*' || replace(unaccent(f.vivienda), ' ', '.') || '\W') and
                  unaccent(v.comment) ~* ('Comprador[^\r\w][^\r]*' || replace(unaccent(f.comprador), ' ', '.') || '\W')
            group by f.comprador, f.vendedor, f.vivienda) t
    where t.comprador = firmas.comprador and t.vendedor = firmas.vendedor and t.vivienda = firmas.vivienda and ref_vendedor is null;

update firmas 
    set ref_vendedor = array_distinct(contact_number_agg), ref_prop = array_distinct(ref_prop_agg), step = 5
    from (
        select f.comprador, f.vendedor, f.vivienda, array_agg(v.contact_number) contact_number_agg, array_agg(v.external_property_number) as ref_prop_agg
            from (
                select contact_name, contact_number, string_agg(comment, ' ') || string_agg(property_adress, ' ') as comment, external_property_number
                from sellers
                group by contact_name, contact_number, external_property_number) v, 
                firmas f 
            where unaccent(v.contact_name) ~* ('(^|\W)' || replace(unaccent(f.vendedor), ' ', '.') || '($|\W)') and
                  unaccent(v.comment) ~* ('Propiedad[^\r\w][^\r]*' || replace(unaccent(f.vivienda), ' ', '.') || '\W')
            group by f.comprador, f.vendedor, f.vivienda) t
    where t.comprador = firmas.comprador and t.vendedor = firmas.vendedor and t.vivienda = firmas.vivienda and ref_vendedor is null;

update firmas 
    set ref_vendedor = array_distinct(contact_number_agg), ref_prop = array_distinct(ref_prop_agg), step = 6
    from (
        select f.comprador, f.vendedor, f.vivienda, array_agg(v.contact_number) contact_number_agg, array_agg(v.external_property_number) as ref_prop_agg
            from (
                select contact_name, contact_number, external_property_number 
                from sellers
                group by contact_name, contact_number, external_property_number) v, 
                firmas f 
            where unaccent(v.contact_name) ~* ('(^|\W)' || replace(unaccent(f.vendedor), ' ', '.') || '($|\W)')
            group by f.comprador, f.vendedor, f.vivienda) t
    where t.comprador = firmas.comprador and t.vendedor = firmas.vendedor and t.vivienda = firmas.vivienda and ref_vendedor is null;

update firmas 
    set ref_vendedor = array_distinct(contact_number_agg), ref_prop = array_distinct(ref_prop_agg), step = 7
    from (
        select f.comprador, f.vendedor, f.vivienda, array_agg(v.contact_number) contact_number_agg, array_agg(v.external_property_number) as ref_prop_agg
            from (
                select contact_name, contact_number, string_agg(comment, ' ') as comment, external_property_number
                from sellers 
                group by contact_name, contact_number, external_property_number) v, 
                firmas f 
            where unaccent(v.comment) ~* ('Vendedor[^\r\w][^\r]*' || replace(unaccent(f.comprador), ' ', '.') || '\W')
            group by f.comprador, f.vendedor, f.vivienda) t
    where t.comprador = firmas.comprador and t.vendedor = firmas.vendedor and t.vivienda = firmas.vivienda and ref_vendedor is null;

update firmas 
    set ref_vendedor = array_distinct(contact_number_agg), ref_prop = array_distinct(ref_prop_agg), step = 9
    from (
        select f.comprador, f.vendedor, f.vivienda, array_agg(v.contact_number) contact_number_agg, array_agg(v.external_property_number) as ref_prop_agg
            from (
                select contact_name, contact_number, string_agg(comment, ' ') as comment, external_property_number
                from sellers 
                group by contact_name, contact_number, external_property_number) v, 
                firmas f 
            where unaccent(v.comment) ~* ('\W' || replace(unaccent(f.vendedor), ' ', '.') || '\W')
            group by f.comprador, f.vendedor, f.vivienda) t
    where t.comprador = firmas.comprador and t.vendedor = firmas.vendedor and t.vivienda = firmas.vivienda and ref_vendedor is null;

update firmas 
    set ref_vendedor = array_distinct(contact_number_agg), ref_prop = array_distinct(ref_prop_agg), step = 10
    from (
        select f.comprador, f.vendedor, f.vivienda, array_agg(v.contact_number) contact_number_agg, array_agg(v.external_property_number) as ref_prop_agg
            from (
                select contact_name, contact_number, external_property_number
                from sellers 
                group by contact_name, contact_number, external_property_number) v, 
                firmas f 
            where unaccent(v.contact_name) ~* ('(^|\W)' || substring(unaccent(f.vendedor), '(\w{4,})') || '($|\W)')
            group by f.comprador, f.vendedor, f.vivienda) t
    where t.comprador = firmas.comprador and t.vendedor = firmas.vendedor and t.vivienda = firmas.vivienda and ref_vendedor is null;

update firmas 
    set ref_vendedor = array_distinct(contact_number_agg), ref_prop = array_distinct(ref_prop_agg), step = 11
    from (
        select f.comprador, f.vendedor, f.vivienda, array_agg(v.contact_number) contact_number_agg, array_agg(v.external_property_number) as ref_prop_agg
            from (
                select contact_name, contact_number, external_property_number
                from sellers 
                group by contact_name, contact_number, external_property_number) v, 
                firmas f 
            where unaccent(v.contact_name) ~* ('(^|\W)' || substring(unaccent(f.vendedor), '\w{4,}\W+(\w{4,})') || '($|\W)')
            group by f.comprador, f.vendedor, f.vivienda) t
    where t.comprador = firmas.comprador and t.vendedor = firmas.vendedor and t.vivienda = firmas.vivienda and ref_vendedor is null;

update firmas 
    set ref_vendedor = array_distinct(contact_number_agg), ref_prop = array_distinct(ref_prop_agg), step = 12
    from (
        select f.comprador, f.vendedor, f.vivienda, array_agg(v.contact_number) contact_number_agg, array_agg(v.external_property_number) as ref_prop_agg
            from (
                select contact_name, contact_number, string_agg(comment, ' ') as comment, external_property_number
                from sellers 
                group by contact_name, contact_number, external_property_number) v, 
                firmas f 
            where unaccent(v.comment) ~* ('Vendedor[^\r\w][^\r]*' || substring(unaccent(f.vendedor), '(\w{4,})') || '\W')
            group by f.comprador, f.vendedor, f.vivienda) t
    where t.comprador = firmas.comprador and t.vendedor = firmas.vendedor and t.vivienda = firmas.vivienda and ref_vendedor is null;

update firmas 
    set ref_vendedor = array_distinct(contact_number_agg), ref_prop = array_distinct(ref_prop_agg), step = 13
    from (
        select f.comprador, f.vendedor, f.vivienda, array_agg(v.contact_number) contact_number_agg, array_agg(v.external_property_number) as ref_prop_agg
            from (
                select contact_name, contact_number, string_agg(comment, ' ') as comment, external_property_number
                from sellers 
                group by contact_name, contact_number, external_property_number) v, 
                firmas f 
            where unaccent(v.comment) ~* ('Vendedor[^\r\w][^\r]*' || substring(unaccent(f.vendedor), '\w{4,}\W+(\w{4,})') || '\W')
            group by f.comprador, f.vendedor, f.vivienda) t
    where t.comprador = firmas.comprador and t.vendedor = firmas.vendedor and t.vivienda = firmas.vivienda and ref_vendedor is null;

-------------
-- comprador
-------------

update firmas 
    set ref_comprador = array_distinct(contact_number_agg), ref_prop = array_distinct(array_cat(ref_prop::text[], ref_prop_agg)), step = 101
    from (
        select f.vendedor, f.comprador, f.vivienda, array_agg(v.contact_number) contact_number_agg, array_agg(v.external_property_number) as ref_prop_agg
            from (
                select property_adress, contact_name, contact_number, string_agg(comment, ' ') as comment, external_property_number
                from buyers
                group by property_adress, contact_name, contact_number, external_property_number) v, 
                firmas f 
            where unaccent(v.contact_name) ~* ('(^|\W)' || replace(unaccent(f.comprador), ' ', '.') || '($|\W)') and
                  unaccent(v.comment) ~* ('Comprador[^\r\w][^\r]*' || replace(unaccent(f.comprador), ' ', '.') || '\W') and
                  unaccent(v.comment) ~* ('Vendedor[^\r\w][^\r]*' || replace(unaccent(f.vendedor), ' ', '.') || '\W') and
                  unaccent(v.comment) ~* ('Firma[^\r\w][^\r]*' || regexp_replace(unaccent(f.operacion), '\W', '.', 'g') || '\W') and
                  unaccent(v.property_adress) ~* ('\W' || replace(unaccent(f.vivienda), ' ', '.') || '\W')
            group by f.comprador, f.vendedor, f.vivienda) t
    where t.comprador = firmas.comprador and t.vendedor = firmas.vendedor and t.vivienda = firmas.vivienda and ref_comprador is null;

update firmas 
    set ref_comprador = array_distinct(contact_number_agg), ref_prop = array_distinct(array_cat(ref_prop::text[], ref_prop_agg)), step = 101
    from (
        select f.vendedor, f.comprador, f.vivienda, array_agg(v.contact_number) contact_number_agg, array_agg(v.external_property_number) as ref_prop_agg
            from (
                select property_adress, contact_name, contact_number, string_agg(comment, ' ') as comment, external_property_number
                from buyers
                group by property_adress, contact_name, contact_number, external_property_number) v, 
                firmas f 
            where unaccent(v.contact_name) ~* ('(^|\W)' || replace(unaccent(f.comprador), ' ', '.') || '($|\W)') and
                  unaccent(v.comment) ~* ('Comprador[^\r\w][^\r]*' || replace(unaccent(f.comprador), ' ', '.') || '\W') and
                  unaccent(v.comment) ~* ('Vendedor[^\r\w][^\r]*' || replace(unaccent(f.vendedor), ' ', '.') || '\W') and
                  unaccent(v.property_adress) ~* ('\W' || replace(unaccent(f.vivienda), ' ', '.') || '\W')
            group by f.comprador, f.vendedor, f.vivienda) t
    where t.comprador = firmas.comprador and t.vendedor = firmas.vendedor and t.vivienda = firmas.vivienda and ref_comprador is null;

update firmas 
    set ref_comprador = array_distinct(contact_number_agg), ref_prop = array_distinct(array_cat(ref_prop::text[], ref_prop_agg)), step = 102
    from (
        select f.comprador, f.vendedor, f.vivienda, array_agg(v.contact_number) contact_number_agg, array_agg(v.external_property_number) as ref_prop_agg
            from (
                select contact_name, contact_number, string_agg(comment, ' ') as comment, external_property_number
                from buyers
                group by contact_name, contact_number, external_property_number) v, 
                firmas f 
            where unaccent(v.contact_name) ~* ('(^|\W)' || replace(unaccent(f.comprador), ' ', '.') || '($|\W)') and
                  unaccent(v.comment) ~* ('Comprador[^\r\w][^\r]*' || replace(unaccent(f.comprador), ' ', '.') || '\W') and
                  unaccent(v.comment) ~* ('Firma[^\r\w][^\r]*' || regexp_replace(unaccent(f.operacion), '\W', '.', 'g') || '\W') and
                  unaccent(v.comment) ~* ('Vendedor[^\r\w][^\r]*' || replace(unaccent(f.vendedor), ' ', '.') || '\W')
            group by f.comprador, f.vendedor, f.vivienda) t
    where t.comprador = firmas.comprador and t.vendedor = firmas.vendedor and t.vivienda = firmas.vivienda and ref_comprador is null;

update firmas 
    set ref_comprador = array_distinct(contact_number_agg), ref_prop = array_distinct(array_cat(ref_prop::text[], ref_prop_agg)), step = 102
    from (
        select f.comprador, f.vendedor, f.vivienda, array_agg(v.contact_number) contact_number_agg, array_agg(v.external_property_number) as ref_prop_agg
            from (
                select contact_name, contact_number, string_agg(comment, ' ') as comment, external_property_number
                from buyers
                group by contact_name, contact_number, external_property_number) v, 
                firmas f 
            where unaccent(v.contact_name) ~* ('(^|\W)' || replace(unaccent(f.comprador), ' ', '.') || '($|\W)') and
                  unaccent(v.comment) ~* ('Comprador[^\r\w][^\r]*' || replace(unaccent(f.comprador), ' ', '.') || '\W') and
                  unaccent(v.comment) ~* ('Vendedor[^\r\w][^\r]*' || replace(unaccent(f.vendedor), ' ', '.') || '\W')
            group by f.comprador, f.vendedor, f.vivienda) t
    where t.comprador = firmas.comprador and t.vendedor = firmas.vendedor and t.vivienda = firmas.vivienda and ref_comprador is null;

update firmas 
    set ref_comprador = array_distinct(contact_number_agg), ref_prop = array_distinct(array_cat(ref_prop::text[], ref_prop_agg)), step = 103
    from (
        select f.comprador, f.vendedor, f.vivienda, array_agg(v.contact_number) contact_number_agg, array_agg(v.external_property_number) as ref_prop_agg
            from (
                select property_adress, contact_name, contact_number, external_property_number
                from buyers
                group by property_adress, contact_name, contact_number, external_property_number) v, 
                firmas f 
            where unaccent(v.contact_name) ~* ('(^|\W)' || replace(unaccent(f.comprador), ' ', '.') || '($|\W)') and
                  unaccent(v.property_adress) ~* ('(^|\W)' || replace(unaccent(f.vivienda), ' ', '.') || '\W')
            group by f.comprador, f.vendedor, f.vivienda) t
    where t.comprador = firmas.comprador and t.vendedor = firmas.vendedor and t.vivienda = firmas.vivienda and ref_comprador is null;

update firmas 
    set ref_comprador = array_distinct(contact_number_agg), ref_prop = array_distinct(array_cat(ref_prop::text[], ref_prop_agg)), step = 104
    from (
        select f.comprador, f.vendedor, f.vivienda, array_agg(v.contact_number) contact_number_agg, array_agg(v.external_property_number) as ref_prop_agg
            from (
                select contact_name, contact_number, string_agg(comment, ' ') as comment, external_property_number
                from buyers
                group by contact_name, contact_number, external_property_number) v, 
                firmas f 
            where unaccent(v.comment) ~* ('Propiedad[^\r\w][^\r]*' || replace(unaccent(f.vivienda), ' ', '.') || '\W') and
                  unaccent(v.comment) ~* ('Firma[^\r\w][^\r]*' || regexp_replace(unaccent(f.operacion), '\W', '.', 'g') || '\W') and
                  unaccent(v.comment) ~* ('Comprador[^\r\w][^\r]*' || replace(unaccent(f.comprador), ' ', '.') || '\W')
            group by f.comprador, f.vendedor, f.vivienda) t
    where t.comprador = firmas.comprador and t.vendedor = firmas.vendedor and t.vivienda = firmas.vivienda and ref_comprador is null;

update firmas 
    set ref_comprador = array_distinct(contact_number_agg), ref_prop = array_distinct(array_cat(ref_prop::text[], ref_prop_agg)), step = 104
    from (
        select f.comprador, f.vendedor, f.vivienda, array_agg(v.contact_number) contact_number_agg, array_agg(v.external_property_number) as ref_prop_agg
            from (
                select contact_name, contact_number, string_agg(comment, ' ') as comment, external_property_number
                from buyers
                group by contact_name, contact_number, external_property_number) v, 
                firmas f 
            where unaccent(v.comment) ~* ('Propiedad[^\r\w][^\r]*' || replace(unaccent(f.vivienda), ' ', '.') || '\W') and
                  unaccent(v.comment) ~* ('Comprador[^\r\w][^\r]*' || replace(unaccent(f.comprador), ' ', '.') || '\W')
            group by f.comprador, f.vendedor, f.vivienda) t
    where t.comprador = firmas.comprador and t.vendedor = firmas.vendedor and t.vivienda = firmas.vivienda and ref_comprador is null;

update firmas 
    set ref_comprador = array_distinct(contact_number_agg), ref_prop = array_distinct(array_cat(ref_prop::text[], ref_prop_agg)), step = 105
    from (
        select f.comprador, f.vendedor, f.vivienda, array_agg(v.contact_number) contact_number_agg, array_agg(v.external_property_number) as ref_prop_agg
            from (
                select contact_name, contact_number, string_agg(comment, ' ') || string_agg(property_adress, ' ') as comment, external_property_number
                from buyers
                group by contact_name, contact_number, external_property_number) v, 
                firmas f 
            where unaccent(v.contact_name) ~* ('(^|\W)' || replace(unaccent(f.comprador), ' ', '.') || '($|\W)') and
                  unaccent(v.comment) ~* ('Propiedad[^\r\w][^\r]*' || replace(unaccent(f.vivienda), ' ', '.') || '\W')
            group by f.comprador, f.vendedor, f.vivienda) t
    where t.comprador = firmas.comprador and t.vendedor = firmas.vendedor and t.vivienda = firmas.vivienda and ref_comprador is null;

update firmas 
    set ref_comprador = array_distinct(contact_number_agg), ref_prop = array_distinct(array_cat(ref_prop::text[], ref_prop_agg)), step = 106
    from (
        select f.comprador, f.vendedor, f.vivienda, array_agg(v.contact_number) contact_number_agg, array_agg(v.external_property_number) as ref_prop_agg
            from (
                select contact_name, contact_number, external_property_number
                from buyers
                group by contact_name, contact_number, external_property_number) v, 
                firmas f 
            where unaccent(v.contact_name) ~* ('(^|\W)' || replace(unaccent(f.comprador), ' ', '.') || '($|\W)')
            group by f.comprador, f.vendedor, f.vivienda) t
    where t.comprador = firmas.comprador and t.vendedor = firmas.vendedor and t.vivienda = firmas.vivienda and ref_comprador is null;

update firmas 
    set ref_comprador = array_distinct(contact_number_agg), ref_prop = array_distinct(array_cat(ref_prop::text[], ref_prop_agg)), step = 107
    from (
        select f.comprador, f.vendedor, f.vivienda, array_agg(v.contact_number) contact_number_agg, array_agg(v.external_property_number) as ref_prop_agg
            from (
                select contact_name, contact_number, string_agg(comment, ' ') as comment, external_property_number
                from buyers 
                group by contact_name, contact_number, external_property_number) v, 
                firmas f 
            where unaccent(v.comment) ~* ('Vendedor[^\r\w][^\r]*' || replace(unaccent(f.comprador), ' ', '.') || '\W')
            group by f.comprador, f.vendedor, f.vivienda) t
    where t.comprador = firmas.comprador and t.vendedor = firmas.vendedor and t.vivienda = firmas.vivienda and ref_comprador is null;

update firmas 
    set ref_comprador = array_distinct(contact_number_agg), ref_prop = array_distinct(array_cat(ref_prop::text[], ref_prop_agg)), step = 109
    from (
        select f.comprador, f.vendedor, f.vivienda, array_agg(v.contact_number) contact_number_agg, array_agg(v.external_property_number) as ref_prop_agg
            from (
                select contact_name, contact_number, string_agg(comment, ' ') as comment, external_property_number
                from buyers 
                group by contact_name, contact_number, external_property_number) v, 
                firmas f 
            where unaccent(v.comment) ~* ('\W' || replace(unaccent(f.vendedor), ' ', '.') || '\W')
            group by f.comprador, f.vendedor, f.vivienda) t
    where t.comprador = firmas.comprador and t.vendedor = firmas.vendedor and t.vivienda = firmas.vivienda and ref_comprador is null;

update firmas 
    set ref_comprador = array_distinct(contact_number_agg), ref_prop = array_distinct(array_cat(ref_prop::text[], ref_prop_agg)), step = 110
    from (
        select f.comprador, f.vendedor, f.vivienda, array_agg(v.contact_number) contact_number_agg, array_agg(v.external_property_number) as ref_prop_agg
            from (
                select contact_name, contact_number, external_property_number
                from buyers 
                group by contact_name, contact_number, external_property_number) v, 
                firmas f 
            where unaccent(v.contact_name) ~* ('(^|\W)' || substring(unaccent(f.comprador), '(\w{4,})') || '($|\W)')
            group by f.comprador, f.vendedor, f.vivienda) t
    where t.comprador = firmas.comprador and t.vendedor = firmas.vendedor and t.vivienda = firmas.vivienda and ref_comprador is null;

update firmas 
    set ref_comprador = array_distinct(contact_number_agg), ref_prop = array_distinct(array_cat(ref_prop::text[], ref_prop_agg)), step = 111
    from (
        select f.comprador, f.vendedor, f.vivienda, array_agg(v.contact_number) contact_number_agg, array_agg(v.external_property_number) as ref_prop_agg
            from (
                select contact_name, contact_number, external_property_number
                from buyers 
                group by contact_name, contact_number, external_property_number) v, 
                firmas f 
            where unaccent(v.contact_name) ~* ('(^|\W)' || substring(unaccent(f.comprador), '\w{4,}\W+(\w{4,})') || '($|\W)')
            group by f.comprador, f.vendedor, f.vivienda) t
    where t.comprador = firmas.comprador and t.vendedor = firmas.vendedor and t.vivienda = firmas.vivienda and ref_comprador is null;

update firmas 
    set ref_comprador = array_distinct(contact_number_agg), ref_prop = array_distinct(array_cat(ref_prop::text[], ref_prop_agg)), step = 112
    from (
        select f.comprador, f.vendedor, f.vivienda, array_agg(v.contact_number) contact_number_agg, array_agg(v.external_property_number) as ref_prop_agg
            from (
                select contact_name, contact_number, string_agg(comment, ' ') as comment, external_property_number
                from buyers 
                group by contact_name, contact_number, external_property_number) v, 
                firmas f 
            where unaccent(v.comment) ~* ('Vendedor[^\r\w][^\r]*' || substring(unaccent(f.vendedor), '(\w{4,})') || '\W')
            group by f.comprador, f.vendedor, f.vivienda) t
    where t.comprador = firmas.comprador and t.vendedor = firmas.vendedor and t.vivienda = firmas.vivienda and ref_comprador is null;

update firmas 
    set ref_comprador = array_distinct(contact_number_agg), ref_prop = array_distinct(array_cat(ref_prop::text[], ref_prop_agg)), step = 113
    from (
        select f.comprador, f.vendedor, f.vivienda, array_agg(v.contact_number) contact_number_agg, array_agg(v.external_property_number) as ref_prop_agg
            from (
                select contact_name, contact_number, string_agg(comment, ' ') as comment, external_property_number
                from buyers 
                group by contact_name, contact_number, external_property_number) v, 
                firmas f 
            where unaccent(v.comment) ~* ('Vendedor[^\r\w][^\r]*' || substring(unaccent(f.vendedor), '\w{4,}\W+(\w{4,})') || '\W')
            group by f.comprador, f.vendedor, f.vivienda) t
    where t.comprador = firmas.comprador and t.vendedor = firmas.vendedor and t.vivienda = firmas.vivienda and ref_comprador is null;

select f.vendedor, f.comprador, f.vivienda, array_agg(v.contact_number) contact_number_agg, array_agg(v.external_property_number) as ref_prop_agg,
    (select array_agg(p) from (select regexp_replace((regexp_matches(string_agg(comment, ' '), 'Precio[^\r\d]+([\d,.]+)\W', 'ig'))[1], '[.,]', '', 'g') p) x) as prices
            from (
                select property_adress, contact_name, contact_number, string_agg(comment, ' ') as comment, external_property_number
                from sellers
                group by property_adress, contact_name, contact_number, external_property_number) v, 
                firmas f 
            where unaccent(v.contact_name) ~* ('(^|\W)' || replace(unaccent(f.vendedor), ' ', '.') || '($|\W)') and
                  unaccent(v.comment) ~* ('Comprador[^\r\w][^\r]*' || replace(unaccent(f.comprador), ' ', '.') || '\W') and
                  unaccent(v.comment) ~* ('Vendedor[^\r\w][^\r]*' || replace(unaccent(f.vendedor), ' ', '.') || '\W') and
                  unaccent(v.property_adress) ~* ('\W' || replace(unaccent(f.vivienda), ' ', '.') || '\W')
            group by f.comprador, f.vendedor, f.vivienda;

----------
-- precio
----------

update firmas set precio = prices
    from (
        select id, array_distinct(array_agg(price)) as prices
        from (
            select f.id, regexp_replace((regexp_matches(s.comment || '\n' || b.comment, 'Precio[^\r\d]+([\d,.]+)\W', 'ig'))[1], '\D', '', 'g') as price
            from firmas f,
                 sellers s,
                 buyers b
            where fill_price and
                  array_length(f.ref_vendedor::text[], 1) = 1 and
                  array_length(f.ref_comprador::text[], 1) = 1 and
                  array_length(f.ref_prop::text[], 1) = 1 and
                  unaccent(s.comment || '\n' || b.comment) ~* ('Firma[^\r\w][^\r]*' || regexp_replace(unaccent(f.operacion), '\W', '.', 'g') || '\W') and
                  s.contact_number = array_to_string(f.ref_vendedor::text[], ',') and
                  b.contact_number = array_to_string(f.ref_comprador::text[], ',') and
                  unaccent(s.property_adress) ~* ('\W' || replace(unaccent(f.vivienda), ' ', '.') || '\W') and
                  unaccent(b.property_adress) ~* ('\W' || replace(unaccent(f.vivienda), ' ', '.') || '\W')
            group by f.id, s.comment, b.comment) sq1
        group by id) sq2
    where firmas.id = sq2.id;

update firmas set precio = prices
    from (
        select id, array_distinct(array_agg(price)) as prices
        from (
            select f.id, regexp_replace((regexp_matches(s.comment || '\n' || b.comment, 'Precio[^\r\d]+([\d,.]+)\W', 'ig'))[1], '\D', '', 'g') as price
            from firmas f,
                 sellers s,
                 buyers b
            where fill_price and
                  array_length(f.ref_vendedor::text[], 1) = 1 and
                  array_length(f.ref_comprador::text[], 1) = 1 and
                  array_length(f.ref_prop::text[], 1) = 1 and
                  unaccent(s.comment || '\n' || b.comment) ~* ('Comprador[^\r\w][^\r]*' || replace(unaccent(f.comprador), ' ', '.') || '\W') and
                  unaccent(s.comment || '\n' || b.comment) ~* ('Vendedor[^\r\w][^\r]*' || replace(unaccent(f.vendedor), ' ', '.') || '\W') and
                  s.contact_number = array_to_string(f.ref_vendedor::text[], ',') and
                  b.contact_number = array_to_string(f.ref_comprador::text[], ',') and
                  unaccent(s.property_adress) ~* ('\W' || replace(unaccent(f.vivienda), ' ', '.') || '\W') and
                  unaccent(b.property_adress) ~* ('\W' || replace(unaccent(f.vivienda), ' ', '.') || '\W')
            group by f.id, s.comment, b.comment) sq1
        group by id) sq2
    where firmas.id = sq2.id;
