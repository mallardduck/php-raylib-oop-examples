<?php

namespace MallardDuck\Examples\Gorillas\Entities;

use MallardDuck\Examples\Gorillas\GlobalGorillasState;
use raylib\Vector2;

class Player
{
    public const MIN_POSITION = 5;
    public const MAX_POSITION = 20;

    public Vector2 $position;

    public Vector2 $size;

    public Vector2 $aimingPoint;
    public float $aimingAngle;
    public float $aimingPower;

    public Vector2 $previousPoint;
    public float $previousAngle;
    public float $previousPower;

    public Vector2 $impactPoint;

    public bool $isLeftTeam;                // This player belongs to the left or to the right team
    public bool $isPlayer = true;           // If is a player or an AI - for now there is no AI...
    public bool $isAlive = true;

    private function __construct()
    {
        $this->position = new Vector2(0, 0);
        $this->size = new Vector2(0, 0);
        $this->aimingPoint = new Vector2(0, 0);
        $this->previousPoint = new Vector2(0, 0);
        $this->impactPoint = new Vector2(0, 0);
    }

    public static function getInstance(): self
    {
        return new self();
    }

    public static function initPlayers(): array
    {
        $tmpPlayers = [];
        /**
         * @var GlobalGorillasState $gameState
         */
        $gameState = game_state();

        for ($i = 0; $i < $gameState::MAX_PLAYERS; $i++) {
            $tmpPlayer = self::getInstance();
            $tmpPlayer->isLeftTeam = ($i % 2 == 0);
            $tmpPlayer->size = new Vector2(40, 40);

            // Set position
            if ($tmpPlayer->isLeftTeam) {
                $randSpot = rand(
                    $gameState->screenWidth * self::MIN_POSITION / 100,
                    $gameState->screenWidth * self::MAX_POSITION / 100
                );
                $tmpPlayer->position->setX($randSpot);
            } else {
                $randSpot = rand(
                    $gameState->screenWidth * self::MIN_POSITION / 100,
                    $gameState->screenWidth * self::MAX_POSITION / 100
                );
                $tmpPlayer->position->setX($gameState->screenWidth - $randSpot);
            }

            // Place the player
            for ($j = 0; $j < $gameState::MAX_BUILDINGS; $j++) {
                if ($gameState->buildings[$j]->rectangle->getX() > $tmpPlayer->position->getX()) {
                    $thisBuilding = $gameState->buildings[$j - 1];
                    // Set the player in the center of the building
                    $tmpPlayer->position->setX($thisBuilding->rectangle->getX() + $thisBuilding->rectangle->getWidth() / 2);
                    // Set the player at the top of the building
                    $tmpPlayer->position->setY($thisBuilding->rectangle->getY() - $tmpPlayer->size->getY() / 2);

                    break;
                }
            }

            // Set statistics to 0
            $tmpPlayer->aimingPoint = clone $tmpPlayer->position;
            $tmpPlayer->previousAngle = 0;
            $tmpPlayer->previousPower = 0;
            $tmpPlayer->previousPoint = clone $tmpPlayer->position;
            $tmpPlayer->aimingAngle = 0;
            $tmpPlayer->aimingPower = 0;

            $tmpPlayer->impactPoint = new Vector2(-100, -100);
            $tmpPlayers[$i] = $tmpPlayer;
        }

        return $tmpPlayers;
    }
}