# Comments

Comments are, well, comments related to posts and pages.

They can be crated by any registered users, not anonymous ones, and can be edited up to 10 minutes after their creation.

Admin users can edit and delete comments whenever.

Comments must be enabled on a per-post and per-page basis.  
If comments are disabled after a post had some, they are not deleted but just not displayed anymore.

Comments can be written and edited directly on the post/page, not in the admin UI.

Comments of a user is deleted when the user is deleted.

`comments` table:
- `id PK`
- `user_id FK users.id`
- {fields for Doctrine to do polymorphic relationship}
- `content text not null`
- timestamp fields

Note: if comments could only be done on posts or pages, it would be ok to have two nullable FK, one for the posts, one for the pages.
But for the exercise, I want to handle this as a polymorphic relationship.