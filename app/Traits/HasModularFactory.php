<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory as BaseHasFactory;
use Illuminate\Support\Str;

trait HasModularFactory
{
    use BaseHasFactory;

    /**
     * Create a new factory instance for the model.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    protected static function newFactory()
    {
        $modelName = class_basename(static::class);
        $modelNamespace = static::getModelNamespace();
        
        // Try module-specific factory first
        $moduleFactoryClass = static::getModuleFactoryClass($modelNamespace, $modelName);
        if (class_exists($moduleFactoryClass)) {
            return $moduleFactoryClass::new();
        }

        // Fall back to global factory if module factory doesn't exist
        $globalFactoryClass = "Database\\Factories\\{$modelName}Factory";
        if (class_exists($globalFactoryClass)) {
            return $globalFactoryClass::new();
        }

        // If no factory exists, throw a meaningful error
        throw new \InvalidArgumentException(
            "Unable to locate factory for model [{$modelName}]. " .
            "Tried: [{$moduleFactoryClass}], [{$globalFactoryClass}]"
        );
    }

    /**
     * Get the model namespace from the current model class.
     */
    protected static function getModelNamespace(): string
    {
        $reflection = new \ReflectionClass(static::class);
        return $reflection->getNamespaceName();
    }

    /**
     * Generate the module factory class name based on model namespace.
     */
    protected static function getModuleFactoryClass(string $modelNamespace, string $modelName): string
    {
        // Convert Modules\Auth\Models to Modules\Auth\Database\Factories
        if (Str::startsWith($modelNamespace, 'Modules\\')) {
            // Extract module name (e.g., 'Auth' from 'Modules\Auth\Models')
            $parts = explode('\\', $modelNamespace);
            if (count($parts) >= 3 && $parts[0] === 'Modules' && $parts[2] === 'Models') {
                $moduleName = $parts[1];
                return "Modules\\{$moduleName}\\Database\\Factories\\{$modelName}Factory";
            }
        }

        // For non-module models, fall back to global factory
        return "Database\\Factories\\{$modelName}Factory";
    }
}