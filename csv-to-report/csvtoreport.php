<?php
/*
Plugin Name: CSV to html report
Plugin URI: https://github.com/dmitry575
Description: CSV файлы в html таблицу
Version: 1.1.86
Author: Dmitry Tarakanov
Author URI: https://github.com/dmitry575
Text Domain: csv to html report
Domain Path: /lang
License: GPLv2
*/
defined('ABSPATH') or die('No access allowed!');

if (!class_exists('csvtoreport')) {
    ini_set("auto_detect_line_endings", true); //Does not apply when loading external file(s), therefore also custom function for this below

    /**
     * Class converting
     *
     */
    class csvtoreport
    {
        private $csv_delimit; //Used when using anynmous function in array_map when loading file(s) into array(s)

        /**
         *  Constructor
         *
         *  This function will construct all the neccessary actions, filters and functions for the sourcetotable plugin to work
         *
         * @param N/A
         * @return    N/A
         */
        public function __construct()
        {
            add_action('init', array($this, 'loadlanguage'));
        }


        /**
         * loadlanguage
         *
         * This function load translations (if there are any)
         *
         * @param N/Acsvtohtml_create source_files
         * @return    N/A
         *
         */
        public function loadlanguage()
        {
            $loaded_translation = load_plugin_textdomain('csv-to-report', false, dirname(plugin_basename(__FILE__)) . '/lang/');
            if (session_id() == '') {
                session_start();
            }
            $this->init();
        }


        /**
         *  init
         *
         *  This function initiates the actual shortcodes etc
         *
         * @param N/A
         * @return N/A
         *
         */
        public function init()
        {

            wp_register_style('csvtohtml-css', plugins_url('/css/wibergsweb.css', __FILE__), false);
            wp_enqueue_style('csvtohtml-css');

            wp_enqueue_script('jquery');
            wp_enqueue_script(
                'csvtohtml-js',
                plugins_url('/js/wibergsweb.js', __FILE__, array('jquery'))
            );
            wp_localize_script('csvtohtml-js', 'my_ajax_object', array('ajax_url' => admin_url('admin-ajax.php')));

            add_shortcode('csvtoreport', array($this, 'report_table'));
            add_action('admin_menu', array($this, 'help_page'));
            add_action('admin_post_tokmakov_upload_file', array($this, 'upload_csv_file'));
        }

        public function upload_csv_file()
        {

            $check1 = wp_verify_nonce(
                $_POST['_wpnonce'],
                'tokmakov_upload_file'
            );
            $check2 = current_user_can('upload_files');
            if ($check1 && $check2) {


                $messages = [];
                foreach ($_FILES['tokmakov_upload_file']['name'] as $i => $v) {
                    $file = [
                        'name' => self::generate_file_name($_FILES['tokmakov_upload_file']['name'][$i]),
                        'type' => $_FILES['tokmakov_upload_file']['type'][$i],
                        'tmp_name' => $_FILES['tokmakov_upload_file']['tmp_name'][$i],
                        'error' => $_FILES['tokmakov_upload_file']['error'][$i],
                        'size' => $_FILES['tokmakov_upload_file']['size'][$i],
                    ];
                    // upload file to path uploads
                    $result = media_handle_sideload($file, 0);
                    // view result of upload
                    if (is_wp_error($result)) {
                        $messages[] = '<b>Ошибка при загрузке файла ' . $file['name'] . '</b>';
                    } else {
                        $messages[] = '<b style="color: green">Файл ' . $file['name'] . ' успешно загружен</b>';
                    }
                }
            } else {
                $messages[] = 'Проверка не пройдена, файлы не загружены';
            }
            $upload_dir = wp_upload_dir();
            $upload_basedir = $upload_dir['basedir'];

            $new_arr['source_files'] = $upload_dir['subdir'] . '/' . $file['name'];
            $html = $this->source_to_table($new_arr);

            file_put_contents($upload_basedir . '/report.html', $html);
            $_SESSION['tokmakov_upload_file'] = $messages;

            $redirect = home_url();
            if (isset($_POST['redirect'])) {
                $redirect = $_POST['redirect'];
                $redirect = wp_validate_redirect($redirect, home_url());
            }

            wp_redirect($redirect);
            die();
        }


        public function help_page()
        {
            add_management_page('CSV Report', 'CSV Report', 'manage_options', 'csv-to-report', array($this, 'startpage'));
        }

        /**
         *  Delete all not english letter
         *
         * @param string $filename
         * @return mixed|string
         */
        static public function generate_file_name($filename)
        {
            if (preg_match('/[^0-9a-zA-Z_\.]/u', $filename, $out, PREG_OFFSET_CAPTURE)) {
                return preg_replace_callback('/[^0-9a-zA-Z_\.]/u', "self::randomsymbol", $filename);
            }
            return strtolower($filename);
        }

        /**
         * Generating random symbols
         *
         * @param array $matches
         * @return char
         */
        static function randomsymbol($matches)
        {
            switch ($matches[0]) {
                case ';':
                case '(':
                case '\'':
                case ')':
                case '!':
                    return '_';
                    break;
            }
            return chr(rand(97, 122));
        }

        /**
         *  startpage
         *
         *  This function shows examples and information about the plugin
         *
         * @param N/A
         * @return N/A
         *
         */
        public function startpage()
        {
            $html = '<div class="wrap">';
            $html .= '<h1>Загрузка CSV остатков</h1>';

            $upload_dir = wp_upload_dir();
            $upload_basedir = $upload_dir['basedir'];

            echo $html;
            ?>
            <div class="wrap">
                <h1>Загрузка файла</h1>
                <p>
                    Плагин загружает CSV файл и конвертирует его в <code>/wp-content/uploads/report.html</code>.
                </p>

                <?php

                if (isset($_SESSION['tokmakov_upload_file'])): ?>
                    <ul class="color: green">
                        <?php foreach ($_SESSION['tokmakov_upload_file'] as $message): ?>
                            <li><?= $message; ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <?php unset($_SESSION['tokmakov_upload_file']); ?>
                <?php endif; ?>
                <?php
                $action = admin_url('admin-post.php');
                $redirect = $_SERVER['REQUEST_URI'];
                ?>
                <form action="<?= $action; ?>" method="post" enctype="multipart/form-data">
                    <?php wp_nonce_field('tokmakov_upload_file'); ?>
                    <input type="hidden" name="action" value="tokmakov_upload_file"/>
                    <input type="hidden" name="redirect" value="<?= $redirect ?>"/>
                    <input type="file" name="tokmakov_upload_file[]" multiple required/>
                    <input type="submit" value="Загрузить файл"/>
                </form>
            </div>
            <?

            $html = '<ul>';
            $html .= '<li>';
            $html .= '<h2>Как использовать</h2>';
            $html .= 'Для того, чтобы отобразить файл report.html нужно на странице вставить следующий код из примера';
            $html .= '</li>';
            $html .= '<hr>';
            $html .= '<li>';
            $html .= __('<i>Пример 1:</i> <strong>[csvtoreport source_files=”report.html”]</strong>', 'csv-to-report');
            $html .= '</li>';
            $html .= '<hr>';

            $html .= '</ul>';

            $html .= '<h2>Поддержка плагина</h2>';
            $html .= '<p>';
            $html .= 'По всем вопросам вы можете обращаться по емайлу:';
            $html .= '<a target="_blank" href="mailto:mail.dimm@gmail.com">mail.dimm@gmail.com</a>';
            $html .= '</p>';
            $html .= '</div>';
            echo $html;
        }


        /**
         *   get_defaults
         *
         *  Helper function for setting default array-values
         *  It's public because of usage from CSV to HTML Premium.
         *
         * @param void
         * @return array defaultvalues for shortcode
         *
         */
        public function get_defaults()
        {

            $defaults = array(
                'responsive' => 'yes',           //If set to no there won't be any class (responsive-html added and no css-rules would be applied for responsive table). If set to yes, there would basic settings of responsive tables.
                'css_max_width' => 760,         //breakpoints media-query
                'css_min_devicewidth' => 768,   //breakpoints media-query
                'css_max_devicewidth' => 1024,  //breakpoints media-query
                'html_id' > null,               //html id of table
                'html_class' => null,           //class(es) set for table
                'title' => null, //if given then put titletext in top left corner
                'path' => '', //This is the base path AFTER the upload path of Wordpress (eg. /2016/03 = /wp-content/uploads/2016/03)
                'source_type' => 'visualizer_plugin', //So plugin knows HOW to fetch content from file(s)
                'source_files' => null, //Files are be divided with sources_separator (file1;file2 etc). It's also possible to include urls to csv files. It's also possible to use a wildcard (example *.csv) for fetching all files from specified path. This only works when fetching files directly from own server.
                'csv_delimiter' => ';', //Delimiter for csv - files (defaults to comma)
                'add_ext_auto' => 'yes', //If file is not included with .csv, then add .csv automatically if this value is yes. Otherwise, set no
                'float_divider' => '.', //If fetching float values from csv use this character to display "float-dividers" (default 6.4, 1.2 etc)
                'debug_mode' => 'no'
            );

            return $defaults;
        }

        public function report_table()
        {

            $defaults = $this->get_defaults();

            //Extract values from shortcode and if not set use defaults above
            $args = wp_parse_args($defaults);
            extract($args);

            //Base upload path of uploads
            $upload_dir = wp_upload_dir();
            $upload_basedir = $upload_dir['basedir'];
            $filename = $upload_basedir . '/report.html';
            if ($debug_mode === 'yes') {
                require_once("debug.php");
                $debug_obj = new debug($args);
                if ($debug_obj === true) {
                    return;
                }
            }

            if (!file_exists($filename)) {
                $debug_obj->show_msg('<pre>Files not exists: ' . $filename . '<br><br>');
                return '';
            }

            return file_get_contents($filename);
        }

        static public function parse_csv_quotes($matches)
        {
            //anything inside the quotes that might be used to split the string into lines and fields later,
            //needs to be quoted. The only character we can guarantee as safe to use, because it will never appear in the unquoted text, is a CR
            //So we're going to use CR as a marker to make escape sequences for CR, LF, Quotes, and Commas.
            $str = str_replace("\r", " ", $matches[3]);
            $str = str_replace("\n", " ", $str);
            $str = str_replace('""', " ", $str);
            $str = str_replace(';', " ", $str);

            //The unquoted text is where commas and newlines are allowed, and where the splits will happen
            //We're going to remove all CRs from the unquoted text, by normalizing all line endings to just LF
            //This ensures us that the only place CR is used, is as the escape sequences for quoted text
            return preg_replace('/\r\n?/', "\n", $matches[1]) . $str;
        }

        /**
         *   source_to_table
         *
         *  This function creates a (html) table based on given source (csv) files
         *  Files are divided by semicolon
         *
         * @param string $attr shortcode attributes
         * @return   string                      html-content
         *
         */
        public function source_to_table($attrs)
        {
            $defaults = $this->get_defaults();

            //Extract values from shortcode and if not set use defaults above
            $args = wp_parse_args($attrs, $defaults);
            extract($args);

            $this->csv_delimit = $csv_delimiter; //Use this char as delimiter

            if ($debug_mode === 'yes') {
                require_once("debug.php");
                $debug_obj = new debug($args);
                if ($debug_obj === true) {
                    return;
                }
            }

            //Base upload path of uploads
            $upload_dir = wp_upload_dir();
            $upload_basedir = $upload_dir['basedir'];

            //If user has put some wildcard in source_files then create a list of files
            //based on that wildcard in the folder that is specified
            if (stristr($source_files, '*') !== false) {
                $files_path = glob($upload_basedir . '/' . $path . '/' . $source_files);
                if ($debug_mode === 'yes') {
                    $debug_obj->show_msg('<pre>Files grabbed from wildcard: ' . $upload_basedir . '/' . $path . '/' . $source_files . '<br><br>');
                }

                $source_files = '';
                foreach ($files_path as $filename) {
                    if ($debug_mode === 'yes') {
                        $debug_obj->show_msg(basename($filename) . "<br>");
                    }
                    $source_files .= basename($filename) . ';';
                }
                if (strlen($source_files) > 0) {
                    $source_files = substr($source_files, 0, -1); //Remove last semicolon
                } else {
                    if ($debug_mode === 'yes') {
                        $debug_obj->show_msg(__('Wildcard set for source-files but no source file(s) could be find in specified path.', 'csv-to-report'));
                    }
                }

                if ($debug_mode === 'yes') {
                    $debug_obj->show_msg('</pre>');
                }
            }

            $sources = explode(';', $source_files);

            //Create an array of ("csv content")
            $content_arr = array();

            foreach ($sources as $s) {


                $file = $s;
                //Add array item with content from file(s)

                //If source file do not have http or https in it or if path is given, then it's a local file
                $local_file = true;


                //Load local file into content array
                if ($local_file === true) {

                    if (strlen($path) > 0) {
                        $file = $upload_basedir . '/' . $path . '/' . $file; //File from uploads folder and path
                    } else {
                        $file = $upload_basedir . $file; //File directly from root upload folder
                    }
                    if (file_exists($file)) {

                        //Put an array with csv content into this array item
                        $str = preg_replace_callback('/([^"]*)("((""|[^"])*)"|$)/s', 'self::parse_csv_quotes', file_get_contents($file));
                        //remove the very last newline to prevent a 0-field array for the last line
                        $str = preg_replace('/\n$/', '', $str);

                        $content_arr[] = array_map(function ($v) {
                            return str_getcsv($v, $this->csv_delimit);
                        }, explode("\n", $str));
                    } else if ($debug_mode === 'yes') {
                        $debug_obj->show_msg($file . ' ' . __('not found', 'csv-to-report'));
                    }
                }
            }

            if (count($content_arr) === 0 && $debug_mode === 'yes') {
                $debug_obj->show_msg(__('No files found', 'csv-to-report'));
                return;
            }
            $content_arr = $content_arr[0];

            $header_values = $content_arr[0];

            $row_values = array_slice($content_arr, 1);

            //Create table
            if (isset($html_id)) {
                $htmlid_set = 'id="' . $html_id . '" ';
            } else {
                $htmlid_set = '';
            }

            if (isset($html_class)) {
                $html_class = ' ' . $html_class;
                if ($responsive === 'yes') {
                    $html_class .= ' responsive-csvtohtml';
                }
            } else {
                $html_class = '';
                if ($responsive === 'yes') {
                    $html_class = ' responsive-csvtohtml';
                }
            }

            $html = '';

            //Responsive table(s)?
            //Then add "title" when lower resolutions (e.g. smartphones)
            if ($responsive === 'yes') {
                /*
                default values:
                'css_max_width' => 760,
                'css_min_devicewidth' => 768,
                'css_max_devicewidth' => 1024,
                */
                if (intval($css_max_width) == 0) {
                    $css_max_width = 760;
                }
                if (intval($css_min_devicewidth) == 0) {
                    $css_min_devicewidth = 768;
                }
                if (intval($css_max_devicewidth) == 0) {
                    $css_max_devicewidth = 1024;
                }

                //If having more than one table on a page
                //then each has to have an id to separate td-before-content below (responsive)
                //The unique_id() is based on time so it can never be exactly the same id
                if (empty($html_id)) {
                    $html_id = uniqid('csvtohtml_id-');
                    $htmlid_set = 'id="' . $html_id . '" ';
                }


                //Take for granted HTML5 is used. Then this below is ok
                $html .= '<style>';
                $html .= '@media 
            only screen and (max-width: ' . $css_max_width . 'px),
            (min-device-width: ' . $css_min_devicewidth . 'px) and (max-device-width: ' . $css_max_devicewidth . 'px)  {';
                foreach ($header_values as $hvkey => $hv) {
                    $html .= 'table#' . $html_id . '.csvtohtml.responsive-csvtohtml td:nth-of-type(' . ($hvkey) . '):before { content: "' . $header_values[$hvkey] . '"; }';
                }
                $html .= '}';
                $html .= '</style>';
            }


            $html .= '<table ' . $htmlid_set . 'class="csvtohtml' . $html_class . '"><thead><tr class="headers">';
            $nr_col = 1;
            foreach ($header_values as $hv) {
                $html .= '<th class="colset colset-' . $nr_col . '">' . $hv . '</th>';
                $nr_col++;
            }
            $html .= '</tr></thead><tbody>';

            $nr_row = 1;
            $pyj_class = 'even';
            $classes_align = array();
            //
            $i = 0;
            foreach ($row_values[0] as $inner) {
                $classes_align[$i] = self::add_style($inner);
                $i++;
            }

            foreach ($row_values as $rv) {

                $html .= '<tr class="rowset ' . $pyj_class . ' rowset-' . $nr_row . '">';
                if ($pyj_class === 'odd') {
                    $pyj_class = 'even';
                } else {
                    $pyj_class = 'odd';
                }

                $nr_col = 1;

                foreach ($rv as $inner_value) {
                    //Display other float divider (e.g. 6,3 instead 6.2)
                    if ($float_divider != '.') {
                        $inner_value[1] = str_replace('.', $float_divider, $inner_value[1]);
                    }
                    $inner_value = trim($inner_value);

                    $html .= '<td class="colset colset-' . $nr_col . $classes_align[$nr_col - 1] . '">' . $inner_value . '</td>';
                    $nr_col++;
                }
                $html .= '</tr>';
                $nr_row++;
            }

            $html .= '</tbody></table>';

            return $html;
        }

        private static function add_style($text)
        {
            //var_dump(filter_var ($text,FILTER_VALIDATE_INT ));
            if (filter_var($text, FILTER_VALIDATE_INT))
                return ' colset-right';
            if (filter_var(str_replace(array(' ', ','), '', $text), FILTER_VALIDATE_FLOAT))
                return ' colset-right';
        }
    }

    $csvtoreport = new csvtoreport();
}