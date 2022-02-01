<?php

namespace blackjack200\worldsx;

use blackjack200\worldsx\command\GameRuleCommand;
use blackjack200\worldsx\command\WorldsXCommand;
use blackjack200\worldsx\generator\VoidGenerator;
use blackjack200\worldsx\lang\Language;
use blackjack200\worldsx\session\WorldsXListener;
use blackjack200\worldsx\world\types\GameRuleParser;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;
use pocketmine\world\generator\GeneratorManager;
use RuntimeException;
use Webmozart\PathUtil\Path;

class WorldsX extends PluginBase {
	protected function onEnable() : void {
		$this->saveDefaultConfig();
		$language = $this->setupLanguage();
		GeneratorManager::getInstance()->addGenerator(VoidGenerator::class, "void", fn() => null);
		if (Server::getInstance()->getPluginManager()->getPlugin('MultiWorld') !== null) {
			$this->getLogger()->warning($language->translateString('multiworld.warning') ?? throw new RuntimeException());
			$this->getServer()->getPluginManager()->disablePlugin($this);
			return;
		}
		GameRuleParser::setup(
			json_decode(stream_get_contents($this->getResource('gamerule_name_map.json')), true),
			json_decode(stream_get_contents($this->getResource('internal_gamerule_map.json')), true),
		);
		$listener = new WorldsXListener();
		$this->getServer()->getPluginManager()->registerEvents($listener, $this);
		$this->getServer()->getCommandMap()->register('wx', new WorldsXCommand($language));
		$this->getServer()->getCommandMap()->register('gamerule', new GameRuleCommand($language, $listener));
	}

	protected function onDisable() : void {

	}

	protected function setupLanguage() : Language {
		$languages = [
			'en_US',
			'zh_CN',
		];
		foreach ($languages as $lang) {
			$this->saveResource("lang/$lang.yml", true);
		}
		$selectedLanguage = $this->getConfig()->get('language', 'en_US');
		//$selectedLanguage = 'en_US';
		$filepath = Path::join($this->getDataFolder(), "lang/$selectedLanguage.yml");
		if (!file_exists($filepath)) {
			$this->getLogger()->error("Language file $selectedLanguage.yml not found, using en_US.yml");
			$filepath = Path::join($this->getDataFolder(), "lang/$selectedLanguage.yml");
		}
		return new Language($selectedLanguage, yaml_parse_file($filepath));
	}
}