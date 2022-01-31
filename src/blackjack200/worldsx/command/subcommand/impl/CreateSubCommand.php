<?php

namespace blackjack200\worldsx\command\subcommand\impl;

use blackjack200\worldsx\command\subcommand\SubCommand;
use blackjack200\worldsx\world\WorldUtil;
use pocketmine\command\CommandSender;
use pocketmine\command\utils\InvalidCommandSyntaxException;
use pocketmine\utils\Utils;
use pocketmine\world\generator\GeneratorManager;
use pocketmine\world\WorldCreationOptions;

class CreateSubCommand extends SubCommand {
	public function execute(CommandSender $sender, array $args) : void {
		if (count($args) < 1) {
			throw new InvalidCommandSyntaxException();
		}
		$worldName = $args[0];
		$generatorName = $args[1] ?? "default";
		$seed = Utils::javaStringHash($args[2] ?? random_bytes(16));
		if (WorldUtil::contains($worldName)) {
			$this->mustSendTranslation($sender, "command.create.exists", [$worldName]);
			return;
		}
		$generator = GeneratorManager::getInstance()->getGenerator($generatorName);
		if ($generator === null) {
			$this->mustSendTranslation($sender, "command.create.generator-not-found", [$generatorName]);
			return;
		}
		if (WorldUtil::create($worldName, WorldCreationOptions::create()
			->setGeneratorClass($generator->getGeneratorClass())
			->setSeed($seed)
		)) {
			$this->mustSendTranslation($sender, "command.create.success", [$worldName, $generator->getGeneratorClass(), $seed]);
		} else {
			$this->mustSendTranslation($sender, "command.create.failed", [$worldName]);
		}
	}
}