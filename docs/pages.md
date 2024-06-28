# Pages

Pages are almost like posts except they are not meant to be displayed in a timeline, but only be reached via links in other post/page or the menu. 

A page has textual content, which may include medias like embedded images, as well as links to other media or websites.

A page is created/edited/deleted only by an admin user.

The text content of a page is the same as of a post. 

Pages, since they are only written by admins and all admins can edit everything are not really "owned" by their writer, but the relationship still exist to see who wrote it originally. 

Pages have a title, from which is derived a unique and permanent slug, a  text content.  
The slug is found in the pages URL, instead of the page's numerical id.
Ie: https://the.blog/the-page-slug would show the page that at least originally had the title "The Page Slug".

Pages can have comments, but it can be turned off in the page's config.
Deleting a pages delete all its comments.

Pages have a draft/published status that indicate if they are published, if they should be viewable publicly.
When not viewable publicly they can still be viewed by admins.

`pages` table:
- `id PK`
- `user_id FK users.id`
- `title varchar(255) not null`
- `slug varchar(255) not null` with unique index on the first 50 characters
- `content longtext not null`
- `allow_comments boolean not null default true`
- `published_at timestamp nullable`
- timestamp fields

This is the exact same structure as the posts table, but since their usage is different, we keep them in separate tables. 
