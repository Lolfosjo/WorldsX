<?php

namespace blackjack200\worldsx\command\subcommand;

use blackjack200\worldsx\lang\Language;
use pocketmine\command\CommandSender;
use RuntimeException;

abstract class SubCommand {
	private Language $lang;
	private string $name;

	public function __construct(string $name, Language $lang) {
		$this->name = $name;
		$this->lang = $lang;
	}

	public function sendUsage(CommandSender $sender, string $cmd) : void {
		$msg = $this->lang->translateString("command.$this->name.usage", [$cmd]);
		if ($msg !== null) {
			$sender->sendMessage($msg);
		}
	}

	protected function mustSendTranslation(CommandSender $sender, string $key, array $args = []) : void {
		$sender->sendMessage($this->mustTranslate($key, $args));
	}

	protected function mustTranslate(string $key, array $args = []) : string {
		$msg = $this->lang->translateString($key, $args);
		if ($msg !== null) {
			return $msg;
		} else {
			throw new RuntimeException("Translation key $key not found");
		}
	}

	abstract public function execute(CommandSender $sender, array $args) : void;
}