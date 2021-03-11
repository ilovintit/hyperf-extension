<?php
declare(strict_types=1);

namespace Iit\HyLib\Model;

use Iit\HyLib\Exceptions\CustomException;

/**
 * Class GenerateCodeException
 * @package Iit\HyLib\Model
 */
class GenerateCodeException extends CustomException
{
    /**
     * GenerateCodeException constructor.
     * @param $message
     * @param array $data
     * @param array $headers
     * @param array $debug
     */
    public function __construct($message, $data = [], $headers = [], $debug = [])
    {
        parent::__construct($message, 1, 500, $data, $headers, $debug);
    }
}
