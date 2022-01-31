<?php

namespace blackjack200\worldsx\command\subcommand\impl;

use blackjack200\worldsx\command\subcommand\SubCommand;
use blackjack200\worldsx\world\WorldUtil;
use pocketmine\command\CommandSender;
use pocketmine\command\utils\InvalidCommandSyntaxException;
use Throwable;

class UnloadSubCommand extends SubCommand {
	public function execute(CommandSender $sender, array $args) : void {
		if (count($args) !== 1) {
			throw new InvalidCommandSyntaxException();
		}
		$worldName = $args[0];
		if (WorldUtil::findWorldByFolderName($worldName) !== null) {
			try {
				WorldUtil::unloadWorldByFolderName($worldName);
				$this->mustSendTranslation($sender, 'command.unload.success', [$worldName]);
			} catch (Throwable $thr) {
				$this->mustSendTranslation($sender, 'command.unload.error', [$worldName, $thr->getMessage()]);
			}
		} else {
			$this->mustSendTranslation($sender, 'command.unload.unloaded', [$worldName]);
		}
	}
}