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

select load_csv_file('firmas', '/home/claudio/Downloads/Firmas incl. colab. 2017.csv', 21);

insert into partner (name)
    select distinct upper(colab_comprador)
    from firmas f
         left join partner p on (p.name = upper(colab_comprador))
    where colab_comprador is not null and
          p.name is null;

insert into partner (name)
    select distinct upper(colab_vendedor)
    from firmas f
         left join partner p on (p.name = upper(colab_vendedor))
    where colab_vendedor is not null and
          p.name is null;

insert into transaction (
    external_id,
    option_signed_at,
    sale_price_euc,
    buyer_id,
    seller_id,
    property_id,
    created_at,
    transaction_type,
    buyer_provider,
    seller_provider)
select distinct on (f.id)
    f.id,
    f.fecha_firma::date,
    regexp_replace(f.precio, '[, € ]', '', 'g')::float*100,
    b.id,
    s.id,
    p.id,
    now(),
    coalesce(upper(asesoramiento), 'COMPRAVENTA'),
    upper(colab_comprador),
    upper(colab_vendedor)
from firmas f
         join contact s on (f.ref_vendedor = s.reference)
         join contact b on (f.ref_comprador = b.reference)
         join property p on (f.ref_prop = p.reference);

insert into attribution (advisor_id, attribution_type_id, amount_euc, transaction_id, created_at)
    select ad.id, at.id, 0, t.id, now()
    from firmas f
         join transaction t on (t.external_id = f.id)
         join attribution_type at on (at.name = 'DESCONOCIDO' and attribution_bp = 0),
         advisor ad
    where 'RA' = any (regexp_split_to_array(comercial, '/')) and ad.name = 'RAFAEL ALZOLA' or
          'GG' = any (regexp_split_to_array(comercial, '/')) and ad.name = 'GILBERTO GIL' or
          'LL' = any (regexp_split_to_array(comercial, '/')) and ad.name = 'LONNIE LINDQUIST' or
          'LM' = any (regexp_split_to_array(comercial, '/')) and ad.name = 'LEONOR MARTÍN' or
          'CG' = any (regexp_split_to_array(comercial, '/')) and ad.name = 'CAROLINA GARCÍA' or
          'AK' = any (regexp_split_to_array(comercial, '/')) and ad.name = 'AXEL KUBISCH' or
          'DG' = any (regexp_split_to_array(comercial, '/')) and ad.name = 'DANIEL GARCÍA' or
          'SB' = any (regexp_split_to_array(comercial, '/')) and ad.name = 'STEPHAN BERGONJE' or
          'CM' = any (regexp_split_to_array(comercial, '/')) and ad.name = 'CARINA MAEHLE' or
          'KB' = any (regexp_split_to_array(comercial, '/')) and ad.name = 'KENT BERGSTEN' or
          'IH' = any (regexp_split_to_array(comercial, '/')) and ad.name = 'INGE HILDEBRANDT' or
          'DT' = any (regexp_split_to_array(comercial, '/')) and ad.name = 'DEBORAH TESCH' or
          'YW' = any (regexp_split_to_array(comercial, '/')) and ad.name = 'YVONNE WEERTS' or
          'TB' = any (regexp_split_to_array(comercial, '/')) and ad.name = 'THERESA BONA' or
          'TE' = any (regexp_split_to_array(comercial, '/')) and ad.name = 'THOMAS EKBLOM' or
          'CC' = any (regexp_split_to_array(comercial, '/')) and ad.name = 'CRISTINA CARUSO'

