<?php

namespace blackjack200\worldsx\session;

use blackjack200\worldsx\world\types\GameRules;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\entity\Living;
use pocketmine\entity\object\PrimedTNT;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityExplodeEvent;
use pocketmine\event\entity\EntityRegainHealthEvent;
use pocketmine\event\entity\EntityTeleportEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\event\world\WorldLoadEvent;
use pocketmine\event\world\WorldUnloadEvent;
use pocketmine\network\mcpe\protocol\SettingsCommandPacket;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\utils\AssumptionFailedError;
use pocketmine\world\World;

class WorldsXListener implements Listener {
	/** @var PlayerSession[] */
	private array $sessions = [];

	public function __construct() {
		foreach (Server::getInstance()->getWorldManager()->getWorlds() as $world) {
			WorldGameRules::setupGameRules($world);
		}
	}

	public function onPlayerJoin(PlayerJoinEvent $e) : void {
		$p = $e->getPlayer();
		$w = $p->getWorld();
		$g = WorldGameRules::getGameRule($w);
		$s = new PlayerSession($p, $g);
		$this->sessions[spl_object_hash($p)] = $s;
		$s->syncGameRules($g);
	}

	public function onPlayerTeleport(EntityTeleportEvent $event) : void {
		$p = $event->getEntity();
		if ($p instanceof Player && $event->getFrom()->getWorld() !== $event->getTo()->getWorld()) {
			$s = $this->getSession($p);
			$s->syncGameRules(WorldGameRules::getGameRule($event->getTo()->getWorld()));
		}
	}

	protected function getSession(Player $e) : PlayerSession {
		return $this->sessions[spl_object_hash($e)] ?? throw new AssumptionFailedError('Player session not found');
	}

	public function onPlayerQuit(PlayerQuitEvent $e) : void {
		unset($this->sessions[spl_object_hash($e->getPlayer())]);
	}

	public function onWorldLoad(WorldLoadEvent $event) : void {
		WorldGameRules::setupGameRules($event->getWorld());
	}

	public function onWorldUnload(WorldUnloadEvent $event) : void {
		WorldGameRules::remove($event->getWorld());
	}

	public function onDamage(EntityDamageByEntityEvent $event) : void {
		$entity = $event->getEntity();
		if ($entity instanceof Player && $event->getDamager() instanceof Player) {
			$g = WorldGameRules::getGameRule($entity->getWorld());
			if (!$g->get(GameRules::PVP)) {
				$event->cancel();
			}
		}
	}

	public function onRegenerate(EntityRegainHealthEvent $event) : void {
		$entity = $event->getEntity();
		if ($entity instanceof Living && !$entity->getEffects()->has(VanillaEffects::REGENERATION())) {
			$g = WorldGameRules::getGameRule($entity->getWorld());
			if (!$g->get(GameRules::NATURAL_REGENERATION)) {
				$event->cancel();
			}
		}
	}

	public function onDataPacketReceive(DataPacketReceiveEvent $event) : void {
		$packet = $event->getPacket();
		if ($packet instanceof SettingsCommandPacket) {
			$player = $event->getOrigin()->getPlayer();
			if ($player !== null) {
				Server::getInstance()->dispatchCommand($player, substr($packet->getCommand(), 1));
			}
		}
	}

	public function onEntityExplode(EntityExplodeEvent $event) : void {
		$e = $event->getEntity();
		if ($e instanceof PrimedTNT) {
			$g = WorldGameRules::getGameRule($e->getWorld());
			if (!$g->get(GameRules::TNT_EXPLODES)) {
				$event->cancel();
			}
		}
	}

	public function onBreak(BlockBreakEvent $event) : void {
		$g = WorldGameRules::getGameRule($event->getPlayer()->getWorld());
		if (!$g->get(GameRules::DO_TILE_DROPS)) {
			$event->setDrops([]);
		}
	}

	public function syncGameRules(World $world) : void {
		foreach ($world->getPlayers() as $player) {
			$this->getSession($player)->sendGameRules();
		}
	}
}