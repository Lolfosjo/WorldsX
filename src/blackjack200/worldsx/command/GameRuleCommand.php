<?php

namespace blackjack200\worldsx\command;

use blackjack200\worldsx\lang\Language;
use blackjack200\worldsx\session\WorldGameRules;
use blackjack200\worldsx\session\WorldsXListener;
use blackjack200\worldsx\world\GameRuleUtil;
use blackjack200\worldsx\world\types\GameRuleMapping;
use blackjack200\worldsx\world\WorldUtil;
use blackjack200\worldsx\WorldsX;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\Plugin;
use pocketmine\plugin\PluginOwned;
use pocketmine\Server;
use pocketmine\world\format\io\data\BaseNbtWorldData;

class GameRuleCommand extends Command implements PluginOwned {
	private Language $lang;
	private WorldsXListener $listener;
	private WorldsX $plugin;

	public function __construct(Language $lang, WorldsXListener $listener, WorldsX $plugin) {
		$this->lang = $lang;
		$this->listener = $listener;
		$this->plugin = $plugin;
		parent::__construct('gamerule', 'GameRules', '/gamerule <name> <value> <world>', ['gr']);
		$this->setPermission('worldsx.gamerule');
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args) {
		if ($this->testPermission($sender)) {
			if (count($args) < 2) {
				$sender->sendMessage($this->lang->translateString('command.gamerule.usage'));
				return;
			}
			$worldName = $args[2] ?? null;
			if ($worldName === null) {
				if ($sender instanceof Player) {
					$worldName = $sender->getWorld()->getFolderName();
				} else {
					$worldName = Server::getInstance()->getWorldManager()->getDefaultWorld()->getFolderName();
				}
			}

			[$name, $value] = $args;
			$world = WorldUtil::findWorldByFolderName($worldName, true);
			if ($world === null) {
				$sender->sendMessage($this->lang->translateString('command.gamerule.world-not-exists', [$worldName]));
				return;
			}
			$internal = GameRuleMapping::toInternal($name);
			if ($internal === null) {
				$sender->sendMessage($this->lang->translateString('command.gamerule.rule-not-exists', [$name]));
				return;
			}
			$g = WorldGameRules::mustGetGameRuleCollection($world);
			$g->set($internal, $value);
			$data = $world->getProvider()->getWorldData();
			$args[] = $world->getFolderName();
			if ($data instanceof BaseNbtWorldData) {
				GameRuleUtil::save($data, $g);
				$this->listener->syncGameRules($world);
				$sender->sendMessage($this->lang->translateString('command.gamerule.success', $args));
			} else {
				$sender->sendMessage($this->lang->translateString('command.gamerule.error', $args));
			}
		}
	}

	public function getOwningPlugin() : Plugin {
		return $this->plugin;
	}
}