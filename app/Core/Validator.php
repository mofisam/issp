<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Tiny rule-based validator. Good enough for form input; swap in something
 * heavier if validation rules grow complex.
 */
class Validator
{
    private array $errors = [];

    public function __construct(private array $data)
    {
    }

    public function required(string $field, string $label): self
    {
        if (trim((string) ($this->data[$field] ?? '')) === '') {
            $this->errors[$field] = "{$label} is required.";
        }
        return $this;
    }

    public function email(string $field, string $label): self
    {
        if (!empty($this->data[$field]) && !filter_var($this->data[$field], FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field] = "{$label} must be a valid email address.";
        }
        return $this;
    }

    public function maxLength(string $field, int $max, string $label): self
    {
        if (!empty($this->data[$field]) && mb_strlen((string) $this->data[$field]) > $max) {
            $this->errors[$field] = "{$label} must be {$max} characters or fewer.";
        }
        return $this;
    }

    public function passes(): bool
    {
        return empty($this->errors);
    }

    public function errors(): array
    {
        return $this->errors;
    }
}
