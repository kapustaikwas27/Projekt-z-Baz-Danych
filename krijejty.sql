DROP TABLE impreza_typ;
DROP TABLE uzytkownik_typ;
DROP TABLE typ;
DROP TABLE impreza;
DROP TABLE uzytkownik;
DROP SEQUENCE ciag;

CREATE TABLE typ (
    id NUMBER(4) PRIMARY KEY,
    nazwa VARCHAR(20)
);

CREATE TABLE impreza (
    id NUMBER(4) PRIMARY KEY,
    data_pocz DATE NOT NULL,
    data_kon DATE NOT NULL,
    nazwa VARCHAR(100) NOT NULL,
    url_imprezy VARCHAR(300),
    miejsce VARCHAR(300) NOT NULL,
    url_miejsca VARCHAR(300),
    opis VARCHAR(400)
);

CREATE TABLE uzytkownik (
    id NUMBER(4) NOT NULL,
    przydomek VARCHAR(100) NOT NULL,
    haslo VARCHAR(100) NOT NULL
);

ALTER TABLE uzytkownik
ADD CONSTRAINT uzytkownik_pk PRIMARY KEY(id);

CREATE SEQUENCE ciag;

CREATE OR REPLACE TRIGGER na_uzytkownika
BEFORE INSERT ON uzytkownik
FOR EACH ROW
BEGIN
    SELECT ciag.nextval
    INTO :NEW.id
    FROM DUAL;
END;
/

CREATE TABLE impreza_typ (
    id_imprezy NUMBER(4) NOT NULL REFERENCES impreza,
    id_typu NUMBER(4) NOT NULL REFERENCES typ,
    CONSTRAINT impreza_typ_pk PRIMARY KEY (id_imprezy, id_typu)
);

CREATE TABLE uzytkownik_typ (
    id_uzytkownika NUMBER(4) NOT NULL REFERENCES uzytkownik,
    id_typu NUMBER(4) NOT NULL REFERENCES typ,
    CONSTRAINT uzytkownik_typ_pk PRIMARY KEY (id_uzytkownika, id_typu)
);

ALTER SESSION SET NLS_DATE_FORMAT = 'YYYY-MM-DD';

COMMIT;
