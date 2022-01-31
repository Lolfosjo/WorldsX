<?php

namespace blackjack200\worldsx\command\subcommand\impl;

use blackjack200\worldsx\command\subcommand\SubCommand;
use blackjack200\worldsx\world\WorldUtil;
use pocketmine\command\CommandSender;
use pocketmine\command\utils\InvalidCommandSyntaxException;
use Throwable;

class RenameSubCommand extends SubCommand {
	public function execute(CommandSender $sender, array $args) : void {
		if (count($args) !== 2) {
			throw new InvalidCommandSyntaxException();
		}
		[$orig, $dest] = $args;
		if (!WorldUtil::contains($orig)) {
			$this->mustSendTranslation($sender, 'command.rename.not-exists', [$orig]);
			return;
		}
		if (WorldUtil::contains($dest)) {
			$this->mustSendTranslation($sender, 'command.rename.exists', [$dest]);
			return;
		}
		try {
			WorldUtil::rename($orig, $dest);
			$this->mustSendTranslation($sender, 'command.rename.success', [$orig, $dest]);
		} catch (Throwable $e) {
			$this->mustSendTranslation($sender, 'command.rename.error', [$orig, $dest, $e->getMessage()]);
		}
	}
}