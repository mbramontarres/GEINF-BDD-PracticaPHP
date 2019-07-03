REM ********************************************************************************************************
REM Taula curses. Afegim la columna tempsEnregistrats
REM 	- Aquesta columna ens permet saber si ja s'han entrat tots els temps dels participants d'una cursa
REM     - Valors possibles: 'N'=No s'han entrat enregistrat els temps, 'S'=S'han enregistrat els temps
REM     - Tot i que els temps d'una cursa s'entren tots de cop, ens veiem obligats a afegir aquest camp
REM       per distingir les curses on tothom ha abandonat de les que no tenen cap temps entrat
REM     - Després d'afegir la columna l'alimentem amb un valor calculat 
REM ********************************************************************************************************
alter table curses add tempsEnregistrats char(1) default 'N' NOT NULL;
update curses set tempsEnregistrats='S' where exists (select * from participantscurses pc where pc.cursa=curses.codi and not temps is null);

REM ********************************************************************************************************
REM Taula vehicles. Afegim la columna habilitat
REM 	- Aquesta columna ens permet saber si un vehicle amb un propietari ha estat venut, tal com 
REM       es demana en les especificacions de la pràctica.
REM   - Valors possibles: 'N'=Vehicle venut, 'S'=Vehicle no venut
REM ********************************************************************************************************
alter table vehicles add habilitat char(1) default 'S' not null;


REM ********************************************************************************************************
REM Taula factures. Creació
REM 	- Inclou totes les columnes que es requereixen inicialment a l'enunciat de la pràctica.
REM   - S'han afegit, addicionalment, 2 columnes: codi i data_factura
REM   - El codi és de tipus sencer i actua com a clau primària. S'assigna automàtica i seqüencialment a partir de 1.
REM   - La data_factura és de tipus data i s'assigna automàticament amb la data actual quan es crea la factura 
REM   - També afegim les claus foranes corresponents a les columnes: cursa, vehicle i propietari
REM ********************************************************************************************************
DROP TABLE factures CASCADE CONSTRAINT;

CREATE TABLE factures (
  codi NUMBER(8,0) CONSTRAINT cp_factures PRIMARY KEY,
  cursa VARCHAR2(15) NOT NULL,
  vehicle VARCHAR2(10) NOT NULL,
  propietari VARCHAR2(15) NOT NULL,
  data_factura DATE NOT NULL,
  temps NUMBER(6,3) NOT NULL,
  cost_combustible NUMBER(4,2) NOT NULL,
  preu_servei NUMBER(4,2) NOT NULL,
  iva NUMBER(3,0) NOT NULL,
  total NUMBER(8,2) NOT NULL,
   CONSTRAINT cf_factures_curses
	FOREIGN KEY (cursa)
	REFERENCES curses (codi),
   CONSTRAINT cf_factures_vehicle
    FOREIGN KEY (vehicle)
    REFERENCES vehicles (codi),
   CONSTRAINT cf_factures_usuaris
    FOREIGN KEY (propietari)
    REFERENCES usuaris (alias)  
);


commit;
