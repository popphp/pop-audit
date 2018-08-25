CREATE SEQUENCE audit_id_seq START 1;

CREATE TABLE "audit_log" (
  "id" integer NOT NULL DEFAULT nextval('audit_id_seq'),
  "user_id" integer,
  "username" varchar(255),
  "domain" varchar(255),
  "route" varchar(255),
  "method" varchar(255),
  "model" varchar(255) NOT NULL,
  "model_id" integer NOT NULL,
  "action" varchar(255) NOT NULL,
  "old" text,
  "new" text,
  "metadata" text,
  "timestamp" timestamp NOT NULL,
  UNIQUE ("id")
) ;

ALTER SEQUENCE audit_id_seq OWNED BY "audit_log"."id";
CREATE INDEX "user_id" ON "audit_log" ("user_id");
CREATE INDEX "username" ON "audit_log" ("username");
CREATE INDEX "model" ON "audit_log" ("model");
CREATE INDEX "model_id" ON "audit_log" ("model_id");
CREATE INDEX "action" ON "audit_log" ("action");
CREATE INDEX "timestamp" ON "audit_log" ("timestamp");
