<?php

namespace blackjack200\worldsx\session;

use blackjack200\worldsx\world\GameRuleCollection;
use blackjack200\worldsx\world\GameRuleUtil;
use blackjack200\worldsx\world\types\DefaultGameRules;
use pocketmine\utils\AssumptionFailedError;
use pocketmine\world\format\io\data\BaseNbtWorldData;
use pocketmine\world\format\ThreadedWorldProvider;
use pocketmine\world\World;
use RuntimeException;

class WorldGameRules {
	/** @var GameRuleCollection[] */
	private static array $gameRules = [];

	public static function setupGameRules(World $w) : void {
		$data = $w->getProvider()->getWorldData();
		if (interface_exists(ThreadedWorldProvider::class)) {
			$data = $data->get();
		}
		if ($data instanceof BaseNbtWorldData) {
			$c = GameRuleUtil::parse($data);
			if ($c !== null) {
				self::$gameRules[$w->getFolderName()] = $c;
				self::applyGameRules($c, $w);
			} else {
				throw new RuntimeException('Failed to parse game rules for world ' . $w->getFolderName());
			}
		} else {
			throw new RuntimeException('World ' . $w->getFolderName() . ' is not a NBT-data-based world');
		}
	}

	public static function applyGameRules(GameRuleCollection $c, World $w) : void {
		if (!$c->get(DefaultGameRules::DO_DAYLIGHT_CYCLE)) {
			$w->stopTime();
		} else {
			$w->startTime();
		}
	}

	public static function mustGetGameRuleCollection(World $world) : GameRuleCollection {
		return self::$gameRules[$world->getFolderName()] ?? throw new AssumptionFailedError('Game rules not loaded');
	}

	public static function remove(World $world) : void {
		unset(self::$gameRules[$world->getFolderName()]);
	}
}