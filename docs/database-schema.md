# Guidelines for the database schema to use

The DB is assumed to be MySQL 8.0+, with InnoDB tables.

For all the rules mentioned below, there can always be exceptions if justified.

## Names

Table, field, index, and key names must be lower camel case.

Table name should be plural where pertinent.

Index and key names must have this structure : `{table name}_{field(s)}_({u}_)?{key type}`.
Ie:
- `posts_slug_u_index` for the UNIQUE index on the `posts.slug` field
- `posts_user_id_fk` for the Foreign key constraints on the `posts.user_id` field

Since we are using MySQL, fields that have a foreign key constraint must not have a separate definition for an index (since MySQL add it automatically when it doesn't already exist).

## Primary keys

All table must have a PK.
PK must be incrementing unsigned big int unless a more natural PK exists and the table has not foreign key.

## Foreign keys

All pertinent tables must have foreign keys constraints with the pertinent on update and delete clauses.

FK must target the foreign's PK, which must be of type int (so no FK on a string column).

Naturally all foreign keys fields must be of the same type as the foreign primary key (which will mostly be unsigned big ints).

## Datetime

Field for datetime should be of type `timestamp` and not `datetime`.

Each table must have a `created_at` and `updated_at` columns that updated automatically, even if their value isn't accessible via their entities.

## Enum

The `enum` type must be used instead of lookup tables.

Enum values must be hardcoded in the migration, must never change order, and be in upper camel case (ie: `ENUM_VALUE`).

Typically, each enum field in the DB must have a matching baked string enum in the code.

## JSON

Fields that contain JSON date must be of the `json` type.

## Boolean

Boolean field must be of type `bool` and must not have index.

## Null and default values

Fields must be non nullable unless necessary.

Fields must have a pertinent non nullable default value.
All fields with a default value must be non nullable.

## Uniqueness

Fields (other than the PK) that holds a value that should be unique in the table must have a UNIQUE index.
