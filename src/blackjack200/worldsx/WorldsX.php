<?php

namespace blackjack200\worldsx;

use blackjack200\worldsx\generator\VoidGenerator;
use pocketmine\plugin\PluginBase;
use pocketmine\world\generator\GeneratorManager;

class WorldsX extends PluginBase {
	protected function onEnable() : void {
		GeneratorManager::getInstance()->addGenerator(VoidGenerator::class, "void", fn() => null);
	}

	protected function onDisable() : void {

	}
}