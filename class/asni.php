<?php
namespace ASNI;

class Asni{
    /** @return void  */
    public static function init() {
        add_action( 'template_redirect', function() {
            // only for admin
            if ( !current_user_can( 'manage_options' ) ) return;

            global $wp;
            if( $wp->request === 'asni/create-file'){
                self::create_file_query_param();
                die;
            }
        
            if( $wp->request === 'asni/create-class'){
                self::create_class_by_query_param();
                die;
            }    
        } );
    }

    /** @return int|false  */
    public static function create_file_query_param(){
        $file_name = self::get_query_parameter_value('file_name', true);
        if ( !$file_name ) return false;

        if( !self::is_valid_file_name( $file_name ) ) {
            echo 'Invalid filename: ' . esc_html( $file_name );
            return false;
        }

        // buat lower case semua agar standard
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

        echo esc_html( $file_name ) . ' creation FAILED!, please our <a href="https://www.subarkah.com/asni" target="_blank"> documentation</a>';
        return false;
    }

    /** @return int|false  */
    public static function create_class_by_query_param(){
        $class_name = self::get_query_parameter_value('class_name', true);
        if ( !$class_name ) return false;
        
        //jika nama class tidak valid
        if( !self::is_valid_class_name($class_name) ) {
            echo 'Invalid class name: ' . esc_html( $class_name ) ;
            return false;
        }
        
        $class_file = str_replace("_", '-', $class_name);
        $class_file = strtolower($class_file) . '.php';   

        if( !self::is_valid_file_name( $class_file ) ) {
            echo 'Invalid file name for class ' . esc_html( $class_name ) ;
            return false;
        }

        $name = pathinfo($class_file, PATHINFO_FILENAME);

        return self::create_class_file($class_name, $name, true);

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

        return self::create_file( ASNI_PHP_DIR . "$file_name.php", $contents, true );
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

        // If this file is called directly, abort.
        if ( ! defined( 'ABSPATH' ) ) {
            exit;
        }

        class $class_name{

        }
        EOF;

        return self::create_file( ASNI_CLASS_DIR . "$file_name.php", $contents, true );
    }

    /**
     * @param string $file_name 
     * @param bool $echo_result 
     * @return int|false  
     */
    public static function create_css_file( $file_name, $echo_result = false ){
        return self::create_file( ASNI_CSS_DIR . "$file_name.css", '', true );
    }

    /**
     * @param string $file_name 
     * @param bool $echo_result 
     * @return int|false  
     */
    public static function create_js_file( $file_name, $echo_result = false ){
        return self::create_file( ASNI_JS_DIR . "$file_name.js", '', true );
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
     * @return string|false 
     */
    public static function get_query_parameter_value($param, $echo_result = false) {
        if( !array_key_exists ($param, $_REQUEST ) ){
            if ( $echo_result ) echo 'Parameter "' . esc_html($param) . '" does not exist!';
            return false;            
        }

        $value = $_REQUEST[$param];

        if( '' === $value ) {
            if ( $echo_result ) echo 'Value of "' . esc_html($param) . '" is empty!';
            return false;
        }
        
        return sanitize_text_field( $value ) ;
    }    
}