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
abstract class AbstractFtpHandler
{
    /**
     * @var Logger $logger
     */
    protected $logger;

    /**
     * @var string $ftp_host
     */
    protected $ftp_host;

    /**
     * @var string $ftp_login
     */
    protected $ftp_login;

    /**
     * @var string $ftp_pass
     */
    protected $ftp_pass;

    /**
     * @var string $ftp_folder
     */
    protected $ftp_folder;

    /**
     * @var Bool $passive_ftp
     */
    protected $passive_ftp;

    /**
     * @var Bool $inspect_ftp
     */
    protected $inspect_ftp;

    /**
     * @var string $remote_file
     */
    protected $remote_file;


    /**
     * @param string $host
     * @param string $login
     * @param string $password
     * @param string $folder
     */
    public function __construct($host,$login,$password,$folder='.'){
        $this->ftp_host = $host;
        $this->ftp_login = $login;
        $this->ftp_pass = $password;
        $this->ftp_folder = $folder;
    }

    /**
     * @return string
     */
    public function getRemoteFile() {
        return $this->remote_file;
    }

    /**
     * @param string $remote_file
     * @return $this
     */
    public function setRemoteFile($remote_file) {
        $this->remote_file = $remote_file;
        return $this;
    }

    /**
     * @return Logger
     */
    public function getLogger() {
        return $this->logger;
    }

    /**
     * @param Logger $logger
     * @return $this
     */
    public function setLogger($logger) {
        $this->logger = $logger;
        return $this;
    }

    /**
     * @return string
     */
    public function getFtpHost() {
        return $this->ftp_host;
    }

    /**
     * @param string $ftp_host
     * @return $this
     */
    public function setFtpHost($ftp_host) {
        $this->ftp_host = $ftp_host;
        return $this;
    }

    /**
     * @return string
     */
    public function getFtpLogin() {
        return $this->ftp_login;
    }

    /**
     * @param string $ftp_login
     * @return $this
     */
    public function setFtpLogin($ftp_login) {
        $this->ftp_login = $ftp_login;
        return $this;
    }

    /**
     * @return string
     */
    public function getFtpPass() {
        return $this->ftp_pass;
    }

    /**
     * @param string $ftp_pass
     * @return $this
     */
    public function setFtpPass($ftp_pass) {
        $this->ftp_pass = $ftp_pass;
        return $this;
    }

    /**
     * @return string
     */
    public function getFtpFolder() {
        return $this->ftp_folder;
    }

    /**
     * @param string $ftp_folder
     * @return $this
     */
    public function setFtpFolder($ftp_folder) {
        $this->ftp_folder = $ftp_folder;
        return $this;
    }

    /**
     * @return Bool
     */
    public function getPassiveFtp() {
        return $this->passive_ftp;
    }

    /**
     * @param Bool $passive_ftp
     * @return $this
     */
    public function setPassiveFtp($passive_ftp) {
        $this->passive_ftp = $passive_ftp;
        return $this;
    }

    /**
     * @return Bool
     */
    public function getInspectFtp() {
        return $this->inspect_ftp;
    }

    /**
     * @param Bool $inspect_ftp
     * @return $this
     */
    public function setInspectFtp($inspect_ftp) {
        $this->inspect_ftp = $inspect_ftp;
        return $this;
    }

    /**
     * @param bool|false $file
     * @return mixed
     */
    abstract function get($file=false);

    /**
     * @param false $file
     * @return mixed
     */
    abstract function put($localFile,$remoteFile);
}