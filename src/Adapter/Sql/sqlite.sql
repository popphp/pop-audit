CREATE TABLE "audit_log" (
  "id" integer NOT NULL PRIMARY KEY AUTOINCREMENT,
  "user_id" integer,
  "username" varchar,
  "domain" varchar,
  "route" varchar,
  "method" varchar,
  "model" varchar NOT NULL,
  "model_id" integer NOT NULL,
  "action" varchar NOT NULL,
  "old" text,
  "new" text,
  "metadata" text,
  "timestamp" datetime NOT NULL,
  UNIQUE ("id")
) ;

INSERT INTO "sqlite_sequence" ("name", "seq") VALUES ('audit_log', 0);
CREATE INDEX "user_id" ON "audit_log" ("user_id");
CREATE INDEX "username" ON "audit_log" ("username");
CREATE INDEX "model" ON "audit_log" ("model");
CREATE INDEX "model_id" ON "audit_log" ("model_id");
CREATE INDEX "action" ON "audit_log" ("action");
CREATE INDEX "timestamp" ON "audit_log" ("timestamp");