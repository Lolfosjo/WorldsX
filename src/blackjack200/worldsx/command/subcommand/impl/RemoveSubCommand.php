<?php

namespace blackjack200\worldsx\command\subcommand\impl;

use blackjack200\worldsx\command\subcommand\SubCommand;
use blackjack200\worldsx\world\WorldUtil;
use pocketmine\command\CommandSender;
use pocketmine\command\utils\InvalidCommandSyntaxException;
use Throwable;

class RemoveSubCommand extends SubCommand {
	public function execute(CommandSender $sender, array $args) : void {
		if (count($args) !== 1) {
			throw new InvalidCommandSyntaxException();
		}
		$worldName = $args[0];
		if (WorldUtil::contains($worldName)) {
			try {
				WorldUtil::remove($worldName);
				$this->mustSendTranslation($sender, 'command.remove.success', [$worldName]);
			} catch (Throwable $thr) {
				$this->mustSendTranslation($sender, 'command.remove.error', [$worldName, $thr->getMessage()]);
			}
		} else {
			$this->mustSendTranslation($sender, 'command.remove.not-exists', [$worldName]);
		}
	}
}