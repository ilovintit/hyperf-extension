<?php
declare(strict_types=1);

namespace Iit\HyLib\Utils;

use Exception;
use Iit\HyLib\Exceptions\CustomException;
use Vtiful\Kernel\Excel;

class Export
{
    /**
     * @var Excel
     */
    public Excel $excel;

    /**
     * @var Excel
     */
    public Excel $excelFile;

    /**
     * @var string
     */
    protected string $filename;

    /**
     * @var array
     */
    protected array $headers;

    /**
     * @param $filename
     * @param array $headers
     * @return static
     */
    public static function create($filename = null, array $headers = []): Export
    {
        return new static($filename, $headers);
    }

    /**
     * Export constructor.
     * @param $filename
     * @param array $headers
     */
    public function __construct($filename = null, array $headers = [])
    {
        $this->filename = (empty($filename) ? H::uuid() : $filename) . '.xlsx';
        $this->headers = $headers;
        $this->excel = new Excel(['path' => BASE_PATH . DIRECTORY_SEPARATOR . 'runtime']);
        $this->excelFile = $this->excel->constMemory($this->filename);
        if (!empty($this->headers)) {
            $this->excelFile = $this->excelFile->header($this->headers);
        }
    }

    /**
     * @param array $data
     * @return $this
     */
    public function setData(array $data): Export
    {
        $this->excelFile = $this->excelFile->data($data);
        return $this;
    }

    /**
     * @return string
     */
    public function output(): string
    {
        return $this->excelFile->output();
    }

    /**
     * @return string
     */
    public function upload(): string
    {
        if (!$saveName = $this->output()) {
            throw new CustomException('生成Excel文件失败');
        }
        try {
            $prefix = 'export/';
            $storage = File::oss();
            if (!$storage->write($prefix . $this->filename, file_get_contents($saveName))) {
                file_exists($saveName) && unlink($saveName);
                throw new CustomException('上传Excel到OSS失败');
            }
            file_exists($saveName) && unlink($saveName);
            return File::ossAdapter()->getObjectUrl($this->filename, $prefix, false);
        } catch (Exception $exception) {
            file_exists($saveName) && unlink($saveName);
            throw new CustomException($exception->getMessage());
        }

    }
}
