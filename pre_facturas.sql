select load_csv_file('facturas', '/home/claudio/Downloads/facturas2016.csv', 7);

update facturas set vendedor = replace(vendedor, 'APTOS. ', '');
update facturas set vendedor = replace(vendedor, 'URB. ', '');
update facturas set vendedor = regexp_replace(vendedor, ',.*', '');
update facturas set vendedor = regexp_replace(vendedor, '/.*', '');
update facturas set vendedor = 'BUGALLAL' where vendedor = 'FDEZ-BUGALLAL Y BARRON';
update facturas set vendedor = 'SOLVIA' where vendedor = 'SOLVIA SERV. INMOBILIARIOS';
update facturas set vendedor = 'PARADISE' where vendedor = 'PARADISE LIVING';
update facturas set vendedor = 'VENTURA' where vendedor = 'PROMOCIONES VENTURA CANARIAS';
update facturas set vendedor = 'FELIZ' where vendedor = 'PTO. FELIZ';
update facturas set vendedor = 'ARQUITECTURA' where vendedor = 'GRUPO ARQUITECTURA Y URB.';
update facturas set vendedor = 'MRKWICKA' where vendedor = 'MRKWICKA-NOLAN';
update facturas set vendedor = 'VENECIA' where vendedor ilike '%venecia%';
update facturas set vendedor = regexp_replace(vendedor, ' .*', '') where length(vendedor) >= 7;
update facturas set nombre = 'PSZCOLINSKI' where nombre = 'PIOTR PSZCOLINSKI';
update facturas set nombre = regexp_replace(nombre, ',.*', '');
update facturas set nombre = regexp_replace(nombre, '/.*', '');
update facturas set nombre = regexp_replace(nombre, '\(.*', '');
update facturas set nombre = regexp_replace(nombre, ' .*', '') where length(nombre) >= 7;

update facturas set firma = f.id
    from firmas f
    where unaccent(f.vendedor) ilike ('%' || unaccent(facturas.vendedor) || '%') and
          unaccent(f.comprador) ilike ('%' || unaccent(facturas.nombre) || '%');

update facturas set firma = '410' where num = '117634';
