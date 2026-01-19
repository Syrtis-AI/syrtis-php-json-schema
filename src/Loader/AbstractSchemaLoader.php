<?php

namespace Wexample\SymfonyJsonSchema\Loader;

use Wexample\SymfonyHelpers\Helper\JsonHelper;

abstract class AbstractSchemaLoader
{
    protected array $cache = [];

    public function load(string $name): object|null
    {
        $path = $this->getSchemaPath($name);
        $schema = $this->loadSchemaFile($path);

        if (! $schema) {
            return null;
        }

        return $this->resolveRefs($schema, []);
    }

    abstract protected function getSchemaDirectory(): string;

    protected function getSchemaBaseUrl(): ?string
    {
        return null;
    }

    protected function getSchemaPath(string $name): string
    {
        return $this->getSchemaDirectory().'/'.$name.'.json';
    }

    protected function loadSchemaFile(string $path): ?object
    {
        if (isset($this->cache[$path])) {
            return $this->cache[$path];
        }

        $schema = JsonHelper::readOrNull($path);
        if ($schema) {
            $this->cache[$path] = $schema;
        }

        return $schema;
    }

    protected function resolveRefs(mixed $node, array $stack): mixed
    {
        if (is_array($node)) {
            foreach ($node as $key => $value) {
                $node[$key] = $this->resolveRefs($value, $stack);
            }

            return $node;
        }

        if (! is_object($node)) {
            return $node;
        }

        if (isset($node->{'$ref'}) && is_string($node->{'$ref'})) {
            $refPath = $this->resolveRefPath($node->{'$ref'});
            if ($refPath && ! in_array($refPath, $stack, true)) {
                $schema = $this->loadSchemaFile($refPath);
                if ($schema) {
                    return $this->resolveRefs($schema, array_merge($stack, [$refPath]));
                }
            }

            return $node;
        }

        foreach ($node as $key => $value) {
            $node->{$key} = $this->resolveRefs($value, $stack);
        }

        return $node;
    }

    protected function resolveRefPath(string $ref): ?string
    {
        $ref = explode('#', $ref)[0];
        if ($ref === '') {
            return null;
        }

        $baseUrl = $this->getSchemaBaseUrl();
        if ($baseUrl && str_starts_with($ref, $baseUrl)) {
            $ref = substr($ref, strlen($baseUrl));
        }

        $file = basename($ref);
        if (! str_ends_with($file, '.json')) {
            return null;
        }

        $name = pathinfo($file, PATHINFO_FILENAME);

        return $this->getSchemaPath($name);
    }
}
