<?php

namespace blackjack200\worldsx\command;

use blackjack200\worldsx\command\subcommand\impl\CreateSubCommand;
use blackjack200\worldsx\command\subcommand\impl\DuplicateSubCommand;
use blackjack200\worldsx\command\subcommand\impl\HelpSubCommand;
use blackjack200\worldsx\command\subcommand\impl\InfoSubCommand;
use blackjack200\worldsx\command\subcommand\impl\ListSubCommand;
use blackjack200\worldsx\command\subcommand\impl\LoadSubCommand;
use blackjack200\worldsx\command\subcommand\impl\RemoveSubCommand;
use blackjack200\worldsx\command\subcommand\impl\RenameSubCommand;
use blackjack200\worldsx\command\subcommand\impl\UnloadSubCommand;
use blackjack200\worldsx\command\subcommand\SubCommand;
use blackjack200\worldsx\lang\Language;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\utils\InvalidCommandSyntaxException;

class WorldsXCommand extends Command {
	/** @var SubCommand[] */
	private array $subCommands = [];
	private Language $lang;

	public function __construct(Language $lang) {
		parent::__construct('worldsx', 'WorldsX command', '/wx', ['mw', 'wx']);
		$this->setPermission('worldsx.command');
		$this->lang = $lang;
		$this->registerSubCommand('help', HelpSubCommand::class, ['h']);
		$this->registerSubCommand('info', InfoSubCommand::class, ['i']);
		$this->registerSubCommand('load', LoadSubCommand::class, ['l', 'ld']);
		$this->registerSubCommand('unload', UnloadSubCommand::class, ['u', 'un']);
		$this->registerSubCommand('list', ListSubCommand::class, ['ls']);
		$this->registerSubCommand('create', CreateSubCommand::class, ['c', 'new']);
		$this->registerSubCommand('remove', RemoveSubCommand::class, ['rm', 'delete', 'del']);
		$this->registerSubCommand('duplicate', DuplicateSubCommand::class, ['dup']);
		$this->registerSubCommand('rename', RenameSubCommand::class);
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args) {
		if ($this->testPermission($sender)) {
			if (count($args) !== 0) {
				$cmd = mb_strtolower($args[0]);
				$subcommand = $this->subCommands[$cmd] ?? null;
				if ($subcommand !== null) {
					array_shift($args);
					try {
						$subcommand->execute($sender, $args);
					} catch (InvalidCommandSyntaxException) {
						$subcommand->sendUsage($sender, $cmd);
					}
				} else {
					$sender->sendMessage($this->lang->translateString('command.not-found', [$cmd]));
				}
			} else {
				$sender->sendMessage($this->lang->translateString('command.help'));
			}
		}
	}

	protected function registerSubCommand(string $name, string $subCommandClass, array $alias = []) : void {
		$this->subCommands[$name] = new $subCommandClass($name, $this->lang);
		foreach ($alias as $a) {
			$this->subCommands[$a] = $this->subCommands[$name];
		}
	}
}