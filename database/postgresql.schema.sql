DROP TABLE IF EXISTS "sample";
 
CREATE TABLE "sample" (
    "id" serial NOT NULL,
    "value_string" character varying(255) NULL,
    "value_integer" int NULL,
    "value_float" float NULL,
    "value_boolean" boolean NULL,
    "value_datetime" timestamp NULL,
    PRIMARY KEY ("id")
);
