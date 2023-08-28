<?php
/**
 * The core plugin class
 * 
 * @link https://github.com/akah-martono/asni
 * @package ASNI
 * 
 * @author Akah Martono <https://www.subarkah.com>
 * @since 0.1
 */
namespace ASNI;

class Asni{
    /** @return void  */
    public static function init() {
        add_action( 'template_redirect', function() {
            
            // only avaiblable for admin
            if ( !current_user_can( 'manage_options' ) ) return;

            global $wp;
            $more_info = '<br>For more information, please read our <a href="https://www.subarkah.com/asni" target="_blank"> documentation</a>';
            
            if( $wp->request === 'asni/create-file'){
                if( false === self::create_file_by_query_param() ){
                    echo wp_kses_post( $more_info );
                }
                die;
            }
        
            if( $wp->request === 'asni/create-class'){
                if( false === self::create_class_by_query_param() ) {
                    echo wp_kses_post( $more_info );
                }
                die;
            }

            if( $wp->request === 'asni/migrate'){
                if( false === self::migrate_by_query_param() ) {
                    echo wp_kses_post( $more_info );
                }
                die;
            }               
        } );
    }

    /** @return int|false  */
    private static function create_file_by_query_param(){
        $file_name = self::get_query_parameter_value('file_name', true);
        if ( !$file_name ) return false;

        if( !self::is_valid_file_name( $file_name ) ) {
            echo 'Invalid filename: ' . esc_html( $file_name );
            return false;
        }

        // convert to lowercase in order to standardize
        $name = strtolower($file_name);

        $name = pathinfo($file_name, PATHINFO_FILENAME);
        $ext = pathinfo($file_name, PATHINFO_EXTENSION);

        if( $ext === 'php' ) {
            return self::create_php_file($name, true);
        }

        if( $ext === 'css' ) {
            return self::create_css_file($name, true);
        }

        if( $ext === 'js' ) {
            return self::create_js_file($name, true);
        }

        echo 'Only file with php, css, and js extension are allowed.';
        return false;
    }

    /** @return int|false  */
    private static function create_class_by_query_param(){
        $class_name = self::get_query_parameter_value('class_name', true);
        if ( !$class_name ) return false;
        
        if( !self::is_valid_class_name($class_name) ) {
            echo 'Invalid class name: ' . esc_html( $class_name ) ;
            return false;
        }
        
        //convert class_name to file_name (make sure it's inline with autoloader)
        $class_file = str_replace("_", '-', $class_name);
        $class_file = strtolower($class_file) . '.php';   

        if( !self::is_valid_file_name( $class_file ) ) {
            echo 'Invalid file name for class ' . esc_html( $class_name ) ;
            return false;
        }

        $name = pathinfo($class_file, PATHINFO_FILENAME);

        return self::create_class_file($class_name, $name, true);
    }
    
    /** @return bool  */
    private static function migrate_by_query_param() {
        $new_plugin_dir = self::get_query_parameter_value('new_dir', true);
        if ( !$new_plugin_dir ) return false;

        $new_plugin_path = WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . $new_plugin_dir . DIRECTORY_SEPARATOR;

        if ( !( file_exists( $new_plugin_path ) && is_dir( $new_plugin_path ) ) ) {
            echo 'New Plugin Dir: ' . esc_html($new_plugin_path) . ' does not exist';
            return false;
        }

        self::copy_files(
            ASNI_CLASS_DIR, 
            $new_plugin_path . 'class' . DIRECTORY_SEPARATOR, 
            ['extension'=>'php', 'exclude'=>['asni.php','index.php']], 
            true
        );
        
        self::copy_files(
            ASNI_PHP_DIR, 
            $new_plugin_path . 'php' . DIRECTORY_SEPARATOR, 
            ['extension'=>'php', 'exclude'=>'index.php'], 
            true
        );

        self::copy_files(
            ASNI_CSS_DIR, 
            $new_plugin_path . 'css' . DIRECTORY_SEPARATOR, 
            ['extension'=>'css'], 
            true
        );
        
        self::copy_files(
            ASNI_JS_DIR, 
            $new_plugin_path  . 'js' . DIRECTORY_SEPARATOR, 
            ['extension'=>'js'], 
            true
        );

        return true;
    }    

    /**
     * @param string $file_name 
     * @param bool $echo_result 
     * @return int|false  
     */
    public static function create_php_file( $file_name, $echo_result = false ){
        $contents = <<<EOF
        <?php
        
        // If this file is called directly, abort.
        if ( ! defined( 'ABSPATH' ) ) {
            exit;
        }
        EOF;

        return self::create_file( ASNI_PHP_DIR . "$file_name.php", $contents, $echo_result );
    }

    /**
     * @param string $file_name 
     * @param bool $echo_result 
     * @return int|false  
     */
    public static function create_class_file( $class_name, $file_name, $echo_result = false ){
        $contents = <<<EOF
        <?php 
        namespace ASNI;

        class $class_name{

        }
        EOF;

        return self::create_file( ASNI_CLASS_DIR . "$file_name.php", $contents, $echo_result );
    }

    /**
     * @param string $file_name 
     * @param bool $echo_result 
     * @return int|false  
     */
    public static function create_css_file( $file_name, $echo_result = false ){
        return self::create_file( ASNI_CSS_DIR . "$file_name.css", '', $echo_result );
    }

    /**
     * @param string $file_name 
     * @param bool $echo_result 
     * @return int|false  
     */
    public static function create_js_file( $file_name, $echo_result = false ){
        return self::create_file( ASNI_JS_DIR . "$file_name.js", '', $echo_result );
    }
    
    /**
     * @param string $file 
     * @param string $contents 
     * @return int|false 
     */
    private static function create_file( $file, $contents, $echo_result = false ){
        $result = 'exist';
        if( !file_exists( $file ) ){
            $result = file_put_contents( $file, $contents );
            if( $result ) {
                chmod($file, 0644);
            }
        }

        if( $echo_result ){
            $str_result = '';
            if ( $result === false ){
                $str_result =  "$file: creation FAILED!";
            } elseif ( $result === 'exist' ){
                $result = false;
                $str_result =  "$file: already EXIST!";
            } else {
                $str_result =  "$file: created SUCCESSFULLY!";
            }

            echo esc_html( $str_result );
        }

        return $result;
    }
    
    /**
     * @param string $file 
     * @return bool 
     */
    public static function is_valid_file_name($file) {
        return preg_match('/^([-\.\w]+)$/', $file) > 0;
    }

    /**
     * @param string $class 
     * @return bool 
     */
    public static function is_valid_class_name($class) {
        // according to https://www.php.net/manual/en/language.oop5.basic.php
        return preg_match( '/^[a-zA-Z_\x80-\xff][a-zA-Z0-9_\x80-\xff]*$/', $class ); 
    }

    /**
     * @param string $param 
     * @param bool $echo_error 
     * @return string|false 
     */
    private static function get_query_parameter_value($param, $echo_error = false) {
        if( !array_key_exists ($param, $_REQUEST ) ){
            if ( $echo_error ) echo 'Parameter "' . esc_html($param) . '" does not exist!';
            return false;            
        }

        $value = $_REQUEST[$param];

        if( '' === $value ) {
            if ( $echo_error ) echo 'Value of "' . esc_html($param) . '" is empty!';
            return false;
        }
        
        return sanitize_text_field( $value ) ;
    }

    /**
     * @param string $source_path full path (included separator at the end)
     * @param string $destination_path full path (included separator at the end)
     * @param array $filter 
     * @param bool $echo_result 
     * @return void 
     */
    private static function copy_files($source_path, $destination_path, $filter = [], $echo_result = false){        
        $extension = array_key_exists('extension', $filter)? $filter['extension'] : [];        
        $exclude = array_key_exists('exclude', $filter)? $filter['exclude'] : [];

        if ( is_string($extension) ) {
            $extension = [$extension]; 
        }

        if ( is_string($exclude) ) {
            $exclude = [$exclude];
        }

        $str_result = '';

        if ( !( file_exists( $source_path ) && is_dir( $source_path ) ) ) {
            if( $echo_result ) echo 'Source: ' . esc_html($source_path) . ' does not exist';
            return;
        }

        if (!( is_dir( $destination_path ) )){
            if( $echo_result ) echo 'Destination: ' . esc_html($destination_path) . ' does not exist';
            return;
        }

        $copied = 0;
        $files = scandir($source_path);  
        foreach ($files as $file) {
            $source_file = $source_path . $file;
            $destination_file = $destination_path . $file;
            if (is_file($source_file)) {
                $source_file_ext = pathinfo($source_file, PATHINFO_EXTENSION);
                
                if ( !( empty( $extension ) || in_array($source_file_ext, $extension) ) ) break;

                if ( in_array($file, $exclude) ) break;

                if ( file_exists( $destination_file ) ){
                    $str_result .=  "$destination_file Already exist<br>";
                } elseif ( copy($source_file, $destination_file) ){
                    $copied ++;
                    $str_result .=  "$source_file Succeessfuly copied to $destination_file<br>";
                } else {
                    $str_result .=  "$source_file Failed to copy<br>";
                }   
            }
        }

        $str_result .= "$copied file(s) copied from $source_path<br>";

        if( $echo_result ) echo wp_kses_post("<p>$str_result</p>");
    }
}