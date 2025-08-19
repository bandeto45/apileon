<?php

namespace Apileon\Validation;

class Validator
{
    protected array $data = [];
    protected array $rules = [];
    protected array $errors = [];
    protected array $customMessages = [];

    public function __construct(array $data, array $rules, array $customMessages = [])
    {
        $this->data = $data;
        $this->rules = $rules;
        $this->customMessages = $customMessages;
    }

    public static function make(array $data, array $rules, array $customMessages = []): self
    {
        return new static($data, $rules, $customMessages);
    }

    public function validate(): array
    {
        $this->errors = [];

        foreach ($this->rules as $field => $rules) {
            $fieldRules = is_string($rules) ? explode('|', $rules) : $rules;
            $this->validateField($field, $fieldRules);
        }

        if (!empty($this->errors)) {
            throw new ValidationException('Validation failed', $this->errors);
        }

        return $this->getValidatedData();
    }

    public function fails(): bool
    {
        try {
            $this->validate();
            return false;
        } catch (ValidationException $e) {
            return true;
        }
    }

    public function passes(): bool
    {
        return !$this->fails();
    }

    public function errors(): array
    {
        return $this->errors;
    }

    protected function validateField(string $field, array $rules): void
    {
        $value = $this->getValue($field);

        foreach ($rules as $rule) {
            $this->applyRule($field, $rule, $value);
        }
    }

    protected function applyRule(string $field, string $rule, $value): void
    {
        if (str_contains($rule, ':')) {
            [$ruleName, $parameter] = explode(':', $rule, 2);
        } else {
            $ruleName = $rule;
            $parameter = null;
        }

        $method = 'validate' . ucfirst($ruleName);

        if (!method_exists($this, $method)) {
            throw new \InvalidArgumentException("Validation rule '{$ruleName}' does not exist");
        }

        if (!$this->$method($field, $value, $parameter)) {
            $this->addError($field, $ruleName, $parameter);
        }
    }

    protected function validateRequired(string $field, $value): bool
    {
        if (is_null($value)) {
            return false;
        }

        if (is_string($value) && trim($value) === '') {
            return false;
        }

        if (is_array($value) && empty($value)) {
            return false;
        }

        return true;
    }

    protected function validateEmail(string $field, $value): bool
    {
        if (is_null($value) || $value === '') {
            return true; // Let required rule handle this
        }

        return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
    }

    protected function validateMin(string $field, $value, string $parameter): bool
    {
        if (is_null($value)) {
            return true;
        }

        $min = (int) $parameter;

        if (is_string($value)) {
            return mb_strlen($value) >= $min;
        }

        if (is_numeric($value)) {
            return $value >= $min;
        }

        if (is_array($value)) {
            return count($value) >= $min;
        }

        return false;
    }

    protected function validateMax(string $field, $value, string $parameter): bool
    {
        if (is_null($value)) {
            return true;
        }

        $max = (int) $parameter;

        if (is_string($value)) {
            return mb_strlen($value) <= $max;
        }

        if (is_numeric($value)) {
            return $value <= $max;
        }

        if (is_array($value)) {
            return count($value) <= $max;
        }

        return false;
    }

    protected function validateNumeric(string $field, $value): bool
    {
        if (is_null($value) || $value === '') {
            return true;
        }

        return is_numeric($value);
    }

    protected function validateInteger(string $field, $value): bool
    {
        if (is_null($value) || $value === '') {
            return true;
        }

        return filter_var($value, FILTER_VALIDATE_INT) !== false;
    }

    protected function validateString(string $field, $value): bool
    {
        if (is_null($value)) {
            return true;
        }

        return is_string($value);
    }

    protected function validateArray(string $field, $value): bool
    {
        if (is_null($value)) {
            return true;
        }

        return is_array($value);
    }

    protected function validateBoolean(string $field, $value): bool
    {
        if (is_null($value)) {
            return true;
        }

        $acceptable = [true, false, 0, 1, '0', '1', 'true', 'false'];
        return in_array($value, $acceptable, true);
    }

    protected function validateIn(string $field, $value, string $parameter): bool
    {
        if (is_null($value) || $value === '') {
            return true;
        }

        $values = explode(',', $parameter);
        return in_array((string) $value, $values);
    }

    protected function validateNotIn(string $field, $value, string $parameter): bool
    {
        if (is_null($value) || $value === '') {
            return true;
        }

        $values = explode(',', $parameter);
        return !in_array((string) $value, $values);
    }

    protected function validateUnique(string $field, $value, string $parameter): bool
    {
        if (is_null($value) || $value === '') {
            return true;
        }

        // Parameter format: table,column,ignore_id
        $parts = explode(',', $parameter);
        $table = $parts[0];
        $column = $parts[1] ?? $field;
        $ignoreId = $parts[2] ?? null;

        $query = new \Apileon\Database\QueryBuilder();
        $query->table($table)->where($column, $value);

        if ($ignoreId) {
            $query->where('id', '!=', $ignoreId);
        }

        return !$query->exists();
    }

    protected function validateExists(string $field, $value, string $parameter): bool
    {
        if (is_null($value) || $value === '') {
            return true;
        }

        // Parameter format: table,column
        $parts = explode(',', $parameter);
        $table = $parts[0];
        $column = $parts[1] ?? $field;

        $query = new \Apileon\Database\QueryBuilder();
        return $query->table($table)->where($column, $value)->exists();
    }

    protected function validateRegex(string $field, $value, string $parameter): bool
    {
        if (is_null($value) || $value === '') {
            return true;
        }

        return preg_match($parameter, $value) > 0;
    }

    protected function validateAlpha(string $field, $value): bool
    {
        if (is_null($value) || $value === '') {
            return true;
        }

        return preg_match('/^[a-zA-Z]+$/', $value) > 0;
    }

    protected function validateAlphaNum(string $field, $value): bool
    {
        if (is_null($value) || $value === '') {
            return true;
        }

        return preg_match('/^[a-zA-Z0-9]+$/', $value) > 0;
    }

    protected function validateAlphaDash(string $field, $value): bool
    {
        if (is_null($value) || $value === '') {
            return true;
        }

        return preg_match('/^[a-zA-Z0-9_-]+$/', $value) > 0;
    }

    protected function validateUrl(string $field, $value): bool
    {
        if (is_null($value) || $value === '') {
            return true;
        }

        return filter_var($value, FILTER_VALIDATE_URL) !== false;
    }

    protected function validateIp(string $field, $value): bool
    {
        if (is_null($value) || $value === '') {
            return true;
        }

        return filter_var($value, FILTER_VALIDATE_IP) !== false;
    }

    protected function validateDate(string $field, $value): bool
    {
        if (is_null($value) || $value === '') {
            return true;
        }

        try {
            new \DateTime($value);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    protected function validateAfter(string $field, $value, string $parameter): bool
    {
        if (is_null($value) || $value === '') {
            return true;
        }

        try {
            $date = new \DateTime($value);
            $after = new \DateTime($parameter);
            return $date > $after;
        } catch (\Exception $e) {
            return false;
        }
    }

    protected function validateBefore(string $field, $value, string $parameter): bool
    {
        if (is_null($value) || $value === '') {
            return true;
        }

        try {
            $date = new \DateTime($value);
            $before = new \DateTime($parameter);
            return $date < $before;
        } catch (\Exception $e) {
            return false;
        }
    }

    protected function getValue(string $field)
    {
        return $this->data[$field] ?? null;
    }

    protected function addError(string $field, string $rule, ?string $parameter = null): void
    {
        $message = $this->getErrorMessage($field, $rule, $parameter);
        
        if (!isset($this->errors[$field])) {
            $this->errors[$field] = [];
        }
        
        $this->errors[$field][] = $message;
    }

    protected function getErrorMessage(string $field, string $rule, ?string $parameter = null): string
    {
        $key = "{$field}.{$rule}";
        
        if (isset($this->customMessages[$key])) {
            return $this->customMessages[$key];
        }

        return $this->getDefaultMessage($field, $rule, $parameter);
    }

    protected function getDefaultMessage(string $field, string $rule, ?string $parameter = null): string
    {
        $fieldName = str_replace('_', ' ', $field);
        
        $messages = [
            'required' => "The {$fieldName} field is required.",
            'email' => "The {$fieldName} must be a valid email address.",
            'min' => "The {$fieldName} must be at least {$parameter} characters.",
            'max' => "The {$fieldName} may not be greater than {$parameter} characters.",
            'numeric' => "The {$fieldName} must be a number.",
            'integer' => "The {$fieldName} must be an integer.",
            'string' => "The {$fieldName} must be a string.",
            'array' => "The {$fieldName} must be an array.",
            'boolean' => "The {$fieldName} field must be true or false.",
            'in' => "The selected {$fieldName} is invalid.",
            'not_in' => "The selected {$fieldName} is invalid.",
            'unique' => "The {$fieldName} has already been taken.",
            'exists' => "The selected {$fieldName} is invalid.",
            'regex' => "The {$fieldName} format is invalid.",
            'alpha' => "The {$fieldName} may only contain letters.",
            'alpha_num' => "The {$fieldName} may only contain letters and numbers.",
            'alpha_dash' => "The {$fieldName} may only contain letters, numbers, dashes and underscores.",
            'url' => "The {$fieldName} format is invalid.",
            'ip' => "The {$fieldName} must be a valid IP address.",
            'date' => "The {$fieldName} is not a valid date.",
            'after' => "The {$fieldName} must be a date after {$parameter}.",
            'before' => "The {$fieldName} must be a date before {$parameter}.",
        ];

        return $messages[$rule] ?? "The {$fieldName} field is invalid.";
    }

    protected function getValidatedData(): array
    {
        $validated = [];
        
        foreach ($this->rules as $field => $rules) {
            if (array_key_exists($field, $this->data)) {
                $validated[$field] = $this->data[$field];
            }
        }
        
        return $validated;
    }
}
