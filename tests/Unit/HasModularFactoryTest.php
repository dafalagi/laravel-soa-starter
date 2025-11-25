<?php

namespace Tests\Unit;

use Tests\TestCase;
use Modules\Auth\Models\User;

class HasModularFactoryTest extends TestCase
{
    /**
     * Test that the HasModularFactory trait correctly creates factories for module models.
     */
    public function test_has_modular_factory_creates_correct_factory(): void
    {
        // When creating a factory instance from a module model
        $factory = User::factory();
        
        // Then it should return the correct module-specific factory
        $this->assertInstanceOf(
            \Modules\Auth\Database\Factories\UserFactory::class,
            $factory
        );
    }
    
    /**
     * Test that factory can create model instances correctly.
     */
    public function test_modular_factory_creates_model_instances(): void
    {
        // When creating a user through the modular factory (without persisting)
        $user = User::factory()->make([
            'email' => 'test@example.com'
        ]);
        
        // Then it should create a valid User instance
        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('test@example.com', $user->email);
        $this->assertNotEmpty($user->name);
    }
}