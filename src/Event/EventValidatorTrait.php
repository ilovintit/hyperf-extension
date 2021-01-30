<?php
declare(strict_types=1);

namespace Iit\HyLib\Contracts;

use App\Utils\Convert;
use Hyperf\Contract\ValidatorInterface;
use Hyperf\Utils\ApplicationContext;
use Hyperf\Validation\Contract\ValidatorFactoryInterface;
use Hyperf\Validation\ValidationException;

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
        $this->input = Convert::arrayRealType($input);
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
