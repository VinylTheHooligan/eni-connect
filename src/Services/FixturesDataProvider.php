<?php

namespace App\Services;

use Faker\Factory;
use Faker\Generator;

class FixturesDataProvider
{
    private int $cityCount = 100;
    private int $placeCount = 150;
    private int $campusCount = 20;
    private int $userCount = 200;
    private int $organizerCount = 5;

    private Generator $faker;

    public function __construct()
    {
        $this->faker = Factory::create('fr_FR');
    }

    public function faker(): Generator
    {
        return $this->faker;
    }

    public function getCityCount(): int
    {
        return $this->cityCount;
    }

    public function setCityCount(int $count): void
    {
        $this->cityCount = $count;
    }

    public function getPlaceCount(): int
    {
        return $this->placeCount;
    }

    public function setPlaceCount(int $count): void
    {
        $this->placeCount = $count;
    }

    public function getCampusCount(): int
    {
        return $this->campusCount;
    }

    public function setCampusCount(int $count): void
    {
        $this->campusCount = $count;
    }

    public function getUserCount(): int
    {
        return $this->userCount;
    }

    public function setUserCount(int $count): void
    {
        $this->userCount = $count;
    }

    public function getOrganizerCount(): int
    {
        return $this->organizerCount;
    }

    public function setOrganizerCount(int $count): void
    {
        $this->organizerCount = $count;
    }
}
