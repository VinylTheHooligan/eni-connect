<?php

namespace App\Factory;

use App\Entity\Outing;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<Outing>
 */
final class OutingFactory extends PersistentProxyObjectFactory
{
    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#factories-as-services
     *
     * @todo inject services if required
     */
    public function __construct()
    {
    }

    public static function class(): string
    {
        return Outing::class;
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     *
     * @todo add your default values here
     */
    protected function defaults(): array|callable
    {
        return [
            'campus' => CampusFactory::new(),
            'createdDateTime' => \DateTimeImmutable::createFromMutable(self::faker()->dateTime()),
            'duration' => self::faker()->randomNumber(),
            'maxRegistrations' => self::faker()->randomNumber(),
            'name' => self::faker()->text(180),
            'organizer' => UserFactory::new(),
            'place' => PlaceFactory::new(),
            'published' => self::faker()->boolean(),
            'registrationDeadline' => \DateTimeImmutable::createFromMutable(self::faker()->dateTime()),
            'startDateTime' => \DateTimeImmutable::createFromMutable(self::faker()->dateTime()),
            'status' => self::faker()->text(50),
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): static
    {
        return $this
            // ->afterInstantiate(function(Outing $outing): void {})
        ;
    }
}
