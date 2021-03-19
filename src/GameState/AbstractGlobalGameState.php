<?php

namespace MallardDuck\RayLibCore\GameState;

abstract class AbstractGlobalGameState implements GlobalGameState
{
    public int $screenWidth = 800;
    public int $screenHeight = 450;

    public bool $gameOver = false;
    public bool $pause = false;
}