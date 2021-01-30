<?php
declare(strict_types=1);

namespace Iit\HyLib\Event;

use Symfony\Component\HttpFoundation\HeaderBag;

trait HeaderToBag
{
    /**
     * @var HeaderBag
     */
    public HeaderBag $headers;

    /**
     * @param array $headers
     */
    public function headerToBag($headers = [])
    {
        $this->headers = new HeaderBag($headers);
    }
}
