<?php

namespace blackjack200\worldsx\command;

use blackjack200\worldsx\command\subcommand\impl\CreateSubCommand;
use blackjack200\worldsx\command\subcommand\impl\DefaultSubCommand;
use blackjack200\worldsx\command\subcommand\impl\DuplicateSubCommand;
use blackjack200\worldsx\command\subcommand\impl\HelpSubCommand;
use blackjack200\worldsx\command\subcommand\impl\InfoSubCommand;
use blackjack200\worldsx\command\subcommand\impl\ListSubCommand;
use blackjack200\worldsx\command\subcommand\impl\LoadSubCommand;
use blackjack200\worldsx\command\subcommand\impl\RemoveSubCommand;
use blackjack200\worldsx\command\subcommand\impl\RenameSubCommand;
use blackjack200\worldsx\command\subcommand\impl\TeleportSubCommand;
use blackjack200\worldsx\command\subcommand\impl\UnloadSubCommand;
use blackjack200\worldsx\command\subcommand\SubCommand;
use blackjack200\worldsx\lang\Language;
use blackjack200\worldsx\WorldsX;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\utils\InvalidCommandSyntaxException;
use pocketmine\plugin\Plugin;
use pocketmine\plugin\PluginOwned;

class WorldsXCommand extends Command implements PluginOwned {
	/** @var SubCommand[] */
	private array $subCommands = [];
	private Language $lang;
	private WorldsX $plugin;

	public function __construct(Language $lang, WorldsX $plugin) {
		parent::__construct('worldsx', 'WorldsX command', '/wx', ['mw', 'wx']);
		$this->setPermission('worldsx.command');
		$this->lang = $lang;
		$this->plugin = $plugin;
		$this->registerSubCommand('help', HelpSubCommand::class, ['h']);
		$this->registerSubCommand('info', InfoSubCommand::class, ['i']);
		$this->registerSubCommand('load', LoadSubCommand::class, ['l', 'ld']);
		$this->registerSubCommand('unload', UnloadSubCommand::class, ['u', 'un']);
		$this->registerSubCommand('list', ListSubCommand::class, ['ls']);
		$this->registerSubCommand('create', CreateSubCommand::class, ['c', 'new']);
		$this->registerSubCommand('remove', RemoveSubCommand::class, ['rm', 'delete', 'del']);
		$this->registerSubCommand('duplicate', DuplicateSubCommand::class, ['dup']);
		$this->registerSubCommand('rename', RenameSubCommand::class);
		$this->registerSubCommand('teleport', TeleportSubCommand::class, ['t', 'tp']);
		$this->registerSubCommand('default', DefaultSubCommand::class, ['d']);
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

	public function getOwningPlugin() : Plugin {
		return $this->plugin;
	}
}