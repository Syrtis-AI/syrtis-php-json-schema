<?php

namespace Syrtis\JsonSchema\Helper;

class JsonSchemaHelper
{
    public static function mergeSchemaObjects(
        object $schema,
        object $baseSchema
    ): object {
        $schemaProperties = (array) ($schema->properties ?? []);
        $baseProperties = (array) ($baseSchema->properties ?? []);
        $schemaRequired = is_array($schema->required ?? null) ? $schema->required : [];
        $baseRequired = is_array($baseSchema->required ?? null) ? $baseSchema->required : [];

        $combined = (object) array_merge((array) $schema, [
            'type' => 'object',
            'properties' => (object) array_merge($baseProperties, $schemaProperties),
            'required' => array_values(array_unique(array_merge($baseRequired, $schemaRequired))),
            'additionalProperties' => false,
        ]);

        unset($combined->allOf, $combined->{'$ref'}, $combined->unevaluatedProperties);

        return $combined;
    }

    public static function mergeAllOfSchema(
        object $schema,
        bool $strict = true
    ): object {
        if (! isset($schema->allOf) || ! is_array($schema->allOf)) {
            return $schema;
        }

        $properties = (array) ($schema->properties ?? []);
        $required = is_array($schema->required ?? null) ? $schema->required : [];

        foreach ($schema->allOf as $entry) {
            if (! is_object($entry)) {
                continue;
            }

            $entryProperties = (array) ($entry->properties ?? []);
            $entryRequired = is_array($entry->required ?? null) ? $entry->required : [];

            $properties = array_merge($properties, $entryProperties);
            $required = array_merge($required, $entryRequired);
        }

        $schema->properties = (object) $properties;
        $schema->required = array_values(array_unique($required));

        if ($strict) {
            $schema->additionalProperties = false;
        }

        unset($schema->allOf, $schema->unevaluatedProperties);

        return $schema;
    }
}
