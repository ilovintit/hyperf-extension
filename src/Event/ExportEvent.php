<?php
declare(strict_types=1);

namespace Iit\HyLib\Contracts;

use App\Utils\Export;

abstract class ExportEvent extends ListEvent
{
    /**
     * @var array
     */
    public $xCodes = [];

    /**
     * @var Export
     */
    public $export;

    /**
     * ExportEvent constructor.
     * @param $headers
     */
    public function __construct($headers)
    {
        parent::__construct($headers);
        $this->export = Export::create(null, $this->headers());
        $xCode = $this->headers->get('X-Select-Codes', null);
        if (!empty($xCode)) {
            $xCode = json_decode(base64_decode($xCode), true);
        }
        if (!empty($xCode)) {
            $this->xCodes = $xCode;
        }
    }

    /**
     * @return array
     */
    abstract public function headers();

    /**
     * @return string
     */

    public function successMessage()
    {
        return '导出成功';
    }

    /**
     * @return string
     */

    public function failedMessage()
    {
        return '导出失败';
    }
}
