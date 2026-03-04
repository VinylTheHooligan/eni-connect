<?php

namespace App\Services;

use Faker\Factory;
use Faker\Generator;

class FixturesDataProvider
{
    //// COMPTEUR DE GENERATION : PERMET DE CHOISIR LE NOMBRE D'INSTANCE CREER DANS LES FIXTURES ////
    private static int $cityCount = 100;
    private static int $placeCount = 150;
    private static int $campusCount = 20;
    private static int $userCount = 100;
    private static int $managerCount = 5;

    public function __construct()
    {}

    public static function faker(): Generator
    {
        return Factory::create('fr_FR');
    }

    public static function getCityCount(): int
    {
        return self::$cityCount;
    }

    public static function getPlaceCount()
    {
        return self::$placeCount;
    }

    public static function getCampusCount()
    {
        return self::$campusCount;
    }

    public static function getUserCount()
    {
        return self::$userCount;
    }

    public static function getManagerCount()
    {
        return self::$managerCount;
    }
}