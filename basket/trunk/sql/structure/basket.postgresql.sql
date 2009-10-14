CREATE TABLE actions_groupbaskets
(
  id_action bigint NOT NULL,
  where_clause text,
  group_id character varying(32) NOT NULL,
  basket_id character varying(32) NOT NULL,
  used_in_basketlist character(1) NOT NULL DEFAULT 'Y'::bpchar,
  used_in_action_page character(1) NOT NULL DEFAULT 'Y'::bpchar,
  default_action_list character(1) NOT NULL DEFAULT 'N'::bpchar,
  CONSTRAINT actions_groupbaskets_pkey PRIMARY KEY (id_action, group_id, basket_id)
)
WITH (OIDS=FALSE);
ALTER TABLE actions_groupbaskets OWNER TO postgres;

CREATE TABLE baskets
(
  coll_id character varying(32) NOT NULL,
  basket_id character varying(32) NOT NULL,
  basket_name character varying(255) NOT NULL,
  basket_desc character varying(255) NOT NULL,
  basket_clause text NOT NULL,
  is_generic character varying(6) NOT NULL DEFAULT 'N'::character varying,
  enabled character(1) NOT NULL DEFAULT 'Y'::bpchar,
  CONSTRAINT baskets_pkey PRIMARY KEY (coll_id, basket_id)
)
WITH (OIDS=FALSE);
ALTER TABLE baskets OWNER TO postgres;

CREATE TABLE groupbasket
(
  group_id character varying(32) NOT NULL,
  basket_id character varying(32) NOT NULL,
  "sequence" integer NOT NULL DEFAULT 0,
  redirect_basketlist character varying(2048) DEFAULT NULL::character varying,
  redirect_grouplist character varying(2048) DEFAULT NULL::character varying,
  result_page character varying(255) DEFAULT 'show_list1.php'::character varying,
  can_redirect character(1) NOT NULL DEFAULT 'N'::bpchar,
  can_delete character(1) NOT NULL DEFAULT 'N'::bpchar,
  can_insert character(1) NOT NULL DEFAULT 'N'::bpchar,
  CONSTRAINT groupbasket_pkey PRIMARY KEY (group_id, basket_id)
)
WITH (OIDS=FALSE);
ALTER TABLE groupbasket OWNER TO postgres;

CREATE TABLE user_abs
(
  user_abs character varying(32) NOT NULL,
  system_id bigserial NOT NULL,
  new_user character varying(32) NOT NULL,
  basket_id character varying(255) NOT NULL,
  basket_owner character varying(255),
  is_virtual character(1) NOT NULL DEFAULT 'N'::bpchar,
  CONSTRAINT user_abs_pkey PRIMARY KEY (system_id)
)
WITH (OIDS=FALSE);
ALTER TABLE user_abs OWNER TO postgres;