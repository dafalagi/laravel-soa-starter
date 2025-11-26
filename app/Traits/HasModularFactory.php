<?php

namespace App\Traits;

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
        $model_name = class_basename(static::class);
        $model_namespace = static::getModelNamespace();
        
        // Try module-specific factory first
        $module_factory_class = static::getModuleFactoryClass($model_namespace, $model_name);
        if (class_exists($module_factory_class)) {
            return $module_factory_class::new();
        }

        // Fall back to global factory if module factory doesn't exist
        $global_factory_class = "Database\\Factories\\{$model_name}Factory";
        if (class_exists($global_factory_class)) {
            return $global_factory_class::new();
        }

        // If no factory exists, throw a meaningful error
        throw new \InvalidArgumentException(
            "Unable to locate factory for model [{$model_name}]. " .
            "Tried: [{$module_factory_class}], [{$global_factory_class}]"
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
    protected static function getModuleFactoryClass(string $model_namespace, string $model_name): string
    {
        // Convert Modules\Auth\Models to Modules\Auth\Database\Factories
        if (Str::startsWith($model_namespace, 'Modules\\')) {
            // Extract module name (e.g., 'Auth' from 'Modules\Auth\Models')
            $parts = explode('\\', $model_namespace);
            if (count($parts) >= 3 && $parts[0] === 'Modules' && $parts[2] === 'Models') {
                $module_name = $parts[1];
                return "Modules\\{$module_name}\\Database\\Factories\\{$model_name}Factory";
            }
        }

        // For non-module models, fall back to global factory
        return "Database\\Factories\\{$model_name}Factory";
    }
}