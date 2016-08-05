## Purpose of Plugin

This plugin provides the ability for users to submit website URLs, which are then stored as a Custom Post Type of "Websites".

## Website Submission

The submission form is located at /your-website/. On the page is a single form with fields for the Website Name and the Website URL. 
Both fields are required; if either field is left blank, the form submission will fail and the user will be notified of the error. (While the form uses the HTML5 `required` attribute, safeguards exist server-side to ensure that both fields are submitted.)

### The Form Template

By default, the plugin loads the `templates/website-form.php` template for the frontend form. However, the plugin employs
a template loading pattern which allows the user to optionally override that template in their own parent or child theme. 

In order to override the template, the user can simply add a file named `website-form.php` in the `mt-trial` directory of their parent or child theme, 
and the plugin will load that instead.

## Submission Processing

Upon submission, the form data is sanitized and processed via the admin_post hook. The server implements a nonce field to
ensure that the request came from the website on which the plugin is installed. 

Once the fields have been validated and sanitized, the source code of the given URL is fetched via a `wp_remote_get` call.
If the `wp_remote_get` call fails, an error is returned to the user so they can try again.

Once the body has been retrieved, it is stored as a transient for caching purposes, and the new Website post object is created.
If for any reason the `wp_insert_post` call fails, an error is returned to the user.

## Administering Websites

Adding or editing Website posts in any way is restricted from the Dashboard. Additionally, only Administrators and Editors
have access to the Website posts in the dashboard at all. When viewing a Website post in the Admin, Administrators will see
the name, URL, and source code of the website. Editors simply see the name of the website. 

## Caching

In order to aid in site performance, the source code for the websites is stored as Transient data. When the source code is 
retrieved, the response headers are parsed for the existence of a `cache-control` header. If it exists and is not `0`, the 
Transient expiration is set to respect the provided `max-age` setting of the header. Otherwise, the Transient receives an 
maximum life of 1 hour. This default value can be filtered by employing the `as_mt_cache_length` filter in a theme or plugin.

### Why Transients

While there are many different ways to handle caching, in the end Transients were chosen for a few reasons. First and foremost, Transients were
implemented because they're available out-of-the-box. Without any sort of server configuration, any user can emply Transients
as an effective form of caching. Additionally, if the user does activate a form of Object Cache on their server, Transients
will automatically take advantage of that Object Cache in order to provide even better performance. 

There are drawbacks of Transients, to be sure. For instance, while they have an expiration value, they can't reliably be counted on to exist up until
that expiration. In some situations this could be an issue, but in this case there's no harm in having the Transient expire
before the indicated expiration date. In that case, the `wp_remote_get` call is simply made again and the source code is
stored as a transient again. 

Perhaps one of the biggest downsides to Transients is that they muddy up the `wp_options` table. This can result in poor 
performance, as that table isn't well-indexed. While this is definitely a concern in high volumes, the ease of use of Transients
along with their immediate availability outweighs that risk. Additionally, whenever a Website post is deleted, the transient
for that URL is deleted as well, ensuring that orphan Transients won't linger in the database. This should help avoid an
over-abundance of Transient entries in the `options` table. 

## JSON Endpoint

The plugin also activates a JSON endpoint for querying Websites. The endpoint is located at `/wp-json/wp/v2/websites/`. 
The endpoint is compatible with the `filter` parameter, meaning any `WP_Query` paramater can be passed to the end point for
advanced querying and filtering. The endpoint returns an array of the Website posts including the `ID`, `title`, and `source`
fields.

### JSON Performance

Since the JSON endpoint returns potentially all of the Website posts, by default it pulls the Website source code that is 
stored in the `post_meta` table for that post. This avoids having to query the URL to get updated source code for any sites 
with expired Transient Data. The source code stored in the `post_meta` table is updated every time the Transient is refreshed,
so it can be reasonably assumed to be acceptably up-to-date for most applications.

However, if the most up-to-date source code is required by the user, they may set the `refresh` paramter to `true`. This 
will cause the server to query any expired source code for updated information. While a potential performance hit, this 
gives the user flexibility to decide what level of fidelity they need from the source code returned without compromising
the default caching.