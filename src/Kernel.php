<?php

namespace MallardDuck\RayLibCore;

use DI\Container;
use MallardDuck\RayLibCore\GameState\GlobalGameState;
use raylib\Draw;
use raylib\Timming;
use raylib\Window;

abstract class Kernel
{
    private static ?Kernel $instance = null;
    private static string $name = '';

    private Container $container;
    protected int $startTime;
    protected int $currentTime;
    protected int $lastTicks = 0;
    protected int $frames = 0;
    protected int $frame = 0;
    protected int $iterating = 0;
    protected bool $forceRedrawRequested = false;
    protected static int $physicsProcessMax = 0;
    protected static int $processMax = 0;

    public int $screenWidth = 800;
    public int $screenHeight = 450;

    protected function __construct(
        protected string $title,
    ) {
        $this->container = $this->buildContainer();
        $this->startTime = $this->currentTime = time();
        Window::init(800, 600, $this->title);
        Timming::setTargetFps(60);

        if (self::$instance == null) {
            self::$instance = $this;
        }
    }

    public static function setName(string $name)
    {
        self::$name = $name;
    }

    public static function getInstance(): Kernel
    {
        return self::$instance;
    }

    public function make(string $abstractName)
    {
        return $this->container->get($abstractName);
    }

    private function buildContainer(): Container
    {
        $container = new Container();
        $container->set(GlobalGameState::class, \DI\create($this->getStateFromClass()));
        $container = $this->initContainer($container) ?? $container;
        return $container;
    }

    protected function initContainer(Container $container): ?Container
    {
        return null;
    }

    public function __deconstruct()
    {
        Window::close();
    }

    // Helpers...
    private function getDeltaTime(): int
    {
        return time() - $this->currentTime;
    }

    public function getGameState(): GlobalGameState
    {
        return $this->container->get(GlobalGameState::class);
    }

    // The main loop logic lives here
    public function run()
    {
        $this->bootGameState($this->container->get(GlobalGameState::class));
        /*
         * Main loop
         */
        while (!Window::shouldClose()) {
            // Track kernel state
            $this->iterating++;

            // Update Physics, then
            if ($this->getDeltaTime() > 1) {
                echo "FPS: " . Timming::getFPS() . PHP_EOL;
                $this->currentTime = time();
            }

            // ..actual physics process..
            $this->physicsProcess($this->getDeltaTime());

            // Update the Scene graphics, then
            Draw::begin();
            Draw::clearBackground(BasicColors::rayWhite());
            $this->process($this->getDeltaTime());
            Draw::end();

            // Receive the user input and act upon it, then
            // "performance updates" (AKA mem management and culling), then
            // update App state to verify user didn't quite or game crash. (Think this is implicit to raylib)
        }
    }

    // All the game specific implementation methods follow...
    abstract public function getStateFromClass(): string;
    abstract public function bootGameState(GlobalGameState $gameState);
    abstract public function physicsProcess(int $delta);
    abstract public function process(int $delta);
}
