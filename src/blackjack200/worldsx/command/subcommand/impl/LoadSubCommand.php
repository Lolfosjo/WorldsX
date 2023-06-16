<?php

namespace blackjack200\worldsx\command\subcommand\impl;

use blackjack200\worldsx\command\subcommand\SubCommand;
use blackjack200\worldsx\session\WorldGameRules;
use blackjack200\worldsx\world\WorldUtil;
use pocketmine\command\CommandSender;
use pocketmine\command\utils\InvalidCommandSyntaxException;
use Throwable;

class LoadSubCommand extends SubCommand {
	public function execute(CommandSender $sender, array $args) : void {
		if (count($args) !== 1) {
			throw new InvalidCommandSyntaxException();
		}
		$worldName = $args[0];
		if (WorldUtil::findWorldByFolderName($worldName) !== null) {
			$this->mustSendTranslation($sender, 'command.load.loaded', [$worldName]);
		} else {
			try {
				$w = WorldUtil::loadWorld($worldName);
				var_dump(WorldGameRules::mustGetGameRuleCollection($w));
				$this->mustSendTranslation($sender, 'command.load.success', [$worldName]);
			} catch (Throwable $thr) {
				$this->mustSendTranslation($sender, 'command.load.error', [$worldName, $thr->getMessage()]);
			}
		}
	}
}