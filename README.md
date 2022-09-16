# WP Vanity Links
Create short/vanity marketing friendly urls that redirect to desired target destination.

## HOW IT WORKS
This plugin is very simple in nature and does not modify the default Wordpress database structure, or the core Wordpress functionality. It uses existing hooks/filters and features of the Wordpress platform like post metadata, custom post types, and permalink rewrite rules.
- Install and activate the plugin.
- The plugin creates a new post type for `wpvu_redirects`.
- Vanity, target, and count parameters are help in the post metadata table.
- You will see a new navigation item labeled `Vanity Links`.
- Navigate to the `Vanity Links` screen to add/update/manage your links.

## Activation 
- Simply download the `.zip` archive, and extract it to your `wp-content > plugins` directory.
- Navigate to your plugins dashboard in Wordpress, activate the new plugin listed.
- Refresh your permalink settings under `Settings > Permalinks` to enable the new url rewrite rules.

## Deactivation
If you do not wish to use this plugin any longer, it is best practice to manually remove/delete any Vanity Links you have created before deactivating the plugin. **DO NOT FORGET** to refresh your permalink settings under `Settings > Permalinks`, this will clear out the custom url rewrite rules the plugin created.