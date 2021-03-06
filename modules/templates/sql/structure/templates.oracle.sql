
CREATE SEQUENCE TEMPLATES_ASSOCIATION_SEQ
  START WITH 1
  MAXVALUE 999999999999999999999999999
  MINVALUE 1
  NOCYCLE
  NOCACHE
  NOORDER;


CREATE SEQUENCE TEMPLATES_SEQ
  START WITH 1
  MAXVALUE 999999999999999999999999999
  MINVALUE 1
  NOCYCLE
  NOCACHE
  NOORDER;

CREATE TABLE TEMPLATES
(
  ID                NUMBER                      NOT NULL,
  LABEL             VARCHAR2(50 BYTE)           DEFAULT NULL,
  CREATION_DATE     DATE                DEFAULT sysdate,
  TEMPLATE_COMMENT  VARCHAR2(255 BYTE)          DEFAULT NULL,
  CONTENT           CLOB
)
PCTUSED    0
PCTFREE    10
INITRANS   1
MAXTRANS   255
STORAGE    (
            INITIAL          64K
            MINEXTENTS       1
            MAXEXTENTS       2147483645
            PCTINCREASE      0
            BUFFER_POOL      DEFAULT
           )
LOGGING 
NOCOMPRESS 
NOCACHE
NOPARALLEL
MONITORING;


CREATE TABLE TEMPLATES_ASSOCIATION
(
  TEMPLATE_ID    NUMBER                         NOT NULL,
  WHAT           VARCHAR2(255 BYTE)             ,
  VALUE_FIELD    VARCHAR2(255 BYTE)             ,
  SYSTEM_ID      NUMBER                         ,
  MAARCH_MODULE  VARCHAR2(255 BYTE)             DEFAULT 'APPS'                
)
PCTUSED    0
PCTFREE    10
INITRANS   1
MAXTRANS   255
STORAGE    (
            INITIAL          64K
            MINEXTENTS       1
            MAXEXTENTS       2147483645
            PCTINCREASE      0
            BUFFER_POOL      DEFAULT
           )
LOGGING 
NOCOMPRESS 
NOCACHE
NOPARALLEL
MONITORING;


CREATE TABLE TEMPLATES_DOCTYPE_EXT
(
  TEMPLATE_ID   NUMBER                          DEFAULT NULL,
  TYPE_ID       INTEGER                         NOT NULL,
  IS_GENERATED  CHAR(1 BYTE)                    DEFAULT 'N'                   
)
PCTUSED    0
PCTFREE    10
INITRANS   1
MAXTRANS   255
STORAGE    (
            INITIAL          64K
            MINEXTENTS       1
            MAXEXTENTS       2147483645
            PCTINCREASE      0
            BUFFER_POOL      DEFAULT
           )
LOGGING 
NOCOMPRESS 
NOCACHE
NOPARALLEL
MONITORING;

CREATE OR REPLACE TRIGGER T_templates_ins
   BEFORE INSERT
   ON templates
   REFERENCING NEW AS NEW OLD AS OLD
   FOR EACH ROW
BEGIN
      SELECT templates_seq.NEXTVAL
        INTO :NEW.id
        FROM DUAL;
   
EXCEPTION
   WHEN OTHERS
   THEN
      RAISE;
END T_TEMPLATES_INS;
/
SHOW ERRORS;


CREATE OR REPLACE TRIGGER T_templates_association_ins
   BEFORE INSERT
   ON templates_association
   REFERENCING NEW AS NEW OLD AS OLD
   FOR EACH ROW
BEGIN
      SELECT templates_association_seq.NEXTVAL
        INTO :NEW.system_id
        FROM DUAL;
   
EXCEPTION
   WHEN OTHERS
   THEN
      RAISE;
END T_TEMPLATES_ASSOCIATION_INS;
/
SHOW ERRORS;

ALTER TABLE TEMPLATES ADD (
  CONSTRAINT TEMPLATES_PKEY
 PRIMARY KEY
 (ID));

ALTER TABLE TEMPLATES_ASSOCIATION ADD (
  CONSTRAINT TEMPLATES_ASSOCIATION_PKEY
 PRIMARY KEY
 (SYSTEM_ID));
