<?php
declare(strict_types=1);

namespace Iit\HyLib\Contracts;

/**
 * Class ValidatorEvent
 * @package Iit\HyLib\Contracts
 */
abstract class ValidatorEvent extends AbstractEvent implements EventValidatorInterface
{
    use EventValidatorTrait;
}
