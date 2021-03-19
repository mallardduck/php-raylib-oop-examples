<?php

namespace MallardDuck\Examples\Gorillas\Entities;

use MallardDuck\Examples\Gorillas\GlobalGorillasState;
use raylib\Color;
use raylib\Rectangle;

class Building
{
    public const RELATIVE_ERROR = 30;
    public const MIN_RELATIVE_HEIGHT = 20;
    public const MAX_RELATIVE_HEIGHT = 60;
    public const MIN_GRAYSCALE_COLOR = 120;
    public const MAX_GRAYSCALE_COLOR = 200;

    public Rectangle $rectangle;
    public Color $color;

    private function __construct()
    {
        $this->rectangle = new Rectangle(0, 0, 0, 0);
    }

    public static function getInstance(): self
    {
        return new self();
    }

    public static function initBuildings(int $screenWidth, int $screenHeight): array
    {
        /**
         * @var GlobalGorillasState $gameState
         */
        $gameState = game_state();
        $tmpBuildings = [];

        // Horizontal generation
        $currentWidth = 0;

        // We make sure the absolute error randomly generated for each building, has as a minimum value the screenWidth.
        // This way all the screen will be filled with buildings. Each building will have a different, random width.


        $relativeWidth = 100 / (100 - self::RELATIVE_ERROR);
        $buildingWidthMean = ($screenWidth * $relativeWidth / $gameState::MAX_BUILDINGS) + 1;        // We add one to make sure we will cover the whole screen.

        // Vertical generation
        $grayLevel = null;

        // Creation
        for ($i = 0; $i < $gameState::MAX_BUILDINGS; $i++) {
            $newBuilding = new self();

            // Horizontal
            $newBuilding->rectangle->setX($currentWidth);
            $newBuilding->rectangle->setWidth(rand($buildingWidthMean * (100 - self::RELATIVE_ERROR / 2) / 100 + 1, $buildingWidthMean * (100 + self::RELATIVE_ERROR) / 100));

            $currentWidth += $newBuilding->rectangle->getWidth();

            // Vertical
            $currentHeighth = rand(self::MIN_RELATIVE_HEIGHT, self::MAX_RELATIVE_HEIGHT);
            $newBuilding->rectangle->setY($screenHeight - ($screenHeight * $currentHeighth / 100));
            $newBuilding->rectangle->setHeight($screenHeight * $currentHeighth / 100 + 1);

            // Color
            $grayLevel = rand(self::MIN_GRAYSCALE_COLOR, self::MAX_GRAYSCALE_COLOR);
            $newBuilding->color = new Color($grayLevel, $grayLevel, $grayLevel, 255);

            // Push into temp array
            $tmpBuildings[$i] = $newBuilding;
        }

        return $tmpBuildings;
    }
}