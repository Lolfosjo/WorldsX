<?php

namespace blackjack200\worldsx\generator;

use pocketmine\block\VanillaBlocks;
use pocketmine\world\ChunkManager;
use pocketmine\world\format\Chunk;
use pocketmine\world\generator\Generator;

class VoidGenerator extends Generator {
	public function generateChunk(ChunkManager $world, int $chunkX, int $chunkZ) : void {
		//We don't know world spawn position so use PM4 default one.
		$spawnChunkPos = 256 >> Chunk::COORD_BIT_SIZE;
		if ($chunkX === $spawnChunkPos && $chunkZ === $spawnChunkPos) {
			$world->getChunk($chunkX, $chunkZ)->setBlockStateId(0, 64, 0, VanillaBlocks::BEDROCK()->getStateId());
		}
	}

	public function populateChunk(ChunkManager $world, int $chunkX, int $chunkZ) : void {

	}
}