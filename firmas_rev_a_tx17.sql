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

drop table if exists firmas17;
select load_csv_file('firmas17', '/home/claudio/projects/cardestat/Transacciones 2017.csv', 18);
update firmas17 set ref_prop = null where ref_prop = 'Sin referencia' or ref_prop = 'Sin ref.' or ref_prop = 'Colaborador';

--insert into property (reference, entry_date, active_date, inactive_date, property_type, location, building_complex, geo_coordinates, plot_area_dm2, built_area_dm2, n_bedrooms) values 
--    ('23447-RS', '2016-08-01', '2016-08-01', '2016-09-01', 'Villa', 'Maspalomas_Meloneras', null, '27.744695, -15.610053', 66800, 39100, 5),
--    ('OB-V11574', '2013-05-01', '2013-05-01', '2013-05-08', 'Apartment', 'Playa del Inglés', 'Ecuador', '27.754808, -15.572702', null, 3464, 1),
--    ('OB-V32483', '2016-05-13', '2016-05-13', '2017-04-03', 'Duplex', 'Meloneras', null, '27.754808, -15.572702', null, 8990, 2);

--insert into contact (reference, first_name, last_name, nationality, type_of_data, contact_source, country_of_residence) values 
    --('24332', 'Victor', 'Basistyi', 'Russia', 'Buyer Gestoría_client,Fiscalidad_client,+500', 'Internet', 'Rusia'),
    --('24322', 'Igor', 'Necajev', 'Lithuania', 'Buyer', null, 'Lithuania'),
    --('24348', 'Siri Andestad', 'Skarpnes', 'Norway', 'Buyer', 'Collaboration', 'Noruega'),
    --('24453', 'Merete', 'Björnsen', 'Sweden', 'Buyer', 'Internet', 'Suecia'),
    --('24511', 'Ramon Bernardo', 'Rodriguez Cabral', 'Spain', 'Buyer', 'Collaborator - ACEGI', 'España'),
    --('24537', null, 'MACARONESIA REAL ESTATE', null, 'Client', 'Collaboration', 'España'),
    --('24550', 'Alejandro', 'Rodriguez Hernandez', 'Spain', 'Seller', 'Collaborator - ACEGI', 'España');

insert into partner (name)
    select distinct upper(colab_comprador)
    from firmas17 f
         left join partner p on (p.name = upper(colab_comprador))
    where colab_comprador is not null and
          p.name is null;

insert into partner (name)
    select distinct upper(colab_vendedor)
    from firmas17 f
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
    coalesce(upper(asesoramiento), 'COMPRAVENTA'),
    upper(colab_comprador),
    upper(colab_vendedor)
from firmas17 f
    join contact s on (f.ref_vendedor = s.reference)
    join contact b on (f.ref_comprador = b.reference)
    join property p on (f.ref_prop = p.reference)
    left join transaction t on (f.id = t.external_id)
where f.ref_prop is not null and -- redundant, but kept for clarity
      t.id is null;

insert into transaction (
    external_id,
    option_signed_at,
    sale_price_euc,
    buyer_id,
    seller_id,
    property_id,
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
    coalesce(upper(asesoramiento), 'COMPRAVENTA'),
    upper(colab_comprador),
    upper(colab_vendedor)
from firmas17 f
    join contact s on (f.ref_vendedor = s.reference)
    join contact b on (f.ref_comprador = b.reference)
    left join property p on (f.ref_prop = p.reference)
    left join transaction t on (f.id = t.external_id)
where f.ref_prop is null and
      t.id is null;

--insert into transaction(
--    external_id,
--    option_signed_at,
--    sale_price_euc,
--    buyer_id,
--    seller_id,
--    property_id,
--    transaction_type)
--select 689, '2016-06-16', 23600000, b.id, s.id, p.id, 'COMPRAVENTA'
--from contact s,
--     contact b,
--     property p
--where s.reference = '21774' and
--      b.reference = '1937' and 
--      p.reference = '23008-RK';
--
--delete from transaction where external_id = '690';
--insert into transaction(
--    external_id,
--    option_signed_at,
--    sale_price_euc,
--    buyer_id,
--    seller_id,
--    property_id,
--    transaction_type)
--select 690, '2016-07-21', 14450000, b.id, s.id, p.id, 'COMPRAVENTA'
--from contact s,
--     contact b,
--     property p
--where s.reference = '21774' and
--      b.reference = '1937' and 
--      p.reference = '23008-RK';
--
--insert into transaction(
--    external_id,
--    option_signed_at,
--    sale_price_euc,
--    buyer_id,
--    seller_id,
--    property_id,
--    transaction_type)
--select '563', '2016-04-22', 23900000, b.id, s.id, p.id, 'COMPRAVENTA'
--from contact s,
--     contact b,
--     property p
--where s.reference = '4249' and
--      b.reference = '7534' and 
--      p.reference = '23091-RK';
--
--insert into transaction (
--    external_id,
--    option_signed_at,
--    sale_price_euc,
--    seller_id,
--    property_id,
--    transaction_type,
--    custom_type,
--    transfer_type,
--    development_type,
--    first_published_price_euc,
--    last_published_price_euc,
--    suggested_sale_price_euc,
--    is_new_buyer,
--    our_fee_euc,
--    buyer_provider,
--    seller_provider)
--select '559', '2016-12-27', 29800000, s.id, p.id, 'COMPRAVENTA', 'MULTIEXCLUSIVA', 'NUEVA CONSTRUCCIÓN', 'EN CONSTRUCCIÓN', 31250000, 31250000, 29800000, true, 5960, 'THE SELLER', 'RECOMMENDED (PASSIVE)'
--from contact s,
--     property p
--where s.reference = '20798' and
--      p.reference = '22833-RK';

insert into attribution (advisor_id, attribution_type_id, amount_euc, transaction_id, created_at)
    select ad.id, at.id, 0, t.id, now()
    from firmas17 f
         join transaction t on (t.external_id = f.id)
         join attribution_type at on (at.name = 'DESCONOCIDO' and attribution_bp = 0),
         advisor ad
    where 'RA' = any (regexp_split_to_array(comercial, '/')) and ad.name = 'RAFAEL ALZOLA' or
          'GG' = any (regexp_split_to_array(comercial, '/')) and ad.name = 'GILBERTO GIL' or
          'LL' = any (regexp_split_to_array(comercial, '/')) and ad.name = 'LONNIE LINDQUIST' or
          'LM' = any (regexp_split_to_array(comercial, '/')) and ad.name = 'LEONOR MARTÍN' or
          'CG' = any (regexp_split_to_array(comercial, '/')) and ad.name = 'CARLOS GÓMEZ' or
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
          'CC' = any (regexp_split_to_array(comercial, '/')) and ad.name = 'CRISTINA CARUSO';

