<?php
// app/Services/ValidationService.php
declare(strict_types=1);

namespace App\Services;

class ValidationService
{
    public function validate(array $data, array $rules): array
    {
        $errors = [];
        
        foreach ($rules as $field => $ruleString) {
            $rules = explode('|', $ruleString);
            
            foreach ($rules as $rule) {
                $this->applyRule($field, $rule, $data, $errors);
            }
        }
        
        return $errors;
    }
    
    private function applyRule(string $field, string $rule, array $data, array &$errors): void
    {
        $parts = explode(':', $rule);
        $ruleName = $parts[0];
        $param = $parts[1] ?? null;
        
        $value = $data[$field] ?? null;
        
        match ($ruleName) {
            'required' => $this->validateRequired($field, $value, $errors),
            'string' => $this->validateString($field, $value, $errors),
            'max' => $this->validateMax($field, $value, (int)$param, $errors),
            'in' => $this->validateIn($field, $value, explode(',', $param), $errors),
            default => null
        };
    }
    
    private function validateRequired(string $field, mixed $value, array &$errors): void
    {
        if (empty($value)) {
            $errors[$field][] = "El campo $field es requerido";
        }
    }
    
    // ... otros métodos de validación
}