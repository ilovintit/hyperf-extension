<?php
declare(strict_types=1);

namespace Iit\HyLib\Event;

use Hyperf\Contract\ValidatorInterface;
use Hyperf\Utils\ApplicationContext;
use Hyperf\Validation\Contract\ValidatorFactoryInterface;
use Hyperf\Validation\ValidationException;
use Iit\HyLib\Utils\Arr;

trait EventValidatorTrait
{
    /**
     * @var ValidatorInterface
     */
    public ValidatorInterface $validator;

    /**
     * @var array
     */
    public array $input;

    /**
     * @param array $input
     * @throws ValidationException
     */

    public function validateInput(array $input)
    {
        $this->input = Arr::toRealType($input);
        $this->validator = ApplicationContext::getContainer()
            ->get(ValidatorFactoryInterface::class)
            ->make($this->input, $this->rules(), $this->messages());
        $this->sometimeRules($this->validator);
        $this->validator->validate();
    }

    protected function sometimeRules(ValidatorInterface $validator)
    {
    }
}
