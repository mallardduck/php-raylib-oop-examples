<?php

namespace MallardDuck\Examples\Gorillas\Entities;

use MallardDuck\Examples\Gorillas\GlobalGorillasState;
use raylib\Vector2;

class Explosion
{
    public Vector2 $position;
    public int $radius = 30;
    public bool $active = false;

    private function __construct()
    {
        $this->position = new Vector2(0.0, 0.0);
    }

    public static function getInstance(): self
    {
        return new self();
    }

    public static function initExplosions(): array
    {
        $tmpExplosions = [];
        /**
         * @var GlobalGorillasState $gameState
         */
        $gameState = game_state();

        for ($i = 0; $i < $gameState::MAX_EXPLOSIONS; $i++) {
            $newExplosion = self::getInstance();

            $tmpExplosions[$i] = $newExplosion;
        }

        return $tmpExplosions;
    }
}