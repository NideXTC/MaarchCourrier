CREATE SEQUENCE RES_ATTACHMENT_RES_ID_SEQ
  START WITH 1
  MAXVALUE 999999999999999999999999999
  MINVALUE 1
  NOCYCLE
  NOCACHE
  NOORDER;
  
  CREATE TABLE RES_ATTACHMENTS
(
  RES_ID             NUMBER                     NOT NULL,
  TITLE              VARCHAR2(255 BYTE)         DEFAULT NULL,
  SUBJECT            VARCHAR2(2048 BYTE)         DEFAULT NULL,
  DESCRIPTION        VARCHAR2(2048 BYTE)         DEFAULT NULL,
  PUBLISHER          VARCHAR2(255 BYTE)         DEFAULT NULL,
  CONTRIBUTOR        VARCHAR2(255 BYTE)         DEFAULT NULL,
  TYPE_ID            NUMBER,
  FORMAT             VARCHAR2(50 BYTE)          NOT NULL,
  TYPIST             VARCHAR2(50 BYTE)          NOT NULL,
  CREATION_DATE      DATE               DEFAULT sysdate,
  FULLTEXT_RESULT    VARCHAR2(10 BYTE)          DEFAULT NULL,
  OCR_RESULT         VARCHAR2(10 BYTE)          DEFAULT NULL,
  AUTHOR             VARCHAR2(255 BYTE)         DEFAULT NULL,
  AUTHOR_NAME        VARCHAR2(2048 BYTE)         DEFAULT NULL,
  IDENTIFIER         VARCHAR2(255 BYTE)         DEFAULT NULL,
  SOURCE             VARCHAR2(255 BYTE)         DEFAULT NULL,
  DOC_LANGUAGE       VARCHAR2(50 BYTE)          DEFAULT NULL,
  RELATION           NUMBER,
  COVERAGE           VARCHAR2(255 BYTE)         DEFAULT NULL,
  DOC_DATE           DATE,
  DOCSERVER_ID       VARCHAR2(32 BYTE)          NOT NULL,
  FOLDERS_SYSTEM_ID  NUMBER,
  ARBOX_ID           VARCHAR2(32 BYTE)          DEFAULT NULL,
  PATH               VARCHAR2(255 BYTE)         DEFAULT NULL,
  FILENAME           VARCHAR2(255 BYTE)         DEFAULT NULL,
  OFFSET_DOC         VARCHAR2(255 BYTE)         DEFAULT NULL,
  LOGICAL_ADR        VARCHAR2(255 BYTE)         DEFAULT NULL,
  FINGERPRINT        VARCHAR2(255 BYTE)         DEFAULT NULL,
  FILESIZE           NUMBER,
  IS_PAPER           CHAR(1 BYTE)               DEFAULT NULL,
  PAGE_COUNT         INTEGER,
  SCAN_DATE          DATE,
  SCAN_USER          VARCHAR2(50 BYTE)          DEFAULT NULL,
  SCAN_LOCATION      VARCHAR2(255 BYTE)         DEFAULT NULL,
  SCAN_WKSTATION     VARCHAR2(255 BYTE)         DEFAULT NULL,
  SCAN_BATCH         VARCHAR2(50 BYTE)          DEFAULT NULL,
  BURN_BATCH         VARCHAR2(50 BYTE)          DEFAULT NULL,
  SCAN_POSTMARK      VARCHAR2(50 BYTE)          DEFAULT NULL,
  ENVELOP_ID         NUMBER,
  STATUS             VARCHAR2(10 BYTE)           DEFAULT NULL,
  DESTINATION        VARCHAR2(50 BYTE)          DEFAULT NULL,
  APPROVER           VARCHAR2(50 BYTE)          DEFAULT NULL,
  VALIDATION_DATE    DATE,
  WORK_BATCH         NUMBER,
  ORIGIN             VARCHAR2(50 BYTE)          DEFAULT NULL,
  IS_INGOING         CHAR(1 BYTE)               DEFAULT NULL,
  PRIORITY           INTEGER,
  INITIATOR          VARCHAR2(50 BYTE)          DEFAULT NULL,
  DEST_USER          VARCHAR2(50 BYTE)          DEFAULT NULL,
  COLL_ID            VARCHAR2(32 BYTE)          ,
  RES_ID_MASTER      NUMBER
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

CREATE OR REPLACE TRIGGER t_res_attachments_ins
   BEFORE INSERT
   ON res_attachments
   REFERENCING NEW AS NEW OLD AS OLD
   FOR EACH ROW
BEGIN
   SELECT res_attachment_res_id_seq.NEXTVAL
     INTO :NEW.res_id
     FROM DUAL;
EXCEPTION
   WHEN OTHERS
   THEN
      RAISE;
END t_res_attachments_ins;
/
SHOW ERRORS;

ALTER TABLE RES_ATTACHMENTS ADD (
  CONSTRAINT RES_ATTACHMENTS_PKEY
 PRIMARY KEY
 (RES_ID));