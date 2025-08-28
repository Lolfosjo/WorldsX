<?php

namespace blackjack200\worldsx;

use blackjack200\worldsx\command\GameRuleCommand;
use blackjack200\worldsx\command\WorldsXCommand;
use blackjack200\worldsx\generator\VoidGenerator;
use muqsit\vanillagenerator\generator\overworld\OverworldGenerator;
use blackjack200\worldsx\lang\Language;
use blackjack200\worldsx\session\WorldsXListener;
use blackjack200\worldsx\world\types\GameRuleMapping;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;
use pocketmine\utils\Filesystem;
use pocketmine\world\generator\GeneratorManager;
use RuntimeException;
use Symfony\Component\Filesystem\Path;

class WorldsX extends PluginBase {
	protected function onLoad() : void {
		GeneratorManager::getInstance()->addGenerator(VoidGenerator::class, "void", fn() => null);
        GeneratorManager::getInstance()->addGenerator(OverworldGenerator::class, "vanilla_normal", fn() => null);
	}

	protected function onEnable() : void {
		$this->saveDefaultConfig();
		$language = $this->setupLanguage();
		if (Server::getInstance()->getPluginManager()->getPlugin('MultiWorld') !== null) {
			$this->getLogger()->warning($language->translateString('multiworld.warning') ?? throw new RuntimeException());
			$this->getServer()->getPluginManager()->disablePlugin($this);
			return;
		}
		GameRuleMapping::setup(
			json_decode(Filesystem::fileGetContents($this->getResourcePath('gamerule_name_map.json')), true, 512, JSON_THROW_ON_ERROR),
			json_decode(Filesystem::fileGetContents($this->getResourcePath('internal_gamerule_map.json')), true, 512, JSON_THROW_ON_ERROR)
		);
		$listener = new WorldsXListener();
		$this->getServer()->getPluginManager()->registerEvents($listener, $this);
		$registerCmd = fn($cmd) => $this->getServer()->getCommandMap()->register($this->getName(), $cmd);
		$registerCmd(new WorldsXCommand($language, $this));
		$registerCmd(new GameRuleCommand($language, $listener, $this));
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
