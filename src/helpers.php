<?php

use MallardDuck\RayLibCore\GameState\GlobalGameState;
use MallardDuck\RayLibCore\Kernel;

function game($abstract = null)
{
    if (is_null($abstract)) {
        return Kernel::getInstance();
    }

    return Kernel::getInstance()->make($abstract);
}

function game_state(): GlobalGameState
{
    return game(GlobalGameState::class);
}
