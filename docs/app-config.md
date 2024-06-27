# App config

Application global configuration options can be edited by admin users via a form.

They are essentially a list of key/value pairs, but users can not choose the keys, and values may be restricted by the UI depending on the key.  
Key value pairs can be viewed, added, edited (only values can be edited, not the key) and deleted.  
Keys are unique, we can't add a key that is already present.

List of configs:
- name of the site (arbitrary value)
- enable registration of new users (boolean)
- mail from email and name (for use when sending email to users) (one email and one arbitrary value)
- SMTP or other mailer info ?

These configuration are kept in the database, and should be cached, with the cache being updated when a value is updated in the DB.

Table: `app_config`:
- `key varchar(255) not null` Since this field is unique and the table This is the Primary key
- `value varchar(2000) not null`
- timestamp fields
