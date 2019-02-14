<?php
namespace Minor\Framework;

class request{
    private $conf;

    private $fs;

    private $param = array();

    private $flg_cmd = false;

    private $request_file_path;

    private $directory_index_primary;

    private $cli_options;

    private $cli_params;

    public function __construct($conf=null){
        $this->conf = $conf;
        if( !is_object($this->conf) ){
            $this->conf = json_decode('{}');
        }
        //$this->fs = new \tomk79\filesystem();

        if(!@is_array($this->conf->get)){
            $this->conf->get = $_GET;
        }
        if(!@is_array($this->conf->post)){
            $this->conf->post = $_POST;
        }
        if(!@is_array($this->conf->files)){
            $this->conf->files = $_FILES;
        }
        if(!@is_array($this->conf->server)){
            $this->conf->server = $_SERVER;
        }
        if( !array_key_exists( 'PATH_INFO' , $this->conf->server ) ){
            $this->conf->server['PATH_INFO'] = null;
        }
        if( !array_key_exists( 'HTTP_USER_AGENT' , $this->conf->server ) ){
            $this->conf->server['HTTP_USER_AGENT'] = null;
        }
        if( !array_key_exists( 'argv' , $this->conf->server ) ){
            $this->conf->server['argv'] = null;
        }
        if(!@strlen($this->conf->session_name)){
            $this->conf->session_name = 'SESSID';
        }
        if(!@strlen($this->conf->session_expire)){
            $this->conf->session_expire = 1800;
        }
        if(!@strlen($this->conf->directory_index_primary)){
            $this->conf->directory_index_primary = 'index.html';
        }
        if(!@strlen($this->conf->cookie_default_path)){
            $this->conf->cookie_default_path = $this->get_path_current_dir();
        }

        $this->parse_input();
        $this->session_start();
    }

    private function parse_input(){
        $this->request_file_path = $this->conf->server['PATH_INFO'];
        if( !strlen($this->request_file_path) ){
            $this->request_file_path = '/';
        }
        $this->cli_params = array();
        $this->cli_options = array();

        if( !array_key_exists( 'REMOTE_ADDR' , $this->conf->server ) ){
            $this->flg_cmd = true;
            if( is_array( $this->conf->server['argv'] ) && count( $this->conf->server['argv'] ) ){
                $tmp_path = null;
                for( $i = 0; count( $this->conf->server['argv'] ) > $i; $i ++ ){
                    if( preg_match( '/^\-/', $this->conf->server['argv'][$i] ) ){
                        $this->cli_params = array();
                        $this->cli_options[$this->conf->server['argv'][$i]] = $this->conf->server['argv'][$i+1];
                        $i ++;
                    }else{
                        array_push( $this->cli_params, $this->conf->server['argv'][$i] );
                    }
                }
                $tmp_path = @$this->cli_params[count($this->cli_params)-1];
                if( preg_match( '/^\//', $tmp_path ) && @is_array($this->conf->server['argv']) ){
                    $tmp_path = array_pop( $this->conf->server['argv'] );
                    $tmp_path = parse_url($tmp_path);
                    $this->request_file_path = $tmp_path['path'];
                    @parse_str( $tmp_path['query'], $query );
                    if( is_array($query) ){
                        $this->conf->get = array_merge( $this->conf->get, $query );
                    }
                }
                unset( $tmp_path );
            }
        }

        if( ini_get('magic_quotes_gpc') ){
            foreach( array_keys( $this->conf->get ) as $Line ){
                $this->conf->get[$Line] = self::stripslashes( $this->conf->get[$Line] );
            }
            foreach( array_keys( $this->conf->post ) as $Line ){
                $this->conf->post[$Line] = self::stripslashes( $this->conf->post[$Line] );
            }
        }

        $this->conf->get = self::convert_encoding( $this->conf->get );
        $this->conf->post = self::convert_encoding( $this->conf->post );
        $param = array_merge( $this->conf->get , $this->conf->post );
        $param = $this->normalize_input( $param );

        if( is_array( $this->conf->files ) ){
            $FILES_KEYS = array_keys( $this->conf->files );
            foreach($FILES_KEYS as $Line){
                $this->conf->files[$Line]['name'] = self::convert_encoding( $this->conf->files[$Line]['name'] );
                $this->conf->files[$Line]['name'] = mb_convert_kana( $this->conf->files[$Line]['name'] , 'KV' , mb_internal_encoding() );
                $param[$Line] = $this->conf->files[$Line];
            }
        }

        $this->param = $param;
        unset($param);

        if (preg_match('/\/$/', $this->request_file_path)) {
            $this->request_file_path .= $this->conf->directory_index_primary;
        }
        //$this->request_file_path = $this->fs->get_realpath( $this->request_file_path );
        //$this->request_file_path = $this->fs->normalize_path( $this->request_file_path );

        return	true;
    }//parse_input()

    private function normalize_input( $param ){
        $is_callable_mb_check_encoding = is_callable( 'mb_check_encoding' );
        foreach( $param as $key=>$val ){
            if( is_array( $val ) ){
                $param[$key] = $this->normalize_input( $param[$key] );
            }elseif( is_string( $param[$key] ) ){
                $param[$key] = mb_convert_kana( $param[$key] , 'KV' , mb_internal_encoding() );
                $param[$key] = preg_replace( '/\r\n|\r|\n/' , "\n" , $param[$key] );
                if( $is_callable_mb_check_encoding ){
                    if( !mb_check_encoding( $key , mb_internal_encoding() ) ){
                        unset( $param[$key] );
                    }
                    if( !mb_check_encoding( $param[$key] , mb_internal_encoding() ) ){
                        $param[$key] = false;
                    }
                }
            }
        }
        return $param;
    }//normalize_input()

    public function get_param( $key ){
        if( !array_key_exists($key, $this->param) ){ return null; }
        return @$this->param[$key];
    }//get_param()

    public function set_param( $key , $val ){
        $this->param[$key] = $val;
        return true;
    }//set_param()

    public function get_all_params(){
        return $this->param;
    }

    public function get_cli_option( $name ){
        return @$this->cli_options[$name];
    }

    public function get_cli_options(){
        return @$this->cli_options;
    }

    public function get_cli_param( $idx = 0 )
    {
        if ($idx < 0) {
            $idx = count($this->cli_params) + $idx;
        }
        return @$this->cli_params[$idx];
    }

    public function get_cli_params(){
        return @$this->cli_params;
    }

    // ----- cookies -----
    public function get_cookie( $key ){
        return	@$_COOKIE[$key];
    }//get_cookie()

    public function set_cookie( $key , $val , $expire = null , $path = null , $domain = null , $secure = false ){
        if( is_null( $path ) ){
            $path = $this->conf->cookie_default_path;
            if( !strlen( $path ) ){
                $path = $this->get_path_current_dir();
            }
            if( !strlen( $path ) ){
                $path = '/';
            }
        }
        if( !@setcookie( $key , $val , $expire , $path , $domain , $secure ) ){
            return false;
        }

        $_COOKIE[$key] = $val;
        return true;
    }//set_cookie()

    public function delete_cookie( $key ){
        if( !@setcookie( $key , null ) ){
            return false;
        }
        unset( $_COOKIE[$key] );
        return true;
    }//delete_cookie()



    // ----- session -----
    private function session_start( $sid = null ){
        $expire = intval($this->conf->session_expire);
        $cache_limiter = 'nocache';
        $session_name = 'SESSID';
        if( strlen( $this->conf->session_name ) ){
            $session_name = $this->conf->session_name;
        }
        $path = $this->conf->cookie_default_path;
        if( !strlen( $path ) ){
            $path = $this->get_path_current_dir();
        }
        if( !strlen( $path ) ){
            $path = '/';
        }

        session_name( $session_name );
        session_cache_limiter( $cache_limiter );
        session_cache_expire( intval($expire/60) );

        if( intval( ini_get( 'session.gc_maxlifetime' ) ) < $expire + 10 ){
            ini_set( 'session.gc_maxlifetime' , $expire + 10 );
        }

        session_set_cookie_params( 0 , $path );
        if( strlen( $sid ) ){
            session_id( $sid );
        }

        $rtn = @session_start();

        if( strlen( $this->get_session( 'SESSION_LAST_MODIFIED' ) ) && intval( $this->get_session( 'SESSION_LAST_MODIFIED' ) ) < intval( time() - $expire ) ){
            if( is_callable('session_regenerate_id') ){
                @session_regenerate_id( true );
            }
        }
        $this->set_session( 'SESSION_LAST_MODIFIED' , time() );
        return $rtn;
    }//session_start()

    public function get_session_id(){
        return session_id();
    }//get_session_id()

    public function get_session( $key ){
        if( @!is_array( $_SESSION ) ){ return null; }
        if( @!array_key_exists($key, $_SESSION) ){ return null; }
        return @$_SESSION[$key];
    }//get_session()

    public function set_session( $key , $val ){
        $_SESSION[$key] = $val;
        return true;
    }//set_session()

    public function delete_session( $key ){
        unset( $_SESSION[$key] );
        return true;
    }//delete_session()


    // ----- upload file access -----
    public function save_uploadfile( $key , $ulfileinfo ){
        $fileinfo = array();
        $fileinfo['name'] = $ulfileinfo['name'];
        $fileinfo['type'] = $ulfileinfo['type'];

        if( $ulfileinfo['content'] ){
            $fileinfo['content'] = base64_encode( $ulfileinfo['content'] );
        }else{
            $filepath = '';
            if( @is_file( $ulfileinfo['tmp_name'] ) ){
                $filepath = $ulfileinfo['tmp_name'];
            }elseif( @is_file( $ulfileinfo['path'] ) ){
                $filepath = $ulfileinfo['path'];
            }else{
                return false;
            }
            $fileinfo['content'] = base64_encode( file_get_contents( $filepath ) );
        }
        $_SESSION['FILE'][$key] = $fileinfo;
        return	true;
    }

    public function get_uploadfile( $key ){
        if(!strlen($key)){ return false; }

        $rtn = @$_SESSION['FILE'][$key];
        if( is_null( $rtn ) ){ return false; }

        $rtn['content'] = base64_decode( @$rtn['content'] );
        return	$rtn;
    }

    public function get_uploadfile_list(){
        return	array_keys( $_SESSION['FILE'] );
    }

    public function delete_uploadfile( $key ){
        unset( $_SESSION['FILE'][$key] );
        return	true;
    }

    public function delete_uploadfile_all(){
        return	$this->delete_session( 'FILE' );
    }


    // ----- utils -----

    public function get_user_agent(){
        return @$this->conf->server['HTTP_USER_AGENT'];
    }//get_user_agent()

    public function get_request_file_path(){
        return $this->request_file_path;
    }//get_request_file_path()

    public function is_ssl(){
        if( @$this->conf->server['HTTP_SSL'] || @$this->conf->server['HTTPS'] ){
            return true;
        }
        return false;
    }

    public function is_cmd(){
        if( array_key_exists( 'REMOTE_ADDR' , $this->conf->server ) ){
            return false;
        }
        return	true;
    }


    // ----- private -----
    private static function convert_encoding( $text, $encode = null, $encodefrom = null ){
        if( !is_callable( 'mb_internal_encoding' ) ){ return $text; }
        if( !strlen( $encodefrom ) ){ $encodefrom = mb_internal_encoding().',UTF-8,SJIS-win,eucJP-win,SJIS,EUC-JP,JIS,ASCII'; }
        if( !strlen( $encode ) ){ $encode = mb_internal_encoding(); }

        if( is_array( $text ) ){
            $rtn = array();
            if( !count( $text ) ){ return $text; }
            $TEXT_KEYS = array_keys( $text );
            foreach( $TEXT_KEYS as $Line ){
                $KEY = mb_convert_encoding( $Line , $encode , $encodefrom );
                if( is_array( $text[$Line] ) ){
                    $rtn[$KEY] = self::convert_encoding( $text[$Line] , $encode , $encodefrom );
                }else{
                    $rtn[$KEY] = @mb_convert_encoding( $text[$Line] , $encode , $encodefrom );
                }
            }
        }else{
            if( !strlen( $text ) ){ return $text; }
            $rtn = @mb_convert_encoding( $text , $encode , $encodefrom );
        }
        return $rtn;
    }

    private static function stripslashes( $text ){
        if( is_array( $text ) ){
            // 配列なら
            foreach( $text as $key=>$val ){
                $text[$key] = self::stripslashes( $val );
            }
        }elseif( is_string( $text ) ){
            // 文字列なら
            $text = stripslashes( $text );
        }
        return	$text;
    }

    private function get_path_current_dir(){
        $rtn = dirname( $this->conf->server['SCRIPT_NAME'] );
        if( !array_key_exists( 'REMOTE_ADDR' , $this->conf->server ) ){
            $rtn = '/';
        }
        $rtn = str_replace('\\','/',$rtn);
        $rtn .= ($rtn!='/'?'/':'');
        return $rtn;
    }//get_path_current_dir()

}
