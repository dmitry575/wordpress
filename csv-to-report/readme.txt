=== CSV to html ===
Contributors: wibergsweb, gdeepwell
Tags: html, table, csv, excel, csv into html, csv into table, csv to html, csv to table, html table generator,csv to html table, multiple
Requires PHP: 5.6
Requires at least: 3.0.1
Tested up to: 5.6
Stable tag: 1.1.86
License: GPLv2
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Displays a responsive (html)table from one or more csv files dynamically directly from server or external files (works with Google sheets to). Very flexible and configurable with debug functionality to get it wokring as fast as possible.

== Description ==

CSV to report makes it easy to fetch content from csv-file(s) and put content from that file/those files and save it to html file. Than display the html(table) on a page with a single shortcode. The strength
of this plugin is that it does fetch the actual content directly from the file(s) without having to import/export any file(s). So any changes in the file(s) will be 
updated when you view your table(s) with this plugin.


If using more then one file, content from all files are mixed into one single table - rather then creating two tables. It's possible to fetch information from csv files from webservers upload folder (or a subfolder to the uploadsfolder) or
from an external source (domain).

Look at instructions and example here: <https://github.com/dmitry575/wordpress/htmltoreport>


== Installation ==

This section describes how to install the plugin and get it working.

1. Upload the plugin folder csvtoreport to the `/wp-content/plugins/' directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Put shortcode on the Wordpress post or page you want to display it on and add css to change layout for those.

= Shortcodes =
* [csvtoreport] - Create the html table from specified csv-file(s)

= [csvtoreport source_files='reports.html'] attributes =
* responsive - yes/no - The default is yes, but this could be set to no if you have issues with other css on your site.
* css_max_width - media query css rules/breakpoint. This is only applied when responsive is set to yes.
* css_min_devicewidth - media query css rules/breakpoint. This is only applied when responsive is set to yes.
* css_max_devicewidth - media query css rules/breakpoint. This is only applied when responsive is set to yes.
* title - set title that is shown as text in top left corner of html table (else nothing is shown there)
* html_id - set id of this table
* html_class - set class of this table (besides default csvtoreport - class)
* path - relative path to uploads-folder of the wordpress - installation ( eg. /wp-content/uploads/{path} )
* fetch_lastheaders - Number of specific headers to retrieve (from end)
* source_files - file(s) to include
* csv_delimiter - what delimiter to use in each line of csv (comma, semicolon etc)
* debug_mode - If set to yes then header-values and row-values would be output to screen and files like "file not found" will be displayed (otherwise it would be "silent errors")

= Default values =
* [csvtoreport css_max_width=760 css_min_devicewidth=768 css_max_devicewidth=1024 title="{none}" html_id="{none}" html_class="{none}" source_type="visualizer_plugin" path="{none}" fetch_lastheaders="0" source_files="{none}" csv_delimiter="," exclude_cols="{none} include_cols="{none}" table_in_cell_cols="{none}" table_in_cell_header="More data" filter_data="{none}" filter_operater="equals" filter_removechars="{none}" filter_col="{none}" eol_detection="cr/lf" convert_encoding_from="{none}" convert_encoding_to="{to}" sort_cols="{none}" sort_cols_order="asc" add_ext_auto="yes" float_divider="." pagination="no" pagination_below_table="yes" pagination_above_table="no" pagination_start=1 pagination_text_start="Start" pagination_text_prev="Prev" pagination_text_next="Next" pagination_text_last="Last" pagination_rows="10" pagination_links_max="10" search_functionality="no" searchbutton_text="Search" resetbutton_text="Reset" debug_mode="no"]

Look at instructions and example here: <https://github.com/dmitry575/wordpress/csvreport/>
