<?php

namespace MallardDuck\Examples\FloppyBlob;

use MallardDuck\Examples\FloppyBlob\Entities\FloppyBlob;
use MallardDuck\Examples\FloppyBlob\Entities\Tube;
use MallardDuck\RayLibCore\GameState\AbstractGlobalGameState;
use MallardDuck\RayLibCore\GameState\GlobalGameState;
use raylib\Vector2;

class GlobalFloppyState extends AbstractGlobalGameState implements GlobalGameState
{
    public int $score = 0;
    public int $highScore = 0;
    public FloppyBlob $floppyBlob;

    /**
     * @var Tube[]
     */
    public array $tubesArray = [];

    /**
     * @var Vector2[]
     */
    public array $tubesPos = [];

    public int $tubesSpeedX = 0;
    public bool $superFx = false;

    public function __construct()
    {
        $this->floppyBlob = new FloppyBlob();
    }
}