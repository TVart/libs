<?php
/**
 * Created by PhpStorm.
 * User: Vartan
 * Date: 27/03/15
 * Time: 17:05
 */

namespace Tvart\Libs\FtpHandler;

/**
 * Class FtpHandler
 * @package Tvart
 */
class FtpHandler extends AbstractFtpHandler
{
    /**
     * @param bool|false $file
     * @return mixed
     */
    private $file;

    private $strategie="php";

    public function get($file=false){
        $this->file = $file;
        $method = "{$this->strategie}Ftp";
        $return = $this->$method();
        return $return;
    }

    public function put($localFile,$remoteFile=false){
        $this->file = $remoteFile;
        $method = "{$this->strategie}FtpPut";
        $return = $this->$method($localFile);
        return $return;
    }

    public function using($strategie){
        $this->strategie = $strategie;
        return $this;
    }

    public function getFile(){
        return $this->file;
    }

    private function phpFtpPut($localFile){
        try {
            if($this->logger){
                $this->logger->debug('Try connect to host...');
            }
            $conn = ftp_connect($this->ftp_host);
            if (!$conn) {
                throw new \Exception("Can not connect to $conn");
            }
            if($this->logger){
                $this->logger->debug('Try loggin into host...');
            }
            $login = ftp_login($conn, $this->ftp_login, $this->getFtpPass());

            if (!$login) {
                if (!$conn) {
                    throw new \Exception("Can not login into $conn with as {$this->ftp_login}");
                }
            }

            if($this->passive_ftp){
                if($this->logger){
                    $this->logger->debug('Setup Passive FTP...');
                }
                $p=ftp_pasv($conn,true);
                if (!$p) {
                    throw new \Exception("Can not swith to passive mode");
                }
            }

            if ($this->ftp_folder !== '.') {
                ftp_chdir($conn, $this->ftp_folder);
            }

            if(!$this->file){
                $this->file = basename($localFile);
            }

            if($this->logger){
                $this->logger->debug('Try to get remote file...');
            }

            if (!ftp_put($conn, $this->file, $localFile, FTP_BINARY)){
                ftp_close($conn);
                throw new \Exception("Can not donwload file {$this->file}");
            }
            ftp_close($conn);
            return realpath($this->file);
        }
        catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    private function phpFtp(){
        try {
            if($this->logger){
                $this->logger->debug('Try connect to host...');
            }
            $conn = ftp_connect($this->ftp_host);
            if (!$conn) {
                throw new \Exception("Can not connect to $conn");
            }
            if($this->logger){
                $this->logger->debug('Try loggin into host...');
            }
            $login = ftp_login($conn, $this->ftp_login, $this->getFtpPass());

            if (!$login) {
                if (!$conn) {
                    throw new \Exception("Can not login into $conn with as {$this->ftp_login}");
                }
            }

            if($this->passive_ftp){
                if($this->logger){
                    $this->logger->debug('Setup Passive FTP...');
                }
                $p=ftp_pasv($conn,true);
                if (!$p) {
                    throw new \Exception("Can not swith to passive mode");
                }
            }

            if ($this->ftp_folder !== '.') {
                ftp_chdir($conn, $this->ftp_folder);
            }

            if($this->file){
                $remote_file = $this->file;
            }else{
                if($this->logger){
                    $this->logger->debug('Try to get available files...');
                }
                $list_contents = ftp_nlist($conn, ".");
                //dd($list_contents);exit;
                sort($list_contents);
                if($this->inspect_ftp){
                    ftp_close($conn);
                    return $list_contents;
                }
                $remote_file = array_pop($list_contents);
                $this->file = basename($remote_file);
                //dd($this->file);
            }
            if($this->logger){
                $this->logger->debug('Try to get remote file...');
            }
            $get = ftp_get($conn, $this->file, $remote_file, FTP_BINARY);
            if (!$get){
                ftp_close($conn);
                throw new \Exception("Can not donwload file {$this->file}");
            }
            ftp_close($conn);
            $this->file = realpath($this->file);
            return $this->file;
        }
        catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    private function curlFtpPut($localfile){
        try {
            if($this->logger){
                $this->logger->debug('Try connect to host...');
            }

            if(!$this->file){
                $this->file = basename($localfile);
            }

            $url = "ftp://{$this->ftp_host}/";
            if ($this->ftp_folder !== '.') {
                $url .= "{$this->ftp_folder}/";
            }

            $url .= $this->file;
            $curl = curl_init();
            $fp = fopen($localfile, 'r');
            if($this->passive_ftp){
                curl_setopt($curl, CURLOPT_FTP_SKIP_PASV_IP, 1 );
            }
            //curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1) ;
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_USERPWD, "{$this->ftp_login}:{$this->ftp_pass}");
            curl_setopt($curl, CURLOPT_UPLOAD, 1);
            curl_setopt($curl, CURLOPT_INFILE, $fp);
            curl_setopt($curl, CURLOPT_INFILESIZE, filesize($localfile));
            curl_exec ($curl);
            $curl_errno = curl_errno($curl);
            $curl_error = curl_error($curl);
            curl_close($curl);
            fclose($fp);
            if ($curl_errno > 0) {
                throw new \Exception("cURL Error ($curl_errno): $curl_error");
            }
            return true;
        }
        catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    private function curlFtp(){
        try {
            if($this->logger){
                $this->logger->debug('Try connect to host...');
            }
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, "ftp://{$this->ftp_host}");
            curl_setopt($curl, CURLOPT_USERPWD, "{$this->ftp_login}:{$this->ftp_pass}");
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1) ;

            if($this->passive_ftp){
                curl_setopt($curl, CURLOPT_FTP_SKIP_PASV_IP, 1 );
            }

            if ($this->ftp_folder !== '.') {
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "CWD /{$this->ftp_folder}");
            }

            if($this->file){
                $url = "ftp://{$this->ftp_host}";
                if($this->ftp_folder !=='.'){
                    $url .= "/$this->ftp_folder/";
                }
                $url .= $this->file;
            }else{
                if($this->logger){
                    $this->logger->debug('Try to get available files...');
                }
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'MLSD'); // get directory list
                curl_setopt($curl, CURLOPT_TIMEOUT_MS, 5000);
                $data = curl_exec($curl);
                $curl_errno = curl_errno($curl);
                $curl_error = curl_error($curl);
                if ($curl_errno > 0) {
                    curl_close($curl);
                    throw new \Exception("cURL Error ($curl_errno): $curl_error");
                }

                $list_contents=[];
                $data = str_getcsv($data, "\n");
                foreach($data as $row){
                    $row = str_getcsv($row, ";");
                    $list_contents[]=trim(array_pop($row));
                }
                sort($list_contents);
                if($this->inspect_ftp){
                    curl_close($curl);
                    return $list_contents;
                }
                $remote_file = trim(array_pop($list_contents));
                $url = "ftp://{$this->ftp_host}";
                if($this->ftp_folder !=='.'){
                    $url .= "/$this->ftp_folder/";
                }
                $url .= $this->remote_file;
                $this->file = basename($remote_file);
            }
            if($this->logger){
                $this->logger->debug('Try to get remote file...');
            }
            $file = fopen($this->file, 'w');
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_FILE, $file);
            curl_exec($curl);
            $curl_errno = curl_errno($curl);
            $curl_error = curl_error($curl);
            curl_close($curl);
            fclose($file);
            if ($curl_errno > 0) {
                throw new \Exception("cURL Error ($curl_errno): $curl_error");

            }
            $this->file = realpath($this->file);
            return $this->file;
        }
        catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }
}