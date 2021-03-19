<?php

namespace MallardDuck\Examples\Gorillas;

use DI\Container;
use MallardDuck\Examples\Gorillas\Entities\Building;
use MallardDuck\Examples\Gorillas\Entities\Explosion;
use MallardDuck\Examples\Gorillas\Entities\Player;
use MallardDuck\RayLibCore\BasicColors;
use MallardDuck\RayLibCore\GameState\GlobalGameState;
use MallardDuck\RayLibCore\Kernel;
use raylib\Collision;
use raylib\Draw;
use raylib\Input\Key;
use raylib\Input\Mouse;
use raylib\Rectangle;
use raylib\Text;
use raylib\Vector2;
use raylib\Window;

class GorillasKernel extends Kernel
{
    public const FLOPPY_RADIUS = 23;
    public const TUBES_WIDTH = 80;
    public const MAX_TUBES = 100;

    public function __construct() {
        parent::__construct('Gorillas - Sample Game');
    }

    public function getStateFromClass(): string
    {
        return GlobalGorillasState::class;
    }

    protected function initContainer(Container $container): ?Container
    {
        $container->set(BasicColors::class, BasicColors::getInstance());
        return $container;
    }

    /**
     * @param GlobalGameState|GlobalGorillasState $gameState
     */
    public function bootGameState(GlobalGameState $gameState)
    {
        if ($gameState->gameOver) {
            $gameState->gameOver = false;
        }

        $gameState->ball->radius = 10;
        $gameState->ballInAir = false;
        $gameState->ball->active = false;

        $gameState->buildings = Building::initBuildings($gameState->screenWidth, $gameState->screenHeight);
        $gameState->players = Player::initPlayers();

        $gameState->explosions = Explosion::initExplosions();
    }

    public function physicsProcess(int $delta)
    {
        /**
         * @var GlobalGorillasState $gameState
         */
        $gameState = game_state();

        if (!$gameState->gameOver) {
            if (Key::isPressed(Key::P)) {
                $gameState->pause = !$gameState->pause;
            }

            if (!$gameState->pause) {
                if (!$gameState->ballInAir) { // If we are aiming
                    $gameState->ballInAir = $this->updatePlayer($gameState->playerTurn);
                } else {
                    // If collision
                    if ($this->updateBall($gameState->playerTurn)) {
                        // Game over logic
                        $leftTeamAlive = false;
                        $rightTeamAlive = false;

                        for ($i = 0; $i < $gameState::MAX_PLAYERS; $i++) {
                            if ($gameState->players[$i]->isAlive) {
                                if ($gameState->players[$i]->isLeftTeam) {
                                    $leftTeamAlive = true;
                                }
                                if (!$gameState->players[$i]->isLeftTeam) {
                                    $rightTeamAlive = true;
                                }
                            }
                        }

                        if ($leftTeamAlive && $rightTeamAlive) {
                            $gameState->ballInAir = false;
                            $gameState->ball->active = false;

                            $gameState->playerTurn++;

                            if ($gameState->playerTurn == $gameState::MAX_PLAYERS) {
                                $gameState->playerTurn = 0;
                            }
                        } else {
                            $gameState->gameOver = true;

                            // if (leftTeamAlive) left team wins
                            // if (rightTeamAlive) right team wins
                        }
                    }
                }
            }
        } else {
            if (Key::isPressed(Key::ENTER)) {
                $this->bootGameState($gameState);
            }
        }

    }

    public function process(int $delta)
    {
        /**
         * @var GlobalGorillasState $gameState
         */
        $gameState = game_state();
        $playerTurn = &$gameState->playerTurn;

        if (!$gameState->gameOver) {
            // Draw buildings
            for ($i = 0; $i < $gameState::MAX_BUILDINGS; $i++) {
                Draw::rectangleRec($gameState->buildings[$i]->rectangle, $gameState->buildings[$i]->color);
            }

            // Draw explosions
            for ($i = 0; $i < $gameState::MAX_EXPLOSIONS; $i++) {
                if ($gameState->explosions[$i]->active) Draw::circle($gameState->explosions[$i]->position->getX(), $gameState->explosions[$i]->position->getY(), $gameState->explosions[$i]->radius, BasicColors::rayWhite());
            }

            // Draw players
            for ($i = 0; $i < $gameState::MAX_PLAYERS; $i++) {
                if ($gameState->players[$i]->isAlive) {
                    if ($gameState->players[$i]->isLeftTeam) {
                        Draw::rectangle(
                            $gameState->players[$i]->position->getX() - $gameState->players[$i]->size->getX() / 2,
                            $gameState->players[$i]->position->getY() - $gameState->players[$i]->size->getY() / 2,
                            $gameState->players[$i]->size->getX(),
                            $gameState->players[$i]->size->getY(),
                            BasicColors::blue()
                        );
                    } else {
                        Draw::rectangle(
                            $gameState->players[$i]->position->getX() - $gameState->players[$i]->size->getX() / 2,
                            $gameState->players[$i]->position->getY() - $gameState->players[$i]->size->getY() / 2,
                            $gameState->players[$i]->size->getX(),
                            $gameState->players[$i]->size->getY(),
                            BasicColors::red()
                        );
                    }
                }
            }

            // Draw ball
            if ($gameState->ball->active) {
                Draw::circle($gameState->ball->position->getX(), $gameState->ball->position->getY(), $gameState->ball->radius, BasicColors::maroon());
            }

            // Draw the angle and the power of the aim, and the previous ones
            if (!$gameState->ballInAir) {
                // Draw shot information
                if ($gameState->players[$playerTurn]->isLeftTeam)
                {
                    Text::draw(sprintf("Previous Point %d, %d", (int)$gameState->players[$playerTurn]->previousPoint->x, (int)$gameState->players[$playerTurn]->previousPoint->y), 20, 20, 20, BasicColors::darkBlue());
                    Text::draw(sprintf("Previous Angle %d", $gameState->players[$playerTurn]->previousAngle), 20, 50, 20, BasicColors::darkBlue());
                    Text::draw(sprintf("Previous Power %d", $gameState->players[$playerTurn]->previousPower), 20, 80, 20, BasicColors::darkBlue());
                    Text::draw(sprintf("Aiming Point %d, %d", (int)$gameState->players[$playerTurn]->aimingPoint->x, (int)$gameState->players[$playerTurn]->aimingPoint->y), 20, 110, 20, BasicColors::darkBlue());
                    Text::draw(sprintf("Aiming Angle %d", $gameState->players[$playerTurn]->aimingAngle), 20, 140, 20, BasicColors::darkBlue());
                    Text::draw(sprintf("Aiming Power %d", $gameState->players[$playerTurn]->aimingPower), 20, 170, 20, BasicColors::darkBlue());
                }
                else
                {
                    Text::draw(sprintf("Previous Point %d, %d", (int)$gameState->players[$playerTurn]->previousPoint->x, (int)$gameState->players[$playerTurn]->previousPoint->y), $gameState->screenWidth * 3/4, 20, 20, BasicColors::darkBlue());
                    Text::draw(sprintf("Previous Angle %d", $gameState->players[$playerTurn]->previousAngle), $gameState->screenWidth * 3/4, 50, 20, BasicColors::darkBlue());
                    Text::draw(sprintf("Previous Power %d", $gameState->players[$playerTurn]->previousPower), $gameState->screenWidth * 3/4, 80, 20, BasicColors::darkBlue());
                    Text::draw(sprintf("Aiming Point %d, %d", (int)$gameState->players[$playerTurn]->aimingPoint->x, (int)$gameState->players[$playerTurn]->aimingPoint->y), $gameState->screenWidth * 3/4, 110, 20, BasicColors::darkBlue());
                    Text::draw(sprintf("Aiming Angle %d", $gameState->players[$playerTurn]->aimingAngle), $gameState->screenWidth * 3/4, 140, 20, BasicColors::darkBlue());
                    Text::draw(sprintf("Aiming Power %d", $gameState->players[$playerTurn]->aimingPower), $gameState->screenWidth * 3/4, 170, 20, BasicColors::darkBlue());
                }

                // Draw aim
                if ($gameState->players[$playerTurn]->isLeftTeam) {
                    // Previous aiming
                    Draw::triangle(
                        new Vector2($gameState->players[$playerTurn]->position->getX() - $gameState->players[$playerTurn]->size->getX() / 4, $gameState->players[$playerTurn]->position->getY() - $gameState->players[$playerTurn]->size->getY() / 4),
                        new Vector2($gameState->players[$playerTurn]->position->getX() + $gameState->players[$playerTurn]->size->getX() / 4, $gameState->players[$playerTurn]->position->getY() + $gameState->players[$playerTurn]->size->getY() / 4),
                        $gameState->players[$playerTurn]->previousPoint,
                        BasicColors::gray()
                    );

                    // Actual aiming
                    Draw::triangle(
                        new Vector2($gameState->players[$playerTurn]->position->getX() - $gameState->players[$playerTurn]->size->getX() / 4, $gameState->players[$playerTurn]->position->getY() - $gameState->players[$playerTurn]->size->getY() / 4),
                        new Vector2($gameState->players[$playerTurn]->position->getX() + $gameState->players[$playerTurn]->size->getX() / 4, $gameState->players[$playerTurn]->position->getY() + $gameState->players[$playerTurn]->size->getY() / 4),
                        $gameState->players[$playerTurn]->aimingPoint,
                        BasicColors::darkBlue()
                    );
                } else {
                    // Previous aiming
                    Draw::triangle(new Vector2($gameState->players[$playerTurn]->position->getX() - $gameState->players[$playerTurn]->size->getX() / 4, $gameState->players[$playerTurn]->position->getY() + $gameState->players[$playerTurn]->size->getY() / 4),
                        new Vector2($gameState->players[$playerTurn]->position->getX() + $gameState->players[$playerTurn]->size->getX() / 4, $gameState->players[$playerTurn]->position->getY() - $gameState->players[$playerTurn]->size->getY() / 4),
                        $gameState->players[$playerTurn]->previousPoint, BasicColors::gray());

                    // Actual aiming
                    Draw::triangle(new Vector2($gameState->players[$playerTurn]->position->getX() - $gameState->players[$playerTurn]->size->getX() / 4, $gameState->players[$playerTurn]->position->getY() + $gameState->players[$playerTurn]->size->getY() / 4),
                        new Vector2($gameState->players[$playerTurn]->position->getX() + $gameState->players[$playerTurn]->size->getX() / 4, $gameState->players[$playerTurn]->position->getY() - $gameState->players[$playerTurn]->size->getY() / 4),
                        $gameState->players[$playerTurn]->aimingPoint, BasicColors::maroon());
                }
            }

            if ($gameState->pause) {
                Text::draw("GAME PAUSED", $gameState->screenWidth / 2 - Text::measure("GAME PAUSED", 40) / 2, $gameState->screenHeight / 2 - 40, 40, BasicColors::gray());
            }
        } else {
            Text::draw("PRESS [ENTER] TO PLAY AGAIN", Window::getScreenWidth() / 2 - Text::measure("PRESS [ENTER] TO PLAY AGAIN", 20) / 2, Window::getScreenHeight() / 2 - 50, 20, BasicColors::gray());
        }

        Text::drawFps(0, 0);
    }

    private function updatePlayer(int $playerTurn): bool
    {
        /**
         * @var GlobalGorillasState $gameState
         */
        $gameState = game_state();

        $mousePos = Mouse::getPosition();

        /**
         * The player who's turn it is
         */
        $currentPlayer = &$gameState->players[$playerTurn];

        /**
         * The position of the current player
         */
        $position = $currentPlayer->position;

        // If we are aiming at the firing quadrant, we calculate the angle
        if ($mousePos->getY() <= $position->getY()) {
            // Left team
            if ($currentPlayer->isLeftTeam && $mousePos->getX() >= $position->getX()) {
                // Distance (calculating the fire power)
                $currentPlayer->aimingPower = sqrt(pow($position->getX() - $mousePos->getX(), 2) + pow($position->getY() - $mousePos->getY(), 2));
                // Calculates the angle via arcsin
                $currentPlayer->aimingAngle = asin(($position->getY() - $mousePos->getY()) / $currentPlayer->aimingPower) * $gameState::RAD2DEG;
                // Point of the screen we are aiming at
                $currentPlayer->aimingPoint = $mousePos;

                // Ball fired
                if (Mouse::isButtonPressed(Mouse::LEFT_BUTTON)) {
                    $currentPlayer->previousPoint = $currentPlayer->aimingPoint;
                    $currentPlayer->previousPower = $currentPlayer->aimingPower;
                    $currentPlayer->previousAngle = $currentPlayer->aimingAngle;
                    $gameState->ball->position = clone $position;

                    return true;
                }
            } // Right team
            elseif (!$currentPlayer->isLeftTeam && $mousePos->getX() <= $position->getX()) {
                // Distance (calculating the fire power)
                $currentPlayer->aimingPower = (int) sqrt(pow($position->getX() - $mousePos->getX(), 2) + pow($position->getY() - $mousePos->getY(), 2));
                // Calculates the angle via arcsin
                $currentPlayer->aimingAngle = (int) (asin(($position->getY() - $mousePos->getY()) / $currentPlayer->aimingPower) * $gameState::RAD2DEG);
                // Point of the screen we are aiming at
                $currentPlayer->aimingPoint = clone $mousePos;

                // Ball fired
                if (Mouse::isButtonPressed(Mouse::LEFT_BUTTON)) {
                    $currentPlayer->previousPoint = $currentPlayer->aimingPoint;
                    $currentPlayer->previousPower = $currentPlayer->aimingPower;
                    $currentPlayer->previousAngle = $currentPlayer->aimingAngle;
                    $gameState->ball->position = clone $position;

                    return true;
                }
            } else {
                $currentPlayer->aimingPoint = clone $position;
                $currentPlayer->aimingPower = 0;
                $currentPlayer->aimingAngle = 0;
            }
        } else {
            $currentPlayer->aimingPoint = clone $position;
            $currentPlayer->aimingPower = 0;
            $currentPlayer->aimingAngle = 0;
        }

        return false;
    }

    private function updateBall(int $playerTurn): bool
    {
        /**
         * @var GlobalGorillasState $gameState
         */
        $gameState = game_state();

        static $explosionNumber = 0;
        $currentPlayer = &$gameState->players[$gameState->playerTurn];

        // Activate ball
        if (!$gameState->ball->active) {
            if ($currentPlayer->isLeftTeam) {
                $gameState->ball->speed->setX(cos($currentPlayer->previousAngle * $gameState::DEG2RAD) * $currentPlayer->previousPower * 3 / $gameState::DELTA_FPS);
                $gameState->ball->speed->setY(-1 * sin($currentPlayer->previousAngle * $gameState::DEG2RAD) * $currentPlayer->previousPower * 3 / $gameState::DELTA_FPS);
                $gameState->ball->active = true;
            } else {
                $gameState->ball->speed->setX(-1 * cos($currentPlayer->previousAngle * $gameState::DEG2RAD) * $currentPlayer->previousPower * 3 / $gameState::DELTA_FPS);
                $gameState->ball->speed->setY(-1 * sin($currentPlayer->previousAngle * $gameState::DEG2RAD) * $currentPlayer->previousPower * 3 / $gameState::DELTA_FPS);
                $gameState->ball->active = true;
            }
        }

        $gameState->ball->position->setX($gameState->ball->position->getX() + $gameState->ball->speed->getX());
        $gameState->ball->position->setY($gameState->ball->position->getY() + $gameState->ball->speed->getY());
        $gameState->ball->speed->setY($gameState->ball->speed->getY() + $gameState::GRAVITY / $gameState::DELTA_FPS);

        // Collision
        if ($gameState->ball->position->getX() + $gameState->ball->radius < 0) {
            echo "$gameState->playerTurn's ball fell out of screen - 0\n";
            return true;
        } elseif ($gameState->ball->position->getX() - $gameState->ball->radius > $gameState->screenWidth) {
            echo "$gameState->playerTurn's ball fell out of screen - $gameState->screenWidth\n";
            return true;
        } else {
            // Player collision
            for ($i = 0; $i < $gameState::MAX_PLAYERS; $i++) {
                if (Collision::checkCircleRec(
                    $gameState->ball->position,
                    $gameState->ball->radius,
                    new Rectangle(
                        $gameState->players[$i]->position->getX() - $gameState->players[$i]->size->getX() / 2,
                        $gameState->players[$i]->position->getY() - $gameState->players[$i]->size->getY() / 2,
                        $gameState->players[$i]->size->getX(),
                        $gameState->players[$i]->size->getY()
                    )
                )) {
                    // We can't hit ourselves
                    if ($i == $gameState->playerTurn) {
                        return false;
                    } else {
                        // We set the impact point
                        $currentPlayer->impactPoint->setX($gameState->ball->position->getX());
                        $currentPlayer->impactPoint->setY($gameState->ball->position->getY() + $gameState->ball->radius);

                        // We destroy the player
                        $gameState->players[$i]->isAlive = false;
                        return true;
                    }
                }
            }

            // Building collision
            // NOTE: We only check building collision if we are not inside an explosion
            for ($i = 0; $i < $gameState::MAX_BUILDINGS; $i++) {
                if (Collision::checkCircles($gameState->ball->position, $gameState->ball->radius, $gameState->explosions[$i]->position, $gameState->explosions[$i]->radius - $gameState->ball->radius)) {
                    return false;
                }
            }

            for ($i = 0; $i < $gameState::MAX_BUILDINGS; $i++) {
                if (Collision::checkCircleRec($gameState->ball->position, $gameState->ball->radius, $gameState->buildings[$i]->rectangle)) {
                    // We set the impact point
                    $currentPlayer->impactPoint->setX($gameState->ball->position->getX());
                    $currentPlayer->impactPoint->setY($gameState->ball->position->getY() + $gameState->ball->radius);

                    // We create an explosion
                    $gameState->explosions[$explosionNumber]->position = clone $currentPlayer->impactPoint;
                    $gameState->explosions[$explosionNumber]->active = true;

                    $explosionNumber++;

                    return true;
                }
            }
        }

        return false;
    }
}