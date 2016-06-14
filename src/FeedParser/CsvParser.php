<?php

namespace Tvart\Libs\FeedParser;
class CsvParser
{
    /**
     * @var \SplFileObject
     */
    protected $file = "";

    /**
     * @var resource
     */
    protected $hanlde = false;

    private $fgetcsv = false;

    /**
     * @var string
     */
    protected $delim = ";";

    /**
     * @var string
     */
    protected $enclosure = '"';

    /**
     * @var bool
     */
    protected $debug = false;

    /**
     * @var string
     */
    protected $encoding = "Windows-1252,ISO-8859-15";

    /**
     * @param $file
     */
    public function __construct($file=false)
    {
        if($file){
            $this->setFile($file);
        }
    }

    /**
     * @param \Closure $callback
     */
    public function parse(\Closure $callback)
    {
        if($this->hanlde){
            if($this->fgetcsv){
                $this->parseAsCsv($callback);
            }else{
                $this->parseHandle($callback);
            }
        }else{
            $this->parseFile($callback);
        }
    }

    /**
     * @param \Closure $callback
     */
    protected function parseFile(\Closure $callback){
        while(!$this->file->eof()) {
            if(strlen($this->delim) == 1) {
                $data = $this->file->fgetcsv($this->delim, $this->enclosure);
            } else {
                $data = explode(
                    $this->delim,
                    $this->file->fgets()
                );

                $data = array_map(function($row){
                    return mb_convert_encoding(trim($row,$this->enclosure),"UTF-8","Windows-1252,ISO-8859-15");
                },$data);

                if($this->debug){break;}
                /*
                 $enclosure = $this->enclosure;
                 array_walk($data, function(&$val) use ($enclosure) {
                    return trim($val, $enclosure);
                });
                */
            }
            $callback($data);
        }
    }

    /**
     * @param \Closure $callback
     */
    protected function parseHandle(\Closure $callback){
        while(($line = fgets($this->hanlde)) !== false)
        {
            $data = explode(
                $this->delim,
                $line
            );

            $data = array_map(function($row){
                return mb_convert_encoding(trim($row,$this->enclosure),"UTF-8",$this->encoding);
            },$data);

            $callback($data);
            if($this->debug){break;}
        }
    }

    /**
     * @param \Closure $callback
     */
    protected function parseAsCsv(\Closure $callback){
        while(($data = fgetcsv($this->hanlde,0,$this->delim,$this->enclosure)) !== false)
        {
            $data = array_map(function($row){
                return mb_convert_encoding($row,"UTF-8",$this->encoding);
            },$data);

            $callback($data);
            if($this->debug){break;}
        }
    }

    public function setEncoding($encoding)
    {
        $this->encoding = $encoding;
        return $this;
    }

    public function setFgetcsv($flag){
        $this->fgetcsv = $flag;
        return $this;
    }
    /**
     * @param bool $flag
     * @return $this
     */
    public function setDebug($flag){

        $this->debug = $flag;
        return $this;
    }

    /**
     * @param $file
     * @return $this
     */
    public function setFile($file)
    {
        $this->file = new \SplFileObject($file, "r");
        if(!$this->file->isFile()) {
            throw new \Exception(sprintf("Invalid file for parsing %s", $file));
        }

        return $this;
    }

    /**
     * @param $file
     * @return $this
     */
    public function setHandle($file)
    {
        $this->hanlde = fopen($file, 'r');
        if($this->hanlde == false) {
            throw new \Exception(sprintf("Unable to handle file %s", $file));
        }

        return $this;
    }

    /**
     * @param $delim
     * @return $this
     * @throws \Exception
     */
    public function setDelim($delim)
    {
        if (0 === strlen($delim)) {
            throw new \Exception("Delimiter can't be empty");
        }
        $this->delim = $delim;

        return $this;
    }

    /**
     * @param $enclosure
     * @return $this
     */
    public function setEnclosure($enclosure)
    {
        $this->enclosure = $enclosure;

        return $this;
    }
}