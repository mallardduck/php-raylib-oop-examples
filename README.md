# OOP examples for PHP Raylib

This repo contains a few simple example games for raylib ported into an OOP style structure.
These are the same games found in the extensions source code here: [raylib-php/examples/games](https://github.com/joseph-montanez/raylib-php/tree/master/examples/games).


## Purpose & Intent
The main goal of this repo was simply to play around with the Raylib extension for PHP.
The secondary goal became creating a reusable game loop (aka the Kernel) that provides an OOP api.
To that end, the project attempts to hastefully create system that mimics (although much simpler) that which Godot provides.

### How to Play
1. Install PHP 8.0
2. Install and enable the [PHP raylib extension](https://github.com/joseph-montanez/raylib-php)
3. Run: `git clone https://github.com/mallardduck/php-raylib-oop-examples.git`
4. Run: `cd php-raylib-oop-examples`
5. Pick which game you want to play: Flappy Bird style demo or Wormz style demo

FloppyBlob
```bash
php ./floppy.php
```
Gorillas
```bash
php ./gorillas.php
```

## TODO
- Complete design and implementation of the "MainLoop"
- Investigate how raylib (at a core level) manages time/frame related state.
- 