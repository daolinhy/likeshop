<?php

namespace app\common\server\storage\engine;

use Aws\Credentials\Credentials;
use Aws\S3\S3Client;
/**
 * 腾讯云存储引擎 (COS)
 * Class Qiniu
 * @package app\common\library\storage\engine
 */
class Cloudflare extends Server
{
    private $config;
    private $cosClient;

    /**
     * 构造方法
     * Qcloud constructor.
     * @param $config
     */
    public function __construct($config)
    {
        parent::__construct();
        $this->config = $config;
        // 创建COS控制类
        $this->createCosClient();
    }

    /**
     * 创建COS控制类
     */
    private function createCosClient()
    {
                
        $credentials = new Credentials($this->config['secret_id'], $this->config['secret_key']);

        $options = [
            'region' => $this->config['region'],
            'endpoint' => $this->config['endpoint'],
            'version' => 'latest',
            'request_checksum_calculation' => 'when_required',
            'response_checksum_validation' => 'when_required',
            'suppress_php_deprecation_warning' => true,
            'credentials' => $credentials
        ];
        $this->cosClient = new S3Client($options);
    }

    /**
     * 执行上传
     * @param $save_dir (保存路径)
     * @return bool|mixed
     */
    public function upload($save_dir)
    {
        // 上传文件
        // putObject(上传接口，最大支持上传5G文件)
        try {
            $result = $this->cosClient->putObject([
                'Bucket' => $this->config['bucket'],
                'Key' => $save_dir . '/' . $this->fileName,
                'Body' => fopen($this->getRealPath(), 'rb')
            ]);
            return true;
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }

    /**
     * Notes: 抓取远程资源
     * @param $url
     * @param null $key
     * @author 张无忌(2021/3/2 14:36)
     * @return mixed|void
     */
    public function fetch($url, $key=null) {
        // putObject(上传接口，最大支持上传5G文件)
        try {
            $result = $this->cosClient->putObject([
                'Bucket' => $this->config['bucket'],
                'Key'    => $key,
                'Body'   => fopen($url, 'rb')
            ]);
            return true;
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }

    /**
     * 删除文件
     * @param $fileName
     * @return bool|mixed
     */
    public function delete($fileName)
    {
        try {
            $result = $this->cosClient->deleteObject(array(
                'Bucket' => $this->config['bucket'],
                'Key' => $fileName
            ));
            return true;
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }

    /**
     * 返回文件路径
     * @return mixed
     */
    public function getFileName()
    {
        return $this->fileName;
    }

}
