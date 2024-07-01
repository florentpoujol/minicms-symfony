# Entities

This document describes the entities involved in the applications, how they relate to each other and how they can be manipulated.
- users
- posts
- comments
- pages
- media
- audit logs


## Users

Users represent registered humans browsing and interacting with the site.

Users are of 3 types:
- admin: they have access to all parts of the admin panel and are allowed to view and do every thing that is possible
- writer: they have access to the admin panel but only see the parts that allows them to interact with theirs own content
- regular: visitors of the website that can only edit their profile and post comments on existing posts and pages

Writers and admins that write posts and page owns this content, which can not be edited by other writers (but can be edited/deleted by other admins).

**CRUD**
New users can be created during a registration process, or be added by admins users in the backend UI.    

Users, once logged-in can edit their email address and their password.
Admin users can also do that in the backend UI.

Only admin can delete users.
Their comments are then deleted. 
Other resources they may own like media, posts and pages are not deleted but re-attributed to the admin doing the deletion.

**Registration and logins**

Registering require and email and a password.  
After registration (even in the admin panel), the email must be validated to be able to login to the site.

A user can log in, and log out.
Authentication is done by email+password with a standard cookie-based session stored in the backend.

**Passwords**
Password can be changed when logged-in from the profile page, or when not logged in.  
Password are stored as Bcrypt hash.

The authentication system and everything around it, including the session must use as much thing from Symfony as possible.

**Database**

Table `users`:
- `id PK`
- `email varchar(255) not null` with UNIQUE index
- `password varchar(100) not null`
- `name varchar(255) nullable`
- `email_validated_at timestamp nullable`
- `role enum(REGULAR, WRITER, ADMIN) default REGULAR`
- timestamp fields


## Posts

Posts are "articles", displayed in a timeline on the home page.

A post has textual content, which may include medias like embedded images, as well as links to other media or websites.

The text content is in markdown (maybe later via a WYSIWYG editor) but link to medias can be easily inserted via **shortcodes** (see the media paragraph below).

Posts have a title, from which is derived a unique and permanent slug, a text content, a creation date.    
The slug is found in the posts URL, instead of the post's numerical id.  
Ie: https://the.blog/posts/the-post-slug would show the post that at least originally had the title "The Post Slug".

Posts can have comments, but it can be turned off in the post's config.

Posts have a draft/published status that indicate if they are published, if they should be viewable publicly.
When not viewable publicly they can still be viewed by their author and admins.

**CRUD**

Posts can be created, edited and deleted by writer and admin in the admin UI.

Writers can only interact with the posts they have created, and can not delete they own posts, while admins can do everything with any posts.

Deleting a posts delete all its comments.

**Database**

`posts` table:
- `id PK`
- `user_id FK users.id`
- `title varchar(255) not null`
- `slug varchar(255) not null` with unique index on the first 50 characters
- `content longtext not null`
- `allow_comments boolean not null default true`
- `published_at timestamp nullable`
- timestamp fields


## Comments

Comments can be added by any registered users on posts that allow them.

They can be created by any registered users, not anonymous ones, and can be edited up to 10 minutes after their creation.

Admin users can edit and delete comments whenever.

Comments must be enabled on a per-post basis.  
If comments are disabled after a post had some, they are not deleted but just not displayed anymore.

Comments can be written and edited directly on the post page, not in the admin UI.

Comments ar deleted when a user or a post are deleted.

Unlike posts and pages, comment content is plain text, and do not support markdown or media shortcodes.

**Database** 

`comments` table:
- `id PK`
- `user_id FK users.id`
- `post_id FM posts.id`
- `content text not null`
- timestamp fields


## Pages

Pages are almost like posts except they are not meant to be displayed in a timeline, but only be reached via links in other post/page or the menu.

A page has textual content, which may include medias like embedded images, as well as links to other media or websites.

The text content of a page is the same as of a post.

Pages have a title, from which is derived a unique and permanent slug, a  text content.  
The slug is found in the pages URL, instead of the page's numerical id.
Ie: https://the.blog/the-page-slug would show the page that at least originally had the title "The Page Slug".

Pages can have comments, but it can be turned off in the page's config.
Deleting a pages delete all its comments.

Pages have a draft/published status that indicate if they are published, if they should be viewable publicly.
When not viewable publicly they can still be viewed by admins.

**CRUD**

A page is created/edited/deleted only by an admin user in the admin UI.

Since they are only written by admins and all admins can edit everything, page are not really "owned" by their original creator, but the relationship still exist to see who wrote it originally.

**Database**

`pages` table:
- `id PK`
- `user_id FK users.id`
- `title varchar(255) not null`
- `slug varchar(255) not null` with unique index on the first 50 characters
- `content longtext not null`
- `published_at timestamp nullable`
- timestamp fields

This is the exact same structure as the posts table, but since their usage is different, we keep them in separate tables. 


## Medias

Medias are files uploaded to site in order to be shown in the posts or pages (if they are images) or be directly downloaded via a link.

Medias can be embedded in content via **shortcodes** so that they work whatever is main URL of the site is.

Shortcodes are custom markup that will resolve to a media properly embedded, with the correct link.
Ie: `[media:{media slug}]`  could be replaced with `![{media alt text}]({media link})` for an image (or just a regular link if not an image).

The upload is limited to 2Mb, and limited to theses files types: `.zip`, `.pdf`, `.jp(e)g`, `.png`.  
While each media has an entry in the DB, the actual file is stored on disk

**CRUD**

Media can be created/deleted by writer or admins, which owns them.
Media can not be edited.


**Database**

Table `medias`:
- `id PK`
- `user_id FK users.id`
- `slug varchar(255) not null`  with unique index on the first 50 characters
- timestamp fields


## Audit logs

Audit logs are a records of the modification of every other entities.

There is one audit log per creation, edition and deletion of users, posts, comments, pages and medias (but not audit logs).

Audit logs are stored in the database, and must be created automatically based on Doctrine events (or whatever Doctrine has that allows to do that).

An audit log stores 
- who did the modification (which user was currently logged-in, if any)
- by what interface (the admin UI, or an async job for instance)
- what properties where modified and what was their value before and after

They are immutable, read-only in the admin UI (even by admins), and can be seen by admin in the admin UI when on the detail of the corresponding entity. 

While they have a nullable one-to-many relationship with the user that did the modification, they have a polymorphic many-to-many relationship to the entity that was modified.

Care should be applied so that not all information are logged, like passwords or content that can be too long like media or even post/page content.

**Database**

`audit_logs` table:
- `id PK`
- `user_id nullable FK users.id` when the user is deleted nothing happens here, the field is left as-is, so the application needs to take that into account
- `interface`
- `before JSON nullable`
- `after JSON nullable`
- `created_at timestamp(3)` here we want a precision 
- whatever fields are needed for the polymorphic relationship (in Laravel, this is two fields, one for the entity id and one for the entity type)

The typical query pattern is (one object + within a date range + ordered by most recent date first).  
So a compound index must be created to allow that.


**Polymorphic relation**
https://www.youtube.com/watch?v=hNAMEWYCkAM
https://www.doctrine-project.org/projects/doctrine-orm/en/3.2/reference/inheritance-mapping.html
