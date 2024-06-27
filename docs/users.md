# Users

Users represent humans browsing and interacting with the site.

New users can register themselves or be added by admins users.    
Registering require and email and a password.  
After registration (even in the admin panel), the email must be validated to be able to login to the site.

A user can log in, and log out.
Authentication is done by email+password with a session stored in the backend with the session id stored in a cookie.

Users are of 3 types : 
- admin: they have access to all parts of the admin panel and are allowed to view and do every thing that is possible
- writer: they have access to the admin panel but only see the UI that allows them to interacts with theirs own content
- regular: visitors of the website that can only edit their profile and post comments on existing posts and pages

Writers and admins that write posts and page owns this content, which can not be edited by other writers (but can be edited/deleted by other admins).

Password can be changed when logged in, from the profile page, or when not logged in.  
Password are stored as Bcrypt hash.

The authentication system and everything around it, including the session must use as much thing from Symfony as possible.

Table `users`:
- `id PK`
- `email varchar(255) not null` with UNIQUE index
- `password varchar(255) not null`
- `name varchar(255) nullable`
- `email_validated_at timestamp nullable`
- `role enum(REGULAR, WRITER, ADMIN) default REGULAR`
- timestamp fields