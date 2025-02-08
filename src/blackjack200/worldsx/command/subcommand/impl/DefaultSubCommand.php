<?php

namespace blackjack200\worldsx\command\subcommand\impl;

use blackjack200\worldsx\command\subcommand\SubCommand;
use blackjack200\worldsx\world\WorldUtil;
use pocketmine\command\CommandSender;
use pocketmine\command\utils\InvalidCommandSyntaxException;

class DefaultSubCommand extends SubCommand {
	public function execute(CommandSender $sender, array $args) : void {
		if (count($args) !== 1) {
			throw new InvalidCommandSyntaxException();
		}
		$worldName = $args[0];
		if (!WorldUtil::contains($worldName)) {
			$this->mustSendTranslation($sender, "command.default.not-exists", [$worldName]);
			return;
		}
		WorldUtil::updateDefaultWorld($worldName, true);
		$this->mustSendTranslation($sender, "command.default.success", [$worldName]);
	}
}