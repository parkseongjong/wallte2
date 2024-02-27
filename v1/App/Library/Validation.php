<?php
namespace App\Library;

use App\Exception\InvalidArgumentException;

class Validation extends \Awurth\SlimValidation\Validator
{
    /**
     * Constructor.
     *
     * @param bool     $showValidationRules
     * @param string[] $defaultMessages
     */
    public function __construct(bool $showValidationRules = false, array $defaultMessages = [])
    {
        parent::__construct($showValidationRules,$defaultMessages);
    }

    /**
     * @return bool
     * @throws InvalidArgumentException
     */
    public function isValid(): bool
    {
        $firstKey = key($this->errors);
        $this->errors[$firstKey][0];

        if ($this->errors) {
            throw new InvalidArgumentException($this->errors[$firstKey][0]);
        }
        return '';
    }
}

/* End of file Validation.php */
/* Location: /app/Library/Validation.php */