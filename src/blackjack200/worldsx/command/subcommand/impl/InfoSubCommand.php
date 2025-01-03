<?php

namespace blackjack200\worldsx\command\subcommand\impl;

use blackjack200\worldsx\command\subcommand\SubCommand;
use blackjack200\worldsx\world\WorldUtil;
use pocketmine\command\CommandSender;
use pocketmine\command\utils\InvalidCommandSyntaxException;
use pocketmine\player\Player;

class InfoSubCommand extends SubCommand {
	public function execute(CommandSender $sender, array $args) : void {
		if (count($args) !== 1) {
			throw new InvalidCommandSyntaxException();
		}
		$worldName = $args[0];
		$world = WorldUtil::findWorldByFolderName($worldName);
		if ($world !== null) {
			$players = array_map(static fn(Player $player) => $player->getName(), $world->getPlayers());
			$this->mustSendTranslation($sender, 'command.info.folder-name', [
				$world->getFolderName(),
			]);
			$this->mustSendTranslation($sender, 'command.info.display-name', [
				$world->getDisplayName(),
			]);
			$this->mustSendTranslation($sender, 'command.info.players', [
				count($world->getPlayers()),
				implode(', ', $players),
			]);
			$this->mustSendTranslation($sender, 'command.info.seed', [
				$world->getSeed(),
			]);
			$this->mustSendTranslation($sender, 'command.info.generator', [
				$world->getProvider()->getWorldData()->get()->getGenerator(),
			]);
			$this->mustSendTranslation($sender, 'command.info.time', [
				$world->getTime(),
			]);
		} else {
			$this->mustSendTranslation($sender, 'command.info.not-exists', [
				$worldName,
			]);
		}
	}
}