<?php

namespace blackjack200\worldsx\command\subcommand\impl;

use blackjack200\worldsx\command\subcommand\SubCommand;
use blackjack200\worldsx\world\WorldUtil;
use pocketmine\command\CommandSender;
use pocketmine\command\utils\InvalidCommandSyntaxException;
use Throwable;

class TeleportSubCommand extends SubCommand {
	public function execute(CommandSender $sender, array $args) : void {
		if (count($args) < 1) {
			throw new InvalidCommandSyntaxException();
		}
		if (count($args) === 1) {
			$args = [$args[0], $sender->getName()];
		}
		[$dst, $target] = $args;
		$targetObj = $sender->getServer()->getPlayerByPrefix($target);
		if ($targetObj === null) {
			$this->mustSendTranslation($sender, 'command.teleport.player-not-exists', [$target]);
			return;
		}
		if (!WorldUtil::contains($dst)) {
			$this->mustSendTranslation($sender, 'command.teleport.not-exists', [$dst]);
			return;
		}
		try {
			$w = WorldUtil::loadWorld($dst);
			$targetObj->teleport($w->getSpawnLocation());
			$this->mustSendTranslation($sender, 'command.teleport.success', [$dst, $target]);
		} catch (Throwable $e) {
			$this->mustSendTranslation($sender, 'command.teleport.error', [$dst, $target, $e->getMessage()]);
		}
	}
}