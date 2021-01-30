<?php
declare(strict_types=1);

namespace App\Extension\Traits;

use Symfony\Component\HttpFoundation\HeaderBag;

trait HeaderToBag
{
    /**
     * @var HeaderBag
     */

    public $headers;

    /**
     * @param array $headers
     */
    public function headerToBag($headers = [])
    {
        $this->headers = new HeaderBag($headers);
    }
}
