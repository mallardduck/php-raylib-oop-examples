<?php

namespace MallardDuck\RayLibCore;

use JetBrains\PhpStorm\Pure;
use raylib\Color;

/**
 * @method static rayWhite()
 * @method static lightGray()
 * @method static darkGray()
 * @method static maroon()
 * @method static gray()
 * @method static red()
 * @method static blue()
 * @method static white()
 * @method static gold()
 * @method static lime()
 * @method static darkBlue()
 * @method static black()
 * @method static violet()
 * @method static pink()
 */
class BasicColors
{
    private static ?BasicColors $instance = null;

    protected static Color $rayWhite;
    protected static Color $lightGray;
    protected static Color $darkGray;
    protected static Color $maroon;
    protected static Color $gray;
    protected static Color $red;
    protected static Color $blue;
    protected static Color $white;
    protected static Color $gold;
    protected static Color $lime;
    protected static Color $darkBlue;
    protected static Color $black;
    protected static Color $violet;
    protected static Color $pink;

    private function __construct() {
        self::$rayWhite = new Color(245, 245, 245, 255);
        self::$lightGray = new Color(200, 200, 200, 255);
        self::$darkGray = new Color(80, 80, 80, 255);
        self::$maroon = new Color(190, 33, 55, 255);
        self::$gray = new Color(130, 130, 130, 255);
        self::$red = new Color(230, 41, 55, 255);
        self::$blue = new Color(0, 121, 241, 255);
        self::$white = new Color(255, 255, 255, 255);
        self::$gold = new Color(255, 203, 0, 255);
        self::$lime = new Color(0, 158, 47, 255);
        self::$darkBlue = new Color(0, 82, 172, 255);
        self::$black = new Color(0, 0, 0, 255);
        self::$violet = new Color(135, 60, 190, 255);
        self::$pink = new Color(255, 109, 194, 255);
    }

    public static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new BasicColors();
        }

        return self::$instance;
    }

    #[Pure]
    public static function __callStatic(string $name, array $arguments): Color
    {
        if (property_exists(self::class, $name)) {
            return self::${$name};
        }
    }
}