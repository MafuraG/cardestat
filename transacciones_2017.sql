drop table if exists firmas17;
select load_csv_file('firmas17', '/home/claudio/projects/cardestat/transacciones_2017.csv', 18);
update firmas17 set ref_prop = null where ref_prop = 'Sin referencia' or ref_prop = 'Sin ref.' or ref_prop = 'Colaborador';

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
    f.fecha_opcion::date,
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
    f.fecha_opcion::date,
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

--delete from transaction where external_id = '690';
insert into transaction(
    external_id,
    option_signed_at,
    sale_price_euc,
    buyer_id,
    seller_id,
    property_id,
    transaction_type)
select 719, '2016-09-30', 23650000, b.id, s.id, p.id, 'COMPRAVENTA'
from contact s,
     contact b,
     property p
where s.reference = '21774' and
      b.reference = '24549' and 
      p.reference = '23008-RK';

insert into transaction(
    external_id,
    option_signed_at,
    sale_price_euc,
    buyer_id,
    seller_id,
    property_id,
    transaction_type)
select 721, '2017-01-31', 9400000, b.id, s.id, p.id, 'COMPRAVENTA'
from contact s,
     contact b,
     property p
where s.reference = '10693' and
      b.reference = '21853' and 
      p.reference = '23259-RK';

insert into transaction(
    external_id,
    option_signed_at,
    sale_price_euc,
    buyer_id,
    seller_id,
    property_id,
    transaction_type)
select 725, '2016-12-23', 12100000, b.id, s.id, p.id, 'COMPRAVENTA'
from contact s,
     contact b,
     property p
where s.reference = '3693' and
      b.reference = '21485' and 
      p.reference = '23182-RK';

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

