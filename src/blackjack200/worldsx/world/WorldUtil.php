<?php

namespace blackjack200\worldsx\world;

use InvalidArgumentException;
use pocketmine\Server;
use pocketmine\utils\Filesystem;
use pocketmine\world\format\io\data\BaseNbtWorldData;
use pocketmine\world\World;
use pocketmine\world\WorldCreationOptions;
use Webmozart\PathUtil\Path;

class WorldUtil {
	public static function findWorldByFolderName(string $name) : ?World {
		// Assume that folderName is unique
		$worlds = Server::getInstance()->getWorldManager()->getWorlds();
		foreach ($worlds as $world) {
			if ($world->getFolderName() === $name) {
				return $world;
			}
		}
		return null;
	}

	public static function unloadWorldByFolderName(string $name) : void {
		$world = self::findWorldByFolderName($name);
		if ($world !== null) {
			if (!Server::getInstance()->getWorldManager()->unloadWorld($world, true)) {
				throw new InvalidArgumentException('Failed to unload world');
			}
		}
	}

	public static function loadWorld(string $world) : World {
		$worldManager = Server::getInstance()->getWorldManager();
		if ($worldManager->loadWorld($world, true)) {
			$w = $worldManager->getWorldByName($world);
			assert($w instanceof World);
			return $w;
		}
		throw new InvalidArgumentException("Failed when loading $world");
	}

	public static function rename(string $old, string $new) : void {
		$basePath = Path::join(Server::getInstance()->getDataPath(), 'worlds');
		$oldPath = Path::join($basePath, $old);
		$newPath = Path::join($basePath, $new);
		if (!file_exists($oldPath)) {
			throw new InvalidArgumentException("World $old does not exist");
		}
		if (file_exists($newPath)) {
			throw new InvalidArgumentException("World $new already exists");
		}
		self::unloadWorldByFolderName($old);
		rename($oldPath, $newPath);
		$world = self::loadWorld($new);
		$worldData = $world->getProvider()->getWorldData();
		if (!($worldData instanceof BaseNbtWorldData)) {
			throw new InvalidArgumentException('World data is not BaseNbtWorldData');
		}
		Server::getInstance()->getWorldManager()->unloadWorld($world, true);
		$worldData->getCompoundTag()->setString('LevelName', $new);
		$worldData->save();
	}

	public static function remove(string $name) : void {
		$basePath = Path::join(Server::getInstance()->getDataPath(), 'worlds');
		$path = Path::join($basePath, $name);
		if (!file_exists($path)) {
			throw new InvalidArgumentException("World $name does not exist");
		}
		self::unloadWorldByFolderName($name);
		Filesystem::recursiveUnlink($path);
	}

	public static function duplicate(string $origin, string $dest) : void {
		$w = self::findWorldByFolderName($origin);
		if ($w !== null) {
			$w->save(true);
		}
		$basePath = Path::join(Server::getInstance()->getDataPath(), 'worlds');
		$originPath = Path::join($basePath, $origin);
		$destPath = Path::join($basePath, $dest);
		Filesystem::recursiveCopy($originPath, $destPath);
	}

	public static function create(string $name, WorldCreationOptions $options) : bool {
		return Server::getInstance()->getWorldManager()->generateWorld($name, $options);
	}

	public static function updateDefaultWorld(string $name, bool $permanent) : void {
		$world = self::loadWorld($name);
		if ($permanent) {
			$cfg = Server::getInstance()->getConfigGroup();
			$cfg->setConfigString('level-name', $name);
			$cfg->save();
		}
		Server::getInstance()->getWorldManager()->setDefaultWorld($world);
	}

	public static function contains(string $name) : bool {
		$basePath = Path::join(Server::getInstance()->getDataPath(), 'worlds');
		$path = Path::join($basePath, $name);
		return is_dir($path);
	}

	public static function getWorlds() : array {
		$basePath = Path::join(Server::getInstance()->getDataPath(), 'worlds');
		$dirs = scandir($basePath, SCANDIR_SORT_NONE);
		foreach ($dirs as $i => $dir) {
			if ($dir === '.' || $dir === '..') {
				unset($dirs[$i]);
			}
		}
		return $dirs;
	}
}