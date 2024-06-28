# Posts

Posts are "articles", displayed in a timeline on the home page.

A post has textual content, which may include medias like embedded images, as well as links to other media or websites.

A post is written by a user of type "writer" or "admin", that can create, edit and delete posts.

The text content is in markdown (maybe later via a WYSIWYG editor) but link to medias can be easily done via shortcodes.

Shortcodes are custom markup that will resolve to a media properly embedded, with the correct link.
Ie: `[media:{media slug}]`  could be replaced with `![{media alt text}]({media link})` for an image.

Posts are owned by their author, can only be edited by them or other admins users.

Posts have a title, from which is derived a unique and permanent slug, a  text content, a creation date.  
The slug is found in the posts URL, instead of the post's numerical id.
Ie: https://the.blog/posts/the-post-slug would show the post that at least originally had the title "The Post Slug".

Posts can have comments, but it can be turned off in the post's config.
Deleting a posts delete all its comments.

Posts have a draft/published status that indicate if they are published, if they should be viewable publicly.
When not viewable publicly they can still be viewed by their author and admins.

`posts` table:
- `id PK`
- `user_id FK users.id`
- `title varchar(255) not null`
- `slug varchar(255) not null` with unique index on the first 50 characters
- `content longtext not null`
- `allow_comments boolean not null default true`
- `published_at timestamp nullable`
- timestamp fields