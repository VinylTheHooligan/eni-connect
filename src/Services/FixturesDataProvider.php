<?php

namespace App\Services;

use Faker\Factory;
use Faker\Generator;

class FixturesDataProvider
{
    //// COMPTEUR DE GENERATION : PERMET DE CHOISIR LE NOMBRE D'INSTANCE CREER DANS LES FIXTURES ////
    private static int $villeCompte = 100;
    private static int $lieuCompte = 150;
    private static int $campusCompte = 20;
    private static int $utilisateurCompte = 100;
    private static int $gestionnaireCompte = 5;

    public function __construct()
    {}

    public static function faker(): Generator
    {
        return Factory::create('fr_FR');
    }

    public static function getVilleCompte(): int
    {
        return self::$villeCompte;
    }

    public static function getLieuCompte()
    {
        return self::$lieuCompte;
    }

    public static function getCampusCompte()
    {
        return self::$campusCompte;
    }

    public static function getUtilisateurCompte()
    {
        return self::$utilisateurCompte;
    }

    public static function getGestionnaireCompte()
    {
        return self::$gestionnaireCompte;
    }
}