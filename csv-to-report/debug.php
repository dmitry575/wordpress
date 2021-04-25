<?php
/**
*
* Debugging class for CSV to html plugin
* This separation is done because of unnecessary code is executed if debugging mode is off (debug_mode="no")
*
*/
defined( 'ABSPATH' ) or die( 'No access allowed!' );

if( !class_exists('debug') ) {

    class debug extends csvtohtmlwp {
        private $csvtohtml_msg;
        
        public function __construct($args = null) 
        {
            if ($args === null || !is_array($args)) 
            {
                echo __('No arguments supplied or arguments are not an array');
                return true;
            }

            $this->show_msg ( __('Debuging functionality iniated.'));
            $this->show_msg ( $args );
            extract ( $args );

            //Errors
            if ( $source_files === null) 
            {
                $this->csvtohtml_msg = __('No source file(s) given. At least one file (or link) must be given', 'csvtohtml-wp');
                $this->show_msg();
                return true;
            }

            if ( $this->valid_sourcetypes( $source_type ) === false ) 
            {
                $this->csvtohtml_msg = __('No valid sourcetype given.', 'csvtohtml-wp');
                $this->show_msg();
                return true;
            }

            if ( strlen($float_divider) >1 ) 
            {
                $this->csvtohtml_msg = __('Float divider can only contain one character.', 'csvtohtml-wp');
                $this->show_msg();
                return true;
            }

            //Warnings
            if ( $source_type !== 'guess')
            {
                echo '<pre>';
                echo __('If not expected output of data, try adding source_type="guess" to your shortcode.', 'csvtohtml-wp');
                echo '</pre>';
            }
            
            if (stristr($source_files,'docs.google.com') !== false && stristr($source_files,'pub?output=csv') === false) 
            {
                echo '<pre>';
                echo __('It seems like you are trying to access csv file through google drive. You have to publish that document to the web chosing csv as an option.','csvtohtml-wp');
                echo __('<br>The link should end with pub?output=csv (copy the link first time you try to publish the csv file on the web)','csvtohtml-wp');
                echo '</pre>';                
            }     

            if (stristr($source_files,'docs.google.com') !== false && $add_ext_auto === 'yes') 
            {
                echo '<pre>';
                echo __('In the shortcode you might try to add_ext_auto="no" for this to work.', 'csvtohtml-wp');                    
                echo '</pre>';
            }

            //Not directly errors/warnings but some settings may have to change to show expected result(s)
            //
            if ( strlen( $filter_data ) > 0 )
            {
                if ( $filter_col === null) 
                {
                    echo '<pre>';
                    echo __('Results when filtering and it\'s numeric chars<br>with a prefix or suffix (like a percent sign), then you could try with the attribute filter_removechars="{actual character(s)}','csvtohtml-wp');
                    echo __('You must specify column to apply the filter on','csvtohtml-wp');
                    echo '</pre>';                    
                }

                if ( strlen( $filter_operator ) > 0 )
                {
                    echo '<pre>';
                    echo __('If you get unexpected results when filtering and it\'s numeric chars<br>with a prefix or suffix (like a percent sign), then you could try with the attribute filter_removechars="{actual character(s)}','csvtohtml-wp');
                    echo '</pre>';
                }

                if ($filter_operator == 'between') 
                {
                    $filter_data = explode( '-', $filter_data );
                    if ( count ($filter_data) !== 2 ) 
                    {
                        echo '<pre>';
                        echo __('You have set between in filter_operator but have not provided a hyphen in your filter_data','csvtohtml-wp');
                        echo '</pre>';
                    }
                    else 
                    {   
                        //Hyphen is set but value nr 2 is not
                        if ( strlen($filter_data[0]) == 0 || strlen($filter_data[1]) == 0 )  
                        {
                            echo '<pre>';
                            echo __('You have set between in filter_operator but you have to set a value before the hyphen (-) and one value after the hyphen (-).','csvtohtml-wp');
                            echo '</pre>';    
                        }
                    }
                }      
                else if ( strlen( $filter_operator ) > 0 ) 
                {
                    if ( strpos( $filter_data, '-') !== false ) 
                    {
                        echo '<pre>';
                        echo __('You have a hyphen (-) in your filter_data that does not have affect on the filter_operator ' . $filter_operator, 'csvtohtml-wp');
                        echo '</pre>';
                    }                
                }

            }
        }

        /**
        * Show error or message when debugging plugin
        * If an array is sent to this function then show values of the array nicely
        * 
        * @param N/A
        * @return N/A
        *                 
        */                 
        public function show_msg($custom_message = null) 
        {               
            if ($custom_message === null) 
            {
                $custom_message = $this->csvtohtml_msg;
            }
            
            if (is_array($custom_message)) 
            {
                echo '<pre>';
                var_dump($custom_message);
                echo '</pre>';
            }
            else {
                echo "{$custom_message}"; 
            }
            return;
        }


        /**
        * Check encoding given in csv file (if it's possible to use and if all necessary values set)
        *
        * @param $convert_encoding_from     What characterset to encode from? (If not set uses default)
        * @param $convert_encoding_to       What characterset to encode to?   (must be set)
        *
        */
        public function check_encoding( $convert_encoding_from, $convert_encoding_to )
        {
            $encoding_error = false;
            if ( $convert_encoding_from !== null && $convert_encoding_to === null)
            {
                $this->show_msg( '<strong>' . __('You must tell what encoding to convert to', 'csvtohtml-wp') . '</strong><br>' );   
                $encoding_error = true;
            }

            if ( $convert_encoding_from == $convert_encoding_to) {
                $this->show_msg( __('Encoding from and encoding to are the same. This works but does slow does slow performance.') . '<br>');                
            }
            
            if ( $convert_encoding_from !== null ) 
            {
                if (in_array($convert_encoding_from, mb_list_encodings() ) === false) 
                {                        
                    $this->show_msg( __('Convert FROM encoding (' . $convert_encoding_from . ') is not supported (make sure upper/lower case is correct)', 'csvtohtml-wp') . '<br>' );   
                    $encoding_error = true;
                }
            }

            if ( $convert_encoding_to !== null ) 
            {
            if (in_array($convert_encoding_to, mb_list_encodings() ) === false)
            {
                $this->show_msg( __('Convert TO encoding (' . $convert_encoding_to . ') is not supported (make sure upper/lower case is correct)', 'csvtohtml-wp') . '<br>' );      
                $encoding_error = true;
            }
            }
            
            if ( $encoding_error === true )
            {
                $this->show_msg( __('Supported encodings:') );
                $this->show_msg( mb_list_encodings() );   
                return true;                  
            }

        }

    }

}