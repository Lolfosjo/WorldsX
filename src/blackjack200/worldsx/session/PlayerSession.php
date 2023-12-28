<?php

namespace blackjack200\worldsx\session;

use blackjack200\worldsx\world\GameRuleCollection;
use pocketmine\network\mcpe\protocol\GameRulesChangedPacket;
use pocketmine\player\Player;

class PlayerSession {
	public Player $player;

	public function __construct(Player $player) {
		$this->player = $player;
	}

	public function sendGameRules(?GameRuleCollection $rules = null) : void {
		if ($rules === null) {
			$rules = WorldGameRules::mustGetGameRuleCollection($this->player->getWorld());
		}
		$pk = GameRulesChangedPacket::create($rules->toGameRules());
		$this->player->getNetworkSession()->sendDataPacket($pk);
	}
}