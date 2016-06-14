<?php
namespace Tvart\Libs\FeedParser;

use Monolog\Logger;

class XmlParser
{
    /**
     * @var \XMLReader
     */
    protected $xmlr = "";

    /**
     * @var string
     */
    protected $file = null;

    /**
     * @var string
     */
    protected $root = "";

    /**
     * @var string
     */
    protected $node = "";

    /**
     * @var bool
     */
    protected $debug = false;

    /**
     * @var bool
     */
    protected $trace = false;

    /**
     * @var bool
     */
    protected $strict = false;

    /**
     * @var FtpHandler $ftp
     */
    protected $ftp = false;

    /**
     * @var bool|array
     */
    protected $passive_ftp = false;

    /**
     * @var bool|array
     */
    protected $inspect_ftp = false;

    /**
     * @var Logger
     */
    protected $logger = null;

    /**
     * @param $file
     */
    public function __construct()
    {
        $this->xmlr = new \XMLReader();
        //$this->setFile($file);
    }

    public function __destruct(){
        if($this->xmlr){
            $this->xmlr->close();
        }
    }
    /**
     * @param \Closure $callback
     */
    public function parse(\Closure $callback)
    {
        while($this->xmlr->read() && $this->xmlr->localName !== $this->node);
        $this->parseFile($callback);
    }


    /**
     * @param \Closure $callback
     */
    protected function parseFile(\Closure $callback){
        while($this->xmlr->localName == $this->node) {
            try{
                $sxe = new \SimpleXMLElement($this->xmlr->readOuterXml());
                if(!$sxe instanceof \SimpleXMLElement){
                    throw new \Exception("node is note SimpleXMLElement");
                }
                $callback($sxe);
            }
            catch (\RuntimeException $e){
                throw new \RuntimeException($e->getMessage());
            }
            catch (\Exception $e){
                if($this->trace){
                    echo sprintf(
                        "%s - %s - %s -%s\n",
                        $e->getMessage(),
                        $e->getFile(),
                        $e->getLine(),
                        $e->getTraceAsString()
                    );
                }
                if($this->strict){
                    throw new \RuntimeException($e->getMessage());
                }
            }
            if($this->debug){break;}
            $this->xmlr->next($this->node);
        }
        $this->xmlr->close();
    }

    /**
     * @param bool $flag
     * @return $this
     */
    public function setDebug($flag){

        $this->debug = $flag;
        return $this;
    }

    public function setLogger(Logger $logger){
        $this->logger = $logger;
        return $this;
    }
    /**
     * @param bool $flag
     * @return $this
     */
    public function setTrace($flag){
        $this->trace = $flag;
        return $this;
    }

    /**
     * @param bool $flag
     * @return $this
     */
    public function setStrict($flag){
        $this->strict = $flag;
        return $this;
    }

    /**
     * @param string $root
     * @return $this
     */
    public function setRoot($root){
        $this->root = $root;
        return $this;
    }

    /**
     * @param $node
     * @return $this
     */
    public function setNode($node){
        $this->node = $node;
        return $this;
    }


    public function getFile(){
        return $this->file;
    }

    public function getFtp(){
        return $this->ftp;
    }
    /**
     * @param $file
     * @return $this
     */
    public function setFile($file, \Closure $callback=null)
    {
        if($this->ftp){
            $file=$this->getFileFromFtp($file);
            $this->file = $this->ftp->getFile();
        }

        libxml_clear_errors();
        libxml_use_internal_errors(true);
        if(!is_null($callback)){
            $callback($file);
        }
        $this->xmlr->open($file);
        $errors = libxml_get_errors();
        if(!empty($errors)) {
            $last_error = libxml_get_last_error();
            throw new \RuntimeException(sprintf("%s %s", $last_error->message,$file));
        }
        libxml_clear_errors();
        return $this;
    }

    public function clearFtp(){
        //unset($this->ftp);
        $this->ftp=false;
        return $this;
    }

    public function setPassiveFtp($bool){
        $this->passive_ftp=$bool;
        return $this;
    }

    public function setInspectFtp($bool){
        $this->inspect_ftp=$bool;
        return $this;
    }

    public function setFtp($host,$login,$password,$folder='.'){
        $this->ftp = new FtpHandler($host,$login,$password,$folder);
        return $this;
    }

    /**
     * @param bool|false $file
     * @param string     $strategie
     * @return mixed
     * @throws Exception
     */
    public function getFileFromFtp($file=false,$strategie="php") {
        try {
            if($this->logger){
                $this->ftp->setLogger($this->logger);
            }
            if($this->passive_ftp){
                $this->ftp->setPassiveFtp($this->passive_ftp);
            }
            if($this->passive_ftp){
                $this->ftp->setInspectFtp($this->inspect_ftp);
            }
            $return = $this->ftp->using($strategie)->get($file);
            return $return;
        } catch (\Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
}