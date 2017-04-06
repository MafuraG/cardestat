insert into transaction (external_id, option_signed_at, sale_price_euc, buyer_id, seller_id, property_id, created_at, updated_at)
    select f.id, f.fecha_firma::date, f.precio::integer*100, b.id, s.id, p.id, now(), now()
    from firmas f
         join contact s on ((f.ref_vendedor::text[])[1] = s.reference)
         join contact b on ((f.ref_comprador::text[])[1] = b.reference)
         join property p on ((f.ref_prop::text[])[1] = p.reference)
    where substring(f.precio, 1, 1) <> '{' and
          array_length(f.ref_vendedor::text[], 1) = 1 and
          array_length(f.ref_comprador::text[], 1) = 1 and
          array_length(f.ref_prop::text[], 1) = 1 and
          fecha_firma ~ '\d{4}\W\d{2}\W\d{2}';

insert into transaction (external_id, option_signed_at, sale_price_euc, buyer_id, seller_id, property_id, created_at)
    select f.id, f.fecha_firma::date, (f.precio::text[])[1]::integer*100, b.id, s.id, p.id, now()
    from firmas f
         join contact s on ((f.ref_vendedor::text[])[1] = s.reference)
         join contact b on ((f.ref_comprador::text[])[1] = b.reference)
         join property p on ((f.ref_prop::text[])[1] = p.reference)
    where substring(f.precio, 1, 1) = '{' and
          array_length(f.precio::text[], 1) = 1 and
          array_length(f.ref_vendedor::text[], 1) = 1 and
          array_length(f.ref_comprador::text[], 1) = 1 and
          array_length(f.ref_prop::text[], 1) = 1 and
          fecha_firma ~ '\d{4}\W\d{2}\W\d{2}';

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

