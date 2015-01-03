DROP TABLE IF EXISTS "sample";
 
CREATE TABLE "sample" (
    "id" serial NOT NULL,
    "value_string" character varying(255) NOT NULL,
    "value_integer" int NULL,
    "value_float" float NULL,
    "value_boolean" boolean NULL,
    "value_datetime" timestamp NULL,
    CONSTRAINT "sample_pk" PRIMARY KEY ("id"),
    CONSTRAINT "sample_unique_string" UNIQUE ("value_string")
);
