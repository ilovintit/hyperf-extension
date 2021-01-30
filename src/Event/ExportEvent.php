<?php
declare(strict_types=1);

namespace Iit\HyLib\Event;

use Iit\HyLib\Utils\Export;

/**
 * Class ExportEvent
 * @package Iit\HyLib\Contracts
 */
abstract class ExportEvent extends ListEvent
{
    /**
     * @var array
     */
    public $xCodes = [];

    /**
     * @var Export
     */
    public Export $export;

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
    abstract public function headers(): array;

    /**
     * @return string
     */

    public function successMessage(): string
    {
        return '导出成功';
    }

    /**
     * @return string
     */

    public function failedMessage(): string
    {
        return '导出失败';
    }
}
