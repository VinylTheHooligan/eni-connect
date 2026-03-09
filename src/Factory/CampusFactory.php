<?php

namespace App\Factory;

use App\Entity\Campus;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<Campus>
 */
final class CampusFactory extends PersistentProxyObjectFactory
{

    public function __construct()
    {
    }

    public static function class(): string
    {
        return Campus::class;
    }

    protected function defaults(): array|callable
    {
        return [
            'name' => 'Campus de ' . self::faker()->city(),
        ];
    }

    protected function initialize(): static
    {
        return $this
            // ->afterInstantiate(function(Campus $campus): void {})
        ;
    }
}
