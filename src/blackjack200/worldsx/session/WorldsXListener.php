<?php

namespace blackjack200\worldsx\session;

use blackjack200\worldsx\world\GameRuleUtil;
use blackjack200\worldsx\world\types\DefaultGameRules;
use pocketmine\block\VanillaBlocks;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\entity\Living;
use pocketmine\entity\object\PrimedTNT;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockSpreadEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDeathEvent;
use pocketmine\event\entity\EntityExplodeEvent;
use pocketmine\event\entity\EntityRegainHealthEvent;
use pocketmine\event\entity\EntityTeleportEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\event\world\WorldLoadEvent;
use pocketmine\event\world\WorldUnloadEvent;
use pocketmine\network\mcpe\protocol\SettingsCommandPacket;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\world\World;

class WorldsXListener implements Listener {
	public function __construct() {
		foreach (Server::getInstance()->getWorldManager()->getWorlds() as $world) {
			WorldGameRules::setupGameRules($world);
		}
	}

	public function syncGameRules(World $world) : void {
		foreach ($world->getPlayers() as $player) {
			GameRuleUtil::send($player);
		}
	}

	public function onPlayerJoin(PlayerJoinEvent $e) : void {
		GameRuleUtil::send($e->getPlayer());
	}

	public function onWorldLoad(WorldLoadEvent $event) : void {
		WorldGameRules::setupGameRules($event->getWorld());
	}

	public function onWorldUnload(WorldUnloadEvent $event) : void {
		WorldGameRules::remove($event->getWorld());
	}

	public function onPlayerTeleport(EntityTeleportEvent $event) : void {
		$p = $event->getEntity();
		if ($p instanceof Player && $event->getFrom()->getWorld() !== $event->getTo()->getWorld()) {
			GameRuleUtil::send($p, WorldGameRules::mustGetGameRuleCollection($event->getTo()->getWorld()));
		}
	}

	public function onDamage(EntityDamageByEntityEvent $event) : void {
		$entity = $event->getEntity();
		if ($entity instanceof Player && $event->getDamager() instanceof Player) {
			$g = WorldGameRules::mustGetGameRuleCollection($entity->getWorld());
			if (!$g->get(DefaultGameRules::PVP)) {
				$event->cancel();
			}
		}
	}

	public function onRegenerate(EntityRegainHealthEvent $event) : void {
		$entity = $event->getEntity();
		if (
			$entity instanceof Living &&
			$event->getRegainReason() === EntityRegainHealthEvent::CAUSE_SATURATION &&
			!$entity->getEffects()->has(VanillaEffects::REGENERATION())
		) {
			$g = WorldGameRules::mustGetGameRuleCollection($entity->getWorld());
			if (!$g->get(DefaultGameRules::NATURAL_REGENERATION)) {
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
			$g = WorldGameRules::mustGetGameRuleCollection($e->getWorld());
			if (!$g->get(DefaultGameRules::TNT_EXPLODES)) {
				$event->cancel();
			}
		}
	}

	public function onBreak(BlockBreakEvent $event) : void {
		$g = WorldGameRules::mustGetGameRuleCollection($event->getPlayer()->getWorld());
		if (!$g->get(DefaultGameRules::DO_TILE_DROPS)) {
			$event->setDrops([]);
		}
	}

	public function onEntityFall(EntityDamageEvent $event) : void {
		if ($event->getCause() === EntityDamageEvent::CAUSE_FALL) {
			$w = $event->getEntity()->getWorld();
			if (!WorldGameRules::mustGetGameRuleCollection($w)->get(DefaultGameRules::FALL_DAMAGE)) {
				$event->cancel();
			}
		}
	}

	public function onEntityDeath(EntityDeathEvent $event) : void {
		$g = WorldGameRules::mustGetGameRuleCollection($event->getEntity()->getWorld());
		if (!$g->get(DefaultGameRules::DO_ENTITY_DROPS)) {
			$event->setDrops([]);
			$event->setXpDropAmount(0);
		}
	}

	public function onBlockBreak(BlockBreakEvent $event) : void {
		$w = $event->getPlayer()->getWorld();
		$g = WorldGameRules::mustGetGameRuleCollection($w);
		if (!$g->get(DefaultGameRules::DO_TILE_DROPS)) {
			$event->setDrops([]);
		}
	}

	public function onPlayerDeath(PlayerDeathEvent $event) : void {
		$g = WorldGameRules::mustGetGameRuleCollection($event->getEntity()->getWorld());
		if (!$g->get(DefaultGameRules::SHOW_DEATH_MESSAGE)) {
			$event->setDeathMessage('');
		}
		if ($g->get(DefaultGameRules::KEEP_INVENTORY)) {
			$event->setKeepInventory(true);
		}
	}

	public function a(BlockSpreadEvent $event) : void {
		$g = WorldGameRules::mustGetGameRuleCollection($event->getSource()->getPosition()->getWorld());
		if ($event->getSource()->hasSameTypeId(VanillaBlocks::FIRE())) {
			if (!$g->get(DefaultGameRules::DO_FIRE_TICK)) {
				$event->cancel();
			}
		}
	}
}