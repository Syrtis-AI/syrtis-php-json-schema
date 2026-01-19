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
}
