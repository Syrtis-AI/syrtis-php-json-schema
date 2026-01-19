<?php

namespace Wexample\SymfonyJsonSchema\Helper;

use Opis\JsonSchema\Validator;

class JsonSchemaValidationHelper
{
    public static function validateOrThrow(
        object $schema,
        array $data,
        string $contextLabel = 'schema'
    ): void {
        $dataObject = json_decode(json_encode($data, JSON_THROW_ON_ERROR));
        $result = (new Validator())->validate($dataObject, $schema);

        if ($result->isValid()) {
            return;
        }

        $error = $result->error();
        $message = $error ? self::formatErrorMessage($error) : 'unknown error';

        throw new \InvalidArgumentException(sprintf(
            '%s does not match schema: %s',
            $contextLabel,
            $message
        ));
    }

    private static function formatErrorMessage(object $error): string
    {
        $message = $error->message();
        $args = $error->args();

        if (! is_array($args) || $args === []) {
            return $message;
        }

        foreach ($args as $key => $value) {
            if (is_array($value)) {
                $value = implode(', ', array_map('strval', $value));
            } elseif (! is_scalar($value)) {
                $value = (string) $value;
            }

            $message = str_replace('{'.$key.'}', (string) $value, $message);
        }

        return $message;
    }
}
