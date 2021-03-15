=== Online Learning Courses ===
Contributors: flowdee, kryptonitewp
Donate link: https://donate.flowdee.de
Tags: udemy, udemy api, udemy course, udemy courses, course, courses, boxes, api, video course, video courses, online learning, learning, online learning courses, flowdee, kryptonitewp
Requires at least: 3.5.1
Requires PHP: 5.6.0
Tested up to: 5.7.0
Stable tag: 1.3.0
License: GPLv3 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Display Online Learning Courses from the best platform inside your WordPress posts and pages.

== Description ==
With Online Learning Courses you can display single courses by their ID or even search for courses by keywords and language.

= Features =

*   This plugin allows you to display Udemy™ courses and lead your visitors directly to the course pages
*   Display single courses by ID
*   Search for courses by keyword (API keys required)
*   Select between the following layouts: Standard Boxes, Grids & Lists
*   Select between the following styles: Standard, Clean, Light & Dark
*   Two separate widgets for single courses and searches
*   Configuration page for more options
*   Try out the **[online demo](https://kryptonitewp.com/demo/wp-udemy/)**
*   Regular updates and improvements: Go though the [changelog](https://wordpress.org/plugins/wp-udemy/changelog/)

= Quickstart Examples =

* Single courses: [ufwp id="ID"]
* Keyword search: [ufwp search="css" items="3" lang="de"]

= More features with the PRO version =

The PRO version extends this plugins exclusively with our affiliate link feature and many more:

*   Affiliate Links
*   Masked Links
*   Click Tracking
*   Highlight Bestselling Courses
*   Highlight New Courses
*   Custom Templates

Details and upgrade can be found **[here](https://kryptonitewp.com/downloads/wp-udemy-pro/?utm_source=wordpress.org&utm_medium=textlink&utm_campaign=Online%20Learning%20Courses&utm_content=plugin-page)**.

= Support =

* Detailed online [documentation](https://kryptonitewp.com/support/knb/online-learning-courses-documentation/)
* Browse [issue tracker](https://github.com/flowdee/wp-udemy/issues) on GitHub
* [Follow me on Twitter](https://twitter.com/flowdee) to stay in contact and informed about updates

= Credits =

* This plugin is not official made or maintained by Udemy™. All data provided through the official Udemy™ API.

== Installation ==

The installation and configuration of the plugin is as simple as it can be.

= Using The WordPress Dashboard =

1. Navigate to the 'Add New' in the plugins dashboard
2. Search for 'online learning courses'
3. Click 'Install Now'
4. Activate the plugin on the Plugin dashboard

= Uploading in WordPress Dashboard =

1. Navigate to the 'Add New' in the plugins dashboard
2. Navigate to the 'Upload' area
3. Select zip file from your computer
4. Click 'Install Now'
5. Activate the plugin in the Plugin dashboard

= Using FTP =

1. Download the plugin
2. Extract the directory to your computer
3. Upload the directory to the `/wp-content/plugins/` directory
4. Activate the plugin in the Plugin dashboard

== Frequently Asked Questions ==

= How do I display a course? =

The default shortcode expects a course ID which can be found after adding the course to the cart and taking the ID out of the url of your browser.

[ufwp id="518498"]

Additionally you can string together multiple ids and display multiple courses at once:

[ufwp id="41305,597898"]

More information about the shortcodes can be found in our online [documentation](https://kryptonitewp.com/support/knb/online-learning-courses-documentation/).

= How do search for courses? =

Instead of selecting specific ids you can search for courses by keywords.

[ufwp search="css" items="3" lang="de"]

More information about the shortcodes can be found in our online [documentation](https://kryptonitewp.com/support/knb/online-learning-courses-documentation/).

= How do I change the template or style? =

All available templates and styles, as well as further instructions, can be found in our online [documentation](https://kryptonitewp.com/support/knb/online-learning-courses-documentation/).

= Multisite supported? =

Yes of course.

== Screenshots ==

1. Box Styles
2. List layout
3. Grid layout
4. Course Widgets
5. Admin: Settings page
6. Admin: Widgets configuration

== Changelog ==

= Version 1.3.0 (15th March 2021) =
* New: Added settings widget to easily submit a plugin review
* Tweak: Optimized images settings
* Tweak: Optimized assets building
* WordPress v5.7 compatibility

= Version 1.2.1 (8th March 2021) =
* Fix: Displaying downloaded course images didn't work
* WordPress v5.6.2 compatibility

= Version 1.2.0 (3rd January 2021) =
* Tweak: API credentials validation now shows an error in case the site/domain is being blocked by the Udemy firewall
* Tweak: Optimized API call headers
* Fix: When using the course search, the API returned more results than requested
* Fix: Several HTML examples were broken on the settings and edit widget screen
* Updated translations .pot file
* PHP v7.4.1 compatibility
* WordPress v5.6.0 compatibility

= Version 1.1.5 (15th May 2020) =
* Tweak: Optimisation for AMP mode
* Fix: Grid template wasn't displayed properly
* WordPress v5.4.1 compatibility

= Version 1.1.4 (21th December 2019) =
* New: Added "review our plugin" note to the plugin's admin page footer
* Tweak: Code style optimizations
* Updated translations
* WordPress v5.3.2 compatibility

= Version 1.1.3 (20th June 2019) =
* New: Prices can now be shown/hidden via settings/shortcode
* Updated translations
* WordPress v5.2.2 compatibility
* Templates updated: amp, grid, list, standard, widget, widget_small

= Version 1.1.2 (4th June 2018) =
* Tweak: API calls now properly verify SSL certificates
* WordPress v4.9.6 compatibility

= Version 1.1.1 (13th April 2018) =
* Tweak: Downloaded course images will be automatically deleted after 24 hours
* WordPress v4.9.5 compatibility

= Version 1.1.0 (2nd April 2018) =
* New: Course images can now be downloaded and served locally instead of using Udemy's server (new setting added)
* Minor CSS improvements for grid template

= Version 1.0.9 (10th March 2018) =
* Fix: "Fatal Error: Cannot use object of type WP_Error as array ... in api-functions.php"
* WordPress v4.9.4 compatibility

= Version 1.0.8 (1st December 2017) =
* New: Price reductions now show up inside course boxes
* New: Added support for Google Accelerated Mobile Pages (AMP)
* Tweak: Cleanup shortcode output in order to prevent unwanted breaks and empty paragraphs
* Tweak: Optimized course data handling in order to reduce the amount of data to be stored in database
* Tweak: Optimized styles handling
* Fix: Placing shortcodes in page builders might lead into into PHP warnings
* Fix: In some cases the plugin styles were not loaded
* WordPress v4.9.1 compatibility

= Version 1.0.7 (22th March 2017) =
* Fix: Date/time formatting on plugin settings (debug mode)

= Version 1.0.6 (30th August 2016) =
* Fix: Star Rating wasn't displayed correctly on Retina displays
* Updated translations

= Version 1.0.5 (19th August 2016) =
* Fix: Styles weren't loaded correctly when using older themes without body_class support
* Updated translations

= Version 1.0.4 (18th August 2016) =
* Tweak: Optimized styles in order to prevent theme issues
* Fix: Styles weren't loaded correctly when using the new ufwp shortcode

= Version 1.0.3 (17th August 2016) =
* Plugin rebranding

= Version 1.0.2 (13th August 2016) =
* Tweak: Optimized course img styles in order to prevent theme issues
* Added settings quickstart grid col example
* Templates updated: grid, list, standard, widget, widget_small

= Version 1.0.1 (8th August 2016) =
* New: Enable/disable meta via settings
* Tweak: Optimized course image responsive styles and removed important rules

= Version 1.0.0 (23th July 2016) =
* Initial release

== Upgrade Notice ==

= Version 1.3.0 (15th March 2021) =
* If you used the setting "download images", you need to visit the settings page and choose this option again

= Version 1.1.5 (15th May 2020) =
* Tweak: Optimisation for AMP mode
* Fix: Grid template wasn't displayed properly
* WordPress v5.4.1 compatibility

= Version 1.1.4 (21th December 2019) =
* New: Added "review our plugin" note to the plugin's admin page footer
* Tweak: Code style optimizations
* Updated translations
* WordPress v5.3.2 compatibility

= Version 1.1.3 (20th June 2019) =
* New: Prices can now be shown/hidden via settings/shortcode
* Updated translations
* WordPress v5.2.2 compatibility
* Templates updated: amp, grid, list, standard, widget, widget_small

= Version 1.1.2 (4th June 2018) =
* Tweak: API calls now properly verify SSL certificates
* WordPress v4.9.6 compatibility

= Version 1.1.1 (13th April 2018) =
* Tweak: Downloaded course images will be automatically deleted after 24 hours
* WordPress v4.9.5 compatibility

= Version 1.1.0 (2nd April 2018) =
* New: Course images can now be downloaded and served locally instead of using Udemy's server (new setting added)
* Minor CSS improvements for grid template

= Version 1.0.9 (10th March 2018) =
* Fix: "Fatal Error: Cannot use object of type WP_Error as array ... in api-functions.php"
* WordPress v4.9.4 compatibility

= Version 1.0.8 (1st December 2017) =
* New: Price reductions now show up inside course boxes
* New: Added support for Google Accelerated Mobile Pages (AMP)
* Tweak: Cleanup shortcode output in order to prevent unwanted breaks and empty paragraphs
* Tweak: Optimized course data handling in order to reduce the amount of data to be stored in database
* Tweak: Optimized styles handling
* Fix: Placing shortcodes in page builders might lead into into PHP warnings
* Fix: In some cases the plugin styles were not loaded
* WordPress v4.9.1 compatibility

= Version 1.0.7 (22th March 2017) =
* Fix: Date/time formatting on plugin settings (debug mode)

= Version 1.0.6 (30th August 2016) =
* Fix: Star Rating wasn't displayed correctly on Retina displays
* Updated translations

= Version 1.0.5 (19th August 2016) =
* Fix: Styles weren't loaded correctly when using older themes without body_class support
* Updated translations

= Version 1.0.4 (18th August 2016) =
* Tweak: Optimized styles in order to prevent theme issues
* Fix: Styles weren't loaded correctly when using the new ufwp shortcode

= Version 1.0.3 (17th August 2016) =
* Plugin rebranding

= Version 1.0.2 (13th August 2016) =
* Tweak: Optimized course img styles in order to prevent theme issues
* Added settings quickstart grid col example
* Templates updated: grid, list, standard, widget, widget_small

= Version 1.0.1 (8th August 2016) =
* New: Enable/disable meta via settings
* Tweak: Optimized course image responsive styles and removed important rules

= Version 1.0.0 (23th July 2016) =
* Initial release