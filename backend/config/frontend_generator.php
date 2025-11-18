<?php

declare(strict_types=1);

use App\Helpers\ValidationHelper;

return [
    'type_mappings' => [
        'string' => 'text',
        'int' => 'number',
        'integer' => 'number',
        'float' => 'number',
        'double' => 'number',
        'bool' => 'checkbox',
        'boolean' => 'checkbox',
        'array' => 'multiselect',
        'datetime' => 'datetime-local',
        'datetimeimmutable' => 'datetime-local',
        'date' => 'datetime-local',
        'enum' => 'select',
        'default' => 'text',
    ],
    'typescript_mappings' => [
        'string' => 'string',
        'int' => 'number',
        'integer' => 'number',
        'float' => 'number',
        'double' => 'number',
        'bool' => 'boolean',
        'boolean' => 'boolean',
        'array' => 'any[]',
        'datetime' => 'string',
        'datetimeimmutable' => 'string',
        'date' => 'string',
        'enum' => 'string',
        'default' => 'string',
    ],
   'validations' => [
        'required' => [
            'rule' => 'required: "config.form.{name}.validation.required"',
            'condition' => [ValidationHelper::class, 'requiredCondition'],  // Updated function name
        ],
        'email' => [
            'rule' => 'pattern: { value: /^[^\\s@]+@[^\s@]+\\.[^\\s@]+$/, message: "config.form.{name}.validation.email" }',
            'condition' => [ValidationHelper::class, 'emailCondition'],  // Updated function name
        ],
        'password' => [
            'rule' => 'minLength: { value: 8, message: "config.form.{name}.validation.minLength" }',
            'condition' => [ValidationHelper::class, 'passwordCondition'],  // Updated function name
        ],
        'id_field' => [
            'rule' => 'pattern: { value: /^\\d+$/, message: "config.form.{name}.validation.pattern" }',
            'condition' => [ValidationHelper::class, 'idFieldCondition'],  // Updated function name
        ],
        'max_length' => [
            'rule' => 'maxLength: { value: 255, message: "config.form.{name}.validation.maxLength" }',
            'condition' => [ValidationHelper::class, 'maxLengthCondition'],  // Updated function name
        ],
        'file' => [
            'rule' => 'mimes: { value: ["jpg", "jpeg", "png"], message: "config.form.{name}.validation.mimes" }',
            'condition' => [ValidationHelper::class, 'fileCondition'],  // Updated function name
        ],
        'file_size' => [
            'rule' => 'maxSize: { value: 5120, message: "config.form.{name}.validation.maxSize" }',
            'condition' => [ValidationHelper::class, 'fileSizeCondition'],  // Updated function name
        ],
        'enum' => [
            'rule' => 'in: { value: {options}, message: "config.form.{name}.validation.in" }',
            'condition' => [ValidationHelper::class, 'enumCondition'],  // Updated function name
        ],
    ],
    'logging' => [
        'channel' => 'daily',
        'level' => 'info',
    ],
];
