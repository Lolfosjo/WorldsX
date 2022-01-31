<?php

namespace blackjack200\worldsx\command\subcommand\impl;

use blackjack200\worldsx\command\subcommand\SubCommand;
use blackjack200\worldsx\world\WorldUtil;
use pocketmine\command\CommandSender;

class ListSubCommand extends SubCommand {
	public function execute(CommandSender $sender, array $args) : void {
		$this->mustSendTranslation($sender, 'command.list.header');
		$worlds = WorldUtil::getWorlds();
		foreach ($worlds as $world) {
			$w = WorldUtil::findWorldByFolderName($world);
			$loaded = $w !== null;
			$cnt = 0;
			if ($w !== null) {
				$cnt = count($w->getPlayers());
			}
			$this->mustSendTranslation($sender, 'command.list.world', [
				$world,
				$loaded ? $this->mustTranslate('command.list.loaded') : $this->mustTranslate('command.list.unloaded'),
				$cnt,
			]);
		}
	}
}