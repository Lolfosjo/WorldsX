<?php

namespace blackjack200\worldsx\command\subcommand\impl;

use blackjack200\worldsx\command\subcommand\SubCommand;
use pocketmine\command\CommandSender;

class HelpSubCommand extends SubCommand {
	public function execute(CommandSender $sender, array $args) : void {
		$this->mustSendTranslation($sender, "command.help");
	}
}