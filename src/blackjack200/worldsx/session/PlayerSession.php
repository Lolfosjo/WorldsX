<?php

namespace blackjack200\worldsx\session;

use blackjack200\worldsx\world\GameRuleCollection;
use pocketmine\network\mcpe\protocol\GameRulesChangedPacket;
use pocketmine\player\Player;

class PlayerSession {
	public Player $player;
	public GameRuleCollection $collection;

	public function __construct(Player $player, GameRuleCollection $collection) {
		$this->collection = $collection;
		$this->player = $player;
	}

	public function syncGameRules(GameRuleCollection $collection) : void {
		$this->collection = $collection;
		$this->sendGameRules();
	}

	public function sendGameRules() : void {
		$pk = GameRulesChangedPacket::create($this->collection->toGameRules());
		$this->player->getNetworkSession()->sendDataPacket($pk);
	}
}