<?php

namespace Tvart\Libs\StreamHandler;

class Fsockopen {
    private $errno=null;
    private $errstr=null;
    private $fp = false;
    private $host="localhost";
    private $ressource="/";
    private $protocol="HTTP/1.0";
    private $timeout=5;
    private $port=80;
    private $user_agent="Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)";
    private $opts = [];
    private $filable = [
        "host",
        "ressource",
        "port",
        "timeout",
        "protocol"
    ];

    public function __construct(){

    }

    public function open($opts = []){
        try{
            if(!empty($opts) && empty($this->opts)){
                $this->setOpts($opts);
            }
            $this->fp = fsockopen($this->host, $this->port, $this->errno, $this->errstr, $this->timeout);
            if (!$this->fp) {
                throw new \Exception("$this->errstr ($this->errno)");
            }
        }catch (\Exception $e){
            echo $e->getMessage();
        }

    }

    public function setOpts($opts){
        try{
            foreach($this->$opts as $field => $value){
                if(isset($this->filable[$field])){
                    $this->$field = $value;
                }else{
                    $this->opts[$field] = $value;
                }
            }
        }catch (\Exception $e){
            echo "Exception : ".$e->getMessage();
        }
    }

    public function getQuery(){
        fputs($this->fp, "GET ".$this->ressource." ".$this->protocol."\r\n");
        fputs($this->fp, "User-Agent: {$this->user_agent}\r\n");
        fputs($this->fp, "Host: {$this->host}\r\n");
        foreach($this->opts as $key => $val){
            fputs($this->fp, ucfirst($key).": $val\r\n");
        }
        fputs($this->fp, "\r\n\r\n");

        header('Content-type: text/plain');
        while (!feof($this->fp)) {
            echo fgets($this->fp, 1024);
        }
    }

    public function postQuery($postdata=[]){
        $content = http_build_query($postdata);
        fwrite($this->fp, "POST {$this->ressource} {$this->protocol}\r\n");
        fwrite($this->fp, "Host: {$this->host}\r\n");
        fwrite($this->fp, "Content-Type: application/x-www-form-urlencoded\r\n");
        fwrite($this->fp, "Content-Length: ".strlen($content)."\r\n");
        foreach($this->opts as $key => $val){
            fwrite($this->fp, ucfirst($key).": $val\r\n");
        }
        fwrite($this->fp, "Connection: close\r\n\r\n");
        fwrite($this->fp, $content);

        header('Content-type: text/plain');
        while (!feof($this->fp)) {
            echo fgets($this->fp, 1024);
        }
    }
}