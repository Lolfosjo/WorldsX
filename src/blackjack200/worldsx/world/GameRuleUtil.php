<?php

namespace blackjack200\worldsx\world;

use blackjack200\worldsx\session\WorldGameRules;
use blackjack200\worldsx\world\types\GameRuleMapping;
use pocketmine\network\mcpe\protocol\GameRulesChangedPacket;
use pocketmine\player\Player;
use pocketmine\world\format\io\data\BaseNbtWorldData;

class GameRuleUtil {
	public static function parse(BaseNbtWorldData $data) : ?GameRuleCollection {
		$rules = $data->getCompoundTag()->getCompoundTag("GameRules");
		if ($rules === null) {
			$rules = GameRuleMapping::getDefaultTags();
			$data->getCompoundTag()->setTag('GameRules', $rules);
			$data->save();
		}
		return GameRuleCollection::from($rules);
	}

	public static function save(BaseNbtWorldData $data, GameRuleCollection $rules) : void {
		$data->getCompoundTag()->setTag('GameRules', $rules->toCompoundTag());
		$data->save();
	}

	public static function send(Player $player, ?GameRuleCollection $rules = null) : void {
		if ($rules === null) {
			$rules = WorldGameRules::mustGetGameRuleCollection($player->getWorld());
		}
		$pk = GameRulesChangedPacket::create($rules->toGameRules());
		$player->getNetworkSession()->sendDataPacket($pk);
	}
}