<?php

namespace MallardDuck\Examples\FloppyBlob;

use DI\Container;
use MallardDuck\Examples\FloppyBlob\Entities\Tube;
use MallardDuck\RayLibCore\BasicColors;
use MallardDuck\RayLibCore\GameState\GlobalGameState;
use MallardDuck\RayLibCore\Kernel;
use raylib\Collision;
use raylib\Draw;
use raylib\Input\Key;
use raylib\Rectangle;
use raylib\Text;
use raylib\Vector2;
use raylib\Window;

class FloppyBlobKernel extends Kernel
{
    public const FLOPPY_RADIUS = 23;
    public const TUBES_WIDTH = 80;
    public const MAX_TUBES = 100;

    public function __construct() {
        parent::__construct('Floppy Blob - Sample Game');
    }

    public function getStateFromClass(): string
    {
        return GlobalFloppyState::class;
    }

    protected function initContainer(Container $container): ?Container
    {
        $container->set(BasicColors::class, BasicColors::getInstance());
        return $container;
    }

    /**
     * @param GlobalGameState|GlobalFloppyState $gameState
     */
    public function bootGameState(GlobalGameState $gameState)
    {
        $gameState->floppyBlob->radius = self::FLOPPY_RADIUS;
        $gameState->floppyBlob->position = new Vector2(80, $this->screenHeight / 2 - $gameState->floppyBlob->radius);
        $gameState->tubesSpeedX = 2;

        // Randomly generate tube positions..
        for ($i = 0; $i < self::MAX_TUBES; $i++) {
            $gameState->tubesPos[$i] = new Vector2(400 + 280 * $i, -rand(0, 120));
        }

        // Init tube's at various indexes...
        for ($i = 0; $i < self::MAX_TUBES * 2; $i += 2) {
            if (!isset($gameState->tubesArray[$i])) {
                $gameState->tubesArray[$i] = new Tube();
            }
            if (!isset($tubes[$i + 1])) {
                $gameState->tubesArray[$i + 1] = new Tube();
            }
            if (!isset($tubes[$i / 2])) {
                $gameState->tubesArray[$i / 2] = new Tube();
            }
        }

        // Init and randomize tube sizes/shapes...
        for ($i = 0; $i < self::MAX_TUBES * 2; $i += 2) {
            $gameState->tubesArray[$i]->rec = new Rectangle(
                $gameState->tubesPos[$i / 2]->getX(),
                $gameState->tubesPos[$i / 2]->getY(),
                self::TUBES_WIDTH,
                255
            );

            $gameState->tubesArray[$i + 1]->rec = new Rectangle(
                $gameState->tubesPos[$i / 2]->getX(),
                600 + $gameState->tubesPos[$i / 2]->getY() - 255,
                self::TUBES_WIDTH,
                255
            );

            $gameState->tubesArray[$i / 2]->active = true;
        }

        $gameState->score = 0;
        $gameState->gameOver = false;
        $gameState->superFx = false;
        $gameState->pause = false;
    }

    public function physicsProcess(int $delta)
    {
        /**
         * @var GlobalFloppyState $gameState
         */
        $gameState = game_state();

        if (!$gameState->gameOver) {
            if (Key::isPressed(Key::P)) {
                $gameState->pause = !$gameState->pause;
            }

            if (!$gameState->pause) {
                for ($i = 0; $i < self::MAX_TUBES; $i++) {
                    $gameState->tubesPos[$i]->setX($gameState->tubesPos[$i]->getX() - $gameState->tubesSpeedX);
                }

                for ($i = 0; $i < self::MAX_TUBES * 2; $i += 2) {
                    $gameState->tubesArray[$i]->rec->setX($gameState->tubesPos[$i / 2]->getX());
                    $gameState->tubesArray[$i + 1]->rec->setX($gameState->tubesPos[$i / 2]->getX());
                }

                if (Key::isDown(Key::SPACE) && !$gameState->gameOver) {
                    $gameState->floppyBlob->position->setY($gameState->floppyBlob->position->getY() - 3);
                } else {
                    $gameState->floppyBlob->position->setY($gameState->floppyBlob->position->getY() + 1);
                }

                // Check Collisions
                for ($i = 0; $i < self::MAX_TUBES * 2; $i++) {
                    if (Collision::checkCircleRec($gameState->floppyBlob->position, $gameState->floppyBlob->radius, $gameState->tubesArray[$i]->rec)) {
                        $gameState->gameOver = true;
                        $gameState->pause = false;
                    } else if (($gameState->tubesPos[$i / 2]->getX() < $gameState->floppyBlob->position->getX()) && $gameState->tubesArray[$i / 2]->active && !$gameState->gameOver) {
                        $gameState->score += 100;
                        $gameState->tubesArray[$i / 2]->active = false;

                        $gameState->superFx = true;

                        if ($gameState->score > $gameState->highScore) {
                            $gameState->highScore = $gameState->score;
                        }
                    }
                }
            }
        } else {
            if (Key::isPressed(Key::ENTER)) {
                $this->bootGameState($gameState);
                $gameState->gameOver = false;
            }
        }
    }

    public function process(int $delta)
    {
        /**
         * @var GlobalFloppyState $gameState
         */
        $gameState = game_state();

        if (!$gameState->gameOver) {
            Draw::circle($gameState->floppyBlob->position->getX(), $gameState->floppyBlob->position->getY(), $gameState->floppyBlob->radius, BasicColors::darkGray());

            // Draw tubes
            for ($i = 0; $i < self::MAX_TUBES; $i++) {
                $thisTube = $gameState->tubesArray[$i * 2];
                $thatTube = $gameState->tubesArray[$i * 2 + 1];
                Draw::rectangle($thisTube->rec->getX(), $thisTube->rec->getY(), $thisTube->rec->getWidth(), $thisTube->rec->getHeight(), BasicColors::gray());
                Draw::rectangle($thatTube->rec->getX(), $thatTube->rec->getY(), $thatTube->rec->getWidth(), $thatTube->rec->getHeight(), BasicColors::gray());
            }

            // Draw flashing fx (one frame only)
            if ($gameState->superFx) {
                Draw::rectangle(0, 0, Window::getScreenWidth(), Window::getScreenHeight(), BasicColors::white());
                $gameState->superFx = false;
            }

            Text::draw(sprintf("SCORE: %04d", $gameState->score), 20, 20, 40, BasicColors::gray());
            Text::draw(sprintf("HI-SCORE: %04d", $gameState->highScore), 20, 70, 20, BasicColors::lightGray());

            if ($gameState->pause) {
                Text::draw("GAME PAUSED", Window::getScreenWidth() / 2 - Text::measure("GAME PAUSED", 40) / 2, Window::getScreenHeight() / 2 - 40, 40, BasicColors::gray());
            }
        } else {
            Text::draw("PRESS [ENTER] TO PLAY AGAIN", Window::getScreenWidth() / 2 - Text::measure("PRESS [ENTER] TO PLAY AGAIN", 20) / 2, Window::getScreenHeight() / 2 - 50, 20, BasicColors::gray());
        }
    }
}