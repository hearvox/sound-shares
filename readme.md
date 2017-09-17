# Sound Shares #
**Contributors:** [hearvox](https://profiles.wordpress.org/hearvox)  
**Donate link:** https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=3546QM2HAEKXW  
**Tags:** social, facebook, twitter, embed, player, audio, video  
**Author URI:** http://hearingvoices.com/tools/sound-shares/  
**Plugin URI:** http://hearingvoices.com/  
**Requires at least:** 4.5  
**Tested up to:** 4.8  
**Stable tag:** 0.1.0  
**License:** GPLv2 or later  
**License URI:** http://www.gnu.org/licenses/gpl-2.0.html  

**NOT READY YET:** SOON. Embed media player in social sites when users share your link in a social sites.  

## Description ##

Add an audio (or video) player into Facebook posts and Twitter tweets of your posts.

### Settings and Security ###

The Settings screen lets you control which user-roles, post-types, and categories display the Sound Shares meta box.

## Installation ##

To install the use the Postscript plugin:

1. Upload the `sound-shares` directory and content to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to the Setting: Postscript options screen.

## Frequently Asked Questions ##

### How do add registered script/style handles to the Postscript meta box? ###
The Settings &gt; Postscript screen lists all available handles, those registered via the [`wp_enqueue_scripts` hook])https://developer.wordpress.org/reference/hooks/wp_enqueue_scripts/} in your active theme and plugins and the defaults registered by WordPress itself.

You can add any registered script or stylesheet handle to the checkboxes in the Postscript meta box. The [GitHub Dev Notes](https://github.com/hearvox/postscript#dev-notes) details on the inner workings of this plugin, including custom fields and taxonomies, transients, options, and filters.

### How do I register scripts? ###
**Your Scripts and Styles:** You can register your own CSS/JS file *handles* with the [wp_register_script()](https://developer.wordpress.org/reference/functions/wp_register_script/) and [wp_register_style()](https://developer.wordpress.org/reference/functions/wp_register_style/) functions.

**Default Scripts and Styles:** WordPress auto-registers numerous styles and scripts via its core functions: [wp_default_scripts()](https://developer.wordpress.org/reference/functions/wp_default_scripts/) and [wp_default_styles()](https://developer.wordpress.org/reference/functions/wp_default_styles/). Each file gets its own unique handle: see the [list of defaults](https://developer.wordpress.org/reference/functions/wp_enqueue_script/#defaults).

### What is a use case for this plugin? ###
Adding Thickbox to a post is an example of what this plugin does. WordPress ships with a modified [ThickBox jQuery library](https://codex.wordpress.org/Javascript_Reference/ThickBox), used to make modal lightbox windows. The [add_thickbox()](https://developer.wordpress.org/reference/functions/add_thickbox/) function enables this feature. When enabled, though, Thickbox's CSS and JS files load on every Post, whether the post needs it or not.

This plugin improves site performance by enqueuing scripts only when specifically requested for an individual post, via the **Postscript** meta box. See [the screenshots](https://wordpress.org/plugins/postscript/screenshots/).

### What might be some future features? ###

Tell us in the [support fourm](https://wordpress.org/support/plugin/sound-shares) about new features you'd like in future releases. For instance:

* Custom descriptions for embedded link preview (rather than default post excerpt).
* Custom size for embedded player.
* Different images for Facebook and Twitter.
* Add filter for...?

### How can I contribute to Postscript? ###

Sound Shares is now on [GitHub](https://github.com/hearvox/sound-shares). Pull Requests welcome.

### How can I translate Postscript? ###
This plugin is internationalized (default: English). Please contribute a translation in your language.

The WordPress.org Polyglots Team maintains a comprehensive [Translator’s Handbook](https://make.wordpress.org/polyglots/handbook/). All text strings in this plugin are localized, following the guidelines of the Wordpress.org Plugin Handbook's [Internationalization section](https://developer.wordpress.org/plugins/internationalization/).

### Credits ###
This plugin was developed as part of a [Reynolds Journalism Institute](https://www.rjionline.org) fellowship and an article for [Current](https://current.org) public media news.

## Screenshots ##

1. Edit Post screen *Sound Shares** meta box
2. Settings Page: User Roles, Post Types, URls, and Classes
3. Embedded Player at Facebook
4. Embedded Player at Twitter

## Changelog ##

### 0.4.5 ###
### 0.2.0
* Beta version.
* Test upgrade option function based on version number.

### 0.1.0
* Initial test version.

## Upgrade Notice ##

### 0.1.0 ###
Secure public release version is 0.2.0.


