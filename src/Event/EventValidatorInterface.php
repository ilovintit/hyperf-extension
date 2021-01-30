<?php
declare(strict_types=1);

namespace Iit\HyLib\Event;

/**
 * Interface EventValidatorInterface
 * @package Iit\HyLib\Contracts
 */
interface EventValidatorInterface
{
    /**
     * @return array
     */
    public function rules(): array;

    /**
     * @return array
     */
    public function messages(): array;
}
