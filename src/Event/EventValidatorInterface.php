<?php
declare(strict_types=1);

namespace Iit\HyLib\Contracts;

interface EventValidatorInterface
{
    /**
     * @return array
     */

    public function rules();

    /**
     * @return array
     */

    public function messages();
}
