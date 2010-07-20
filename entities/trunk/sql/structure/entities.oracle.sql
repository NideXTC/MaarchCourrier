CREATE SEQUENCE GROUPB_REDIRECT_SYSTEM_ID_SEQ
  START WITH 1
  MAXVALUE 999999999999999999999999999
  MINVALUE 1
  NOCYCLE
  NOCACHE
  NOORDER;
  
  CREATE TABLE USERS_ENTITIES
(
  USER_ID         VARCHAR2(32 BYTE)             NOT NULL,
  ENTITY_ID       VARCHAR2(32 BYTE)             NOT NULL,
  USER_ROLE       VARCHAR2(255 BYTE),
  PRIMARY_ENTITY  CHAR(1 BYTE)                  DEFAULT 'N'                   
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

CREATE TABLE LISTINSTANCE
(
  COLL_ID            VARCHAR2(50 BYTE)          NOT NULL,
  RES_ID             NUMBER                     NOT NULL,
  LISTINSTANCE_TYPE  VARCHAR2(50 BYTE)          DEFAULT 'DOC',
  SEQUENCE           NUMBER                     NOT NULL,
  ITEM_ID            VARCHAR2(50 BYTE)          NOT NULL,
  ITEM_TYPE          VARCHAR2(255 BYTE)         NOT NULL,
  ITEM_MODE          VARCHAR2(50 BYTE)          NOT NULL,
  ADDED_BY_USER      VARCHAR2(50 BYTE)          ,
  ADDED_BY_ENTITY    VARCHAR2(50 BYTE)          ,
  VIEWED             NUMBER
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

CREATE TABLE LISTMODELS
(
  COLL_ID         VARCHAR2(50 BYTE)             NOT NULL,
  OBJECT_ID       VARCHAR2(50 BYTE)             NOT NULL,
  OBJECT_TYPE     VARCHAR2(255 BYTE)            ,
  SEQUENCE        NUMBER                        ,
  ITEM_ID         VARCHAR2(50 BYTE)             ,
  ITEM_TYPE       VARCHAR2(255 BYTE)            ,
  ITEM_MODE       VARCHAR2(50 BYTE)             ,
  LISTMODEL_TYPE  VARCHAR2(50 BYTE)             DEFAULT 'DOC'
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

CREATE TABLE ENTITIES
(
  ENTITY_ID         VARCHAR2(32 BYTE)           NOT NULL,
  ENTITY_LABEL      VARCHAR2(255 BYTE),
  SHORT_LABEL       VARCHAR2(50 BYTE),
  ENABLED           CHAR(1 BYTE)                DEFAULT 'Y'                   ,
  ADRS_1            VARCHAR2(255 BYTE),
  ADRS_2            VARCHAR2(255 BYTE),
  ADRS_3            VARCHAR2(255 BYTE),
  ZIPCODE           VARCHAR2(32 BYTE),
  CITY              VARCHAR2(255 BYTE),
  COUNTRY           VARCHAR2(255 BYTE),
  EMAIL             VARCHAR2(255 BYTE),
  BUSINESS_ID       VARCHAR2(32 BYTE),
  PARENT_ENTITY_ID  VARCHAR2(32 BYTE),
  ENTITY_TYPE       VARCHAR2(64 BYTE)
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


CREATE TABLE GROUPBASKET_REDIRECT
(
  SYSTEM_ID      INTEGER                        NOT NULL,
  GROUP_ID       VARCHAR2(32 BYTE)              NOT NULL,
  BASKET_ID      VARCHAR2(32 BYTE)              NOT NULL,
  ACTION_ID      INTEGER                        NOT NULL,
  ENTITY_ID      VARCHAR2(32 BYTE),
  KEYWORD        VARCHAR2(255 BYTE),
  REDIRECT_MODE  VARCHAR2(32 BYTE)              
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


CREATE OR REPLACE TRIGGER t_groupbasket_redirect_ins
   BEFORE INSERT
   ON groupbasket_redirect
   REFERENCING NEW AS NEW OLD AS OLD
   FOR EACH ROW
BEGIN
   SELECT groupb_redirect_system_id_seq.NEXTVAL
     INTO :NEW.system_id
     FROM DUAL;
EXCEPTION
   WHEN OTHERS
   THEN
      RAISE;
END  t_groupbasket_redirect_ins;
/
SHOW ERRORS;

ALTER TABLE USERS_ENTITIES ADD (
  CONSTRAINT USERS_ENTITIES_PKEY
 PRIMARY KEY
 (USER_ID, ENTITY_ID));
 
 ALTER TABLE ENTITIES ADD (
  CONSTRAINT ENTITIES_PKEY
 PRIMARY KEY
 (ENTITY_ID));
 
 
ALTER TABLE GROUPBASKET_REDIRECT ADD (
  CONSTRAINT GROUPBASKET_REDIRECT_PKEY
 PRIMARY KEY
 (SYSTEM_ID));