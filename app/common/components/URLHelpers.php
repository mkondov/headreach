<?php

/**
* URL helpers - use this helper to add/remove query arguments
*/

namespace app\common\components;

class URLHelpers {

    public static function add_query_arg() {
        $args = func_get_args();
        if ( is_array( $args[0] ) ) {
            if ( count( $args ) < 2 || false === $args[1] )
                $uri = $_SERVER['REQUEST_URI'];
            else
                $uri = $args[1];
        } else {
            if ( count( $args ) < 3 || false === $args[2] )
                $uri = $_SERVER['REQUEST_URI'];
            else
                $uri = $args[2];
        }
     
        if ( $frag = strstr( $uri, '#' ) )
            $uri = substr( $uri, 0, -strlen( $frag ) );
        else
            $frag = '';
     
        if ( 0 === stripos( $uri, 'http://' ) ) {
            $protocol = 'http://';
            $uri = substr( $uri, 7 );
        } elseif ( 0 === stripos( $uri, 'https://' ) ) {
            $protocol = 'https://';
            $uri = substr( $uri, 8 );
        } else {
            $protocol = '';
        }
     
        if ( strpos( $uri, '?' ) !== false ) {
            list( $base, $query ) = explode( '?', $uri, 2 );
            $base .= '?';
        } elseif ( $protocol || strpos( $uri, '=' ) === false ) {
            $base = $uri . '?';
            $query = '';
        } else {
            $base = '';
            $query = $uri;
        }
     
        self::hr_parse_str( $query, $qs );
        $qs = self::urlencode_deep( $qs ); // this re-URL-encodes things that were already in the query string

        if ( is_array( $args[0] ) ) {
            foreach ( $args[0] as $k => $v ) {
                $qs[ $k ] = $v;
            }
        } else {
            $qs[ $args[0] ] = $args[1];
        }
     
        foreach ( $qs as $k => $v ) {
            if ( $v === false )
                unset( $qs[$k] );
        }
     
        $ret = self::build_query( $qs );
        $ret = trim( $ret, '?' );
        $ret = preg_replace( '#=(&|$)#', '$1', $ret );
        $ret = $protocol . $base . $ret . $frag;
        $ret = rtrim( $ret, '?' );
        return $ret;
    }


    /**
    * Removes an item or list from the query string.
    *
    * @param string|array $key   Query key or keys to remove.
    * @param bool|string  $query Optional. When false uses the $_SERVER value. Default false.
    * @return string New URL query string.
    */
    public static function remove_query_arg( $key, $query = false ) {
        if ( is_array( $key ) ) { // removing multiple keys
            foreach ( $key as $k )
                $query = self::add_query_arg( $k, false, $query );
            return $query;
        }
        return self::add_query_arg( $key, false, $query );
    }


    /**
    * Build URL query based on an associative and, or indexed array.
    *
    * This is a convenient function for easily building url queries. It sets the
    * separator to '&' and uses _http_build_query() function.
    *
    * @see _http_build_query() Used to build the query
    * @see http://us2.php.net/manual/en/function.http-build-query.php for more on what
    *              http_build_query() does.
    *
    * @param array $data URL-encode key/value pairs.
    * @return string URL-encoded string.
    */
    private static function build_query( $data ) {
        return self::_http_build_query( $data, null, '&', '', false );
    }

    /**
     * From php.net (modified by Mark Jaquith to behave like the native PHP5 function).
     *
     * @see http://us1.php.net/manual/en/function.http-build-query.php
     *
     * @param array|object  $data       An array or object of data. Converted to array.
     * @param string        $prefix     Optional. Numeric index. If set, start parameter numbering with it.
     *                                  Default null.
     * @param string        $sep        Optional. Argument separator; defaults to 'arg_separator.output'.
     *                                  Default null.
     * @param string        $key        Optional. Used to prefix key name. Default empty.
     * @param bool          $urlencode  Optional. Whether to use urlencode() in the result. Default true.
     *
     * @return string The query string.
     */
    private static function _http_build_query( $data, $prefix = null, $sep = null, $key = '', $urlencode = true ) {
        $ret = array();

        foreach ( (array) $data as $k => $v ) {
            if ( $urlencode)
                    $k = urlencode($k);
            if ( is_int($k) && $prefix != null )
                    $k = $prefix.$k;
            if ( !empty($key) )
                    $k = $key . '%5B' . $k . '%5D';
            if ( $v === null )
                    continue;
            elseif ( $v === false )
                    $v = '0';

            if ( is_array($v) || is_object($v) )
                    array_push($ret,_http_build_query($v, '', $sep, $k, $urlencode));
            elseif ( $urlencode )
                    array_push($ret, $k.'='.urlencode($v));
            else
                    array_push($ret, $k.'='.$v);
        }

        if ( null === $sep )
            $sep = ini_get('arg_separator.output');

        return implode($sep, $ret);
    }


    /**
    * Parses a string into variables to be stored in an array.
    *
    * Uses {@link http://www.php.net/parse_str parse_str()} and stripslashes if
    * {@link http://www.php.net/magic_quotes magic_quotes_gpc} is on.
    *
    * @param string $string The string to be parsed.
    * @param array  $array  Variables will be stored in this array.
    */
    private static function hr_parse_str( $string, &$array ) {
        parse_str( $string, $array );
        if ( get_magic_quotes_gpc() )
                $array = self::stripslashes_deep( $array );
    }


    /**
     * Navigates through an array and removes slashes from the values.
     *
     * If an array is passed, the array_map() function causes a callback to pass the
     * value back to the function. The slashes from this value will removed.
     *
     * @param mixed $value The value to be stripped.
     * @return mixed Stripped value.
     */
    private function stripslashes_deep( $value ) {
        if ( is_array($value) ) {
            
            $value = array_map( array( __CLASS__, 'stripslashes_deep' ), $value );

        } elseif ( is_object($value) ) {
            
            $vars = get_object_vars( $value );
            foreach ($vars as $key=>$data) {
                $value->{$key} = self::stripslashes_deep( $data );
            }

        } elseif ( is_string( $value ) ) {
            
            $value = stripslashes($value);

        }

        return $value;
    }


    /**
    * Navigates through an array and encodes the values to be used in a URL.
    *
    * @param array|string $value The array or string to be encoded.
    * @return array|string $value The encoded array (or string from the callback).
    */
    private static function urlencode_deep( $value ) {
        return is_array( $value ) ? array_map( array( __CLASS__, 'urlencode_deep' ), $value ) : urlencode( $value );
    }

}