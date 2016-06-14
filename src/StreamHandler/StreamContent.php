<?php
namespace Tvart\Libs\StreamHandler;
class StreamContent
{
    private function __construct() {
        $this->context[$this->wrapper] = [
            "protocol_version" => $this->protocol_version,
            "method" => $this->method,
            "proxy" => $this->proxy,
            "header" => $this->header,
            "user_agent" => $this->user_agent,
            "ignore_errors" => $this->ignore_errors,
            "content" => http_build_query($this->content)
        ];
    }

    protected static $_instance = null;

    /**
     * @var \Memcache $cache
     */
    protected $memcache = null;
    protected $context = [];
    protected $wrapper = "http";
    protected $protocol_version = 1.1;
    protected $method = "GET";
    private $allowed_methods = ["HEAD","GET", "POST"];
    protected $user_agent = "Mozilla/5.0 (X11; Linux x86_64; rv:33.0) Gecko/20100101 Firefox/33.0";
    protected $ignore_errors = false;
    protected $proxy = null;
    protected $request_fulluri = false;
    protected $max_redirects = 0;
    protected $timeout = 20;
    protected $content = [];
    protected $header = [
        'accept_language:fr,fr-fr;q=0.8,en-us;q=0.5,en;q=0.3',
        'connection: close'
    ];


    public static function getContext(){
        if(is_null(self::$_instance)){
            self::$_instance = new StreamContent();
        }
        return self::$_instance;
    }

    public function checkContext(){
        return $this->context;
    }

    public function getContent($url, $mc = false) {
        if($mc) {
            $mc_key = md5($url);
            $content = $this->getMemcache($mc_key);
            if(empty($content)){
                $content = file_get_contents($url, false, stream_context_create($this->context));
                $this->setMemcache($mc_key,$content);
            }
        }else{
            $content = file_get_contents($url, false, stream_context_create($this->context));
        }

        return str_replace('&nbsp;', ' ', $content);
    }

    public function initMemcache(\Memcache $cache){
        $this->memcache = $cache;
    }

    public function getMemcache($mc_key){
        return $this->memcache->get($mc_key);
    }

    public function setMemcache($mc_key,$content){
        $this->memcache->set($mc_key,$content);
        return $this;
    }

    public function setWrapper($wrapper){
        $context = $this->context[$this->wrapper];
        $this->wrapper = $wrapper;
        $this->context = [
            $this->wrapper => $context
        ];
        return $this;
    }

    public function setProtocolVersion($protocol_version){
        $this->protocol_version = $protocol_version;
        $this->context[$this->wrapper]["protocol_version"] = $protocol_version;
        return $this;
    }

    public function setQuery($content){
        $this->content = $content;
        $this->context[$this->wrapper]["content"] = http_build_query($this->content);
        return $this;
    }

    public function setAuth($username, $password){
        $this->header[] = "Authorization: Basic ".base64_encode("$username:$password");
        $this->context[$this->wrapper]["header"] = $this->header;
        return $this;
    }

    public function setHeaders(array $headers){
        if(!empty($headers)){
            foreach($headers as $header){
                $this->header[] = $header;
            }
        }
        $this->context[$this->wrapper]["header"] = $this->header;
        return $this;
    }

    public function setProxy($host, $port){
        $this->proxy = sprintf("%s:%s",$host,$port);
        $this->request_fulluri = true;
        $this->context[$this->wrapper]["proxy"] = $this->proxy;
        $this->context[$this->wrapper]["request_fulluri"] = $this->request_fulluri;
        return $this;
    }


    public function setMaxRedirects($max_redirects){
        $this->max_redirects = $max_redirects;
        $this->context[$this->wrapper]["max_redirects"] = $max_redirects;
        return $this;
    }


    public function setTimeout($timeout){
        $this->timeout = $timeout;
        $this->context[$this->wrapper]["timeout"] = $timeout;
        return $this;
    }

    public function setMethod($method){
        if(in_array($method,$this->allowed_methods)){
            $this->method = $method;
            $this->context[$this->wrapper]["method"] = $method;
        }
        return $this;
    }

    public function setUserAgent($user_agent){
        $this->user_agent = $user_agent;
        $this->context[$this->wrapper]["user_agent"] = $user_agent;
        return $this;
    }

    public function setIgnoreErrors($bool){
        $this->ignore_errors = $bool;
        $this->context[$this->wrapper]["ignore_errors"] = $bool;
        return $this;
    }

    public function setHeader($header){
        $this->context[$this->wrapper]["header"] = $this->header;
        $this->header = $header;
        return $this;
    }
}