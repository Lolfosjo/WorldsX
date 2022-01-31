<?php

namespace blackjack200\worldsx\command\subcommand\impl;

use blackjack200\worldsx\command\subcommand\SubCommand;
use blackjack200\worldsx\world\WorldUtil;
use pocketmine\command\CommandSender;
use pocketmine\command\utils\InvalidCommandSyntaxException;
use Throwable;

class DuplicateSubCommand extends SubCommand {
	public function execute(CommandSender $sender, array $args) : void {
		if (count($args) !== 2) {
			throw new InvalidCommandSyntaxException();
		}
		[$orig, $dest] = $args;
		if (!WorldUtil::contains($orig)) {
			$this->mustSendTranslation($sender, 'command.duplicate.not-exists', [$orig]);
			return;
		}
		if (WorldUtil::contains($dest)) {
			$this->mustSendTranslation($sender, 'command.duplicate.exists', [$dest]);
			return;
		}
		try {
			WorldUtil::duplicate($orig, $dest);
			$this->mustSendTranslation($sender, 'command.duplicate.success', [$orig, $dest]);
		} catch (Throwable $e) {
			$this->mustSendTranslation($sender, 'command.duplicate.error', [$orig, $dest, $e->getMessage()]);
		}
	}
}