<?php

namespace MallardDuck\Examples\Gorillas;

use MallardDuck\Examples\Gorillas\Entities\Ball;
use MallardDuck\Examples\Gorillas\Entities\Building;
use MallardDuck\Examples\Gorillas\Entities\Explosion;
use MallardDuck\Examples\Gorillas\Entities\Player;
use MallardDuck\RayLibCore\GameState\AbstractGlobalGameState;
use MallardDuck\RayLibCore\GameState\GlobalGameState;

class GlobalGorillasState extends AbstractGlobalGameState implements GlobalGameState
{
    public const MAX_BUILDINGS = 15;
    public const MAX_EXPLOSIONS = 200;
    public const MAX_PLAYERS = 2;

    public const GRAVITY = 9.81;
    public const DELTA_FPS = 60;

    public const RAD2DEG = 57.2958;
    public const DEG2RAD = 0.0174533;

    /**
     * @var Player[]
     */
    public array $players = [];
    /**
     * @var Building[]
     */
    public array $buildings = [];
    /**
     * @var Explosion[]
     */
    public array $explosions = [];

    public ?Ball $ball;

    public int $playerTurn = 0;
    public bool $ballInAir = false;

    public function __construct()
    {
        $this->ball = new Ball();
    }
}