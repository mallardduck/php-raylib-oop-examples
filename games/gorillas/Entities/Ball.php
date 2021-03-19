<?php

namespace MallardDuck\Examples\Gorillas\Entities;

use raylib\Vector2;

class Ball
{
    public Vector2 $position;
    public Vector2 $speed;
    public int $radius;
    public bool $active;

    public function __construct()
    {
        $this->position = new Vector2(0, 0);
        $this->speed = new Vector2(0, 0);
    }
}