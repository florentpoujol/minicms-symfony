# UIs

Visitors and logged-in users can interact with the site via 3 distinct kind of UI.

## Public front-end

This is the one that non-logged-in users have access to.

Published pages, posts and their comments can be viewed there.

Pages are accessible directly via their slug at the root of the site, ie `https://the.site/the-page-slug`.

Full posts are accessible directly via their slug, nested in the "posts" URL segment, ie `https://the.site/posts/the-post-slug`
An excerpt of posts can be displayed in a timeline on the `/posts` page (without another segment in the URL).

The login and register forms are also accessible publicly at the `/login` and `/register` URLs.

Comments appear below the posts that enables them.

## User Profile

Once a user is logged-in it has access to its profile at the `/profile` URL.

The profile allows it to modify so parts of its user info, like its email, or password.

## Admin backend

Writers and admins have in addition access to a whole "admin" area where they can manipulate posts, medias, pages, users, logs, depending on their rights.

### Accessible to writers

- List of their posts
- Edition of their posts
- Creation of new posts
- List of their media
- Creation of new media

### Accessible to admin

For every resource, they can see the full list, create a new one, and edit or delete the existing ones.
Resources are : users, posts, comments, pages, medias.

They also have access to the application configurations page

## Recap or URLs with content or forms

- `/login`
- `/register`
- `/profile`
- `/posts` timeline of posts
- `/posts/{post slug}` a single posts
- `/{page slug}` a single page
- `/admin/configuration` show the application configuration page
- `/admin/{resource}` show a list of that resource
- `/admin/{resource}/create` show the creation form for that resource
- `/admin/{resource}/{resource id}` show the detail of that particular resource
- `/admin/{resource}/{resource id}/edit` show the edit form for that particular resource
