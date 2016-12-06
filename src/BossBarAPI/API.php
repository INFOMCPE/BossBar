<?php

namespace BossBarAPI;

use pocketmine\Player;
use pocketmine\network\protocol\RemoveEntityPacket;
use pocketmine\Server;
use pocketmine\network\protocol\AddEntityPacket;
use pocketmine\entity\Entity;
use pocketmine\network\protocol\MoveEntityPacket;
use pocketmine\level\Location;

class API{

	/**
	 * Sends the text to all players
	 *
	 * @param Player[] $players
	 * To who to send
	 * @param string $title
	 * The title of the boss bar
	 * @param null|int $ticks
	 * How long it displays
	 * @return int EntityID NEEDED FOR CHANGING TEXT/PERCENTAGE! | null (No Players)
	 */
	public static function addBossBar($players, string $title, $ticks = null){
		if(empty($players)) return null;
		
		$eid = Entity::$entityCount++;
		
		$packet = new AddEntityPacket();
		$packet->eid = $eid;
		$packet->type = 52;
		$packet->yaw = 0;
		$packet->pitch = 0;
		$packet->metadata = [Entity::DATA_LEAD_HOLDER_EID => [Entity::DATA_TYPE_LONG, -1], Entity::DATA_FLAGS => [Entity::DATA_TYPE_LONG, 0 ^ 1 << Entity::DATA_FLAG_SILENT ^ 1 << Entity::DATA_FLAG_INVISIBLE], Entity::DATA_SCALE => [Entity::DATA_TYPE_FLOAT, 0], 
				Entity::DATA_NAMETAG => [Entity::DATA_TYPE_STRING, $title], Entity::DATA_BOUNDING_BOX_WIDTH => [Entity::DATA_TYPE_FLOAT, 0], Entity::DATA_BOUNDING_BOX_HEIGHT => [Entity::DATA_TYPE_FLOAT, 0]];
		foreach($players as $player){
			$pk = clone $packet;
			$pk->x = $player->x;
			$pk->y = $player->y;
			$pk->z = $player->z;
			$player->dataPacket($pk);
		}
		
		$bpk = new BossEventPacket(); // This updates the bar
		$bpk->eid = $eid;
		$bpk->state = 0;
		Server::getInstance()->broadcastPacket($players, $bpk);
		
		return $eid; // TODO: return EID from bosseventpacket?
	}

	/**
	 * Sends the text to one player
	 *
	 * @param Player $players
	 * To who to send
	 * @param int $eid
	 * The EID of an existing fake wither
	 * @param string $title
	 * The title of the boss bar
	 * @param null|int $ticks
	 * How long it displays
	 */
	public static function sendBossBarToPlayer(Player $player, int $eid, string $title, $ticks = null){
		$packet = new AddEntityPacket();
		$packet->eid = $eid;
		$packet->type = 52;
		$packet->yaw = 0;
		$packet->pitch = 0;
		$packet->metadata = [Entity::DATA_LEAD_HOLDER_EID => [Entity::DATA_TYPE_LONG, -1], Entity::DATA_FLAGS => [Entity::DATA_TYPE_LONG, 0 ^ 1 << Entity::DATA_FLAG_SILENT ^ 1 << Entity::DATA_FLAG_INVISIBLE], Entity::DATA_SCALE => [Entity::DATA_TYPE_FLOAT, 0], 
				Entity::DATA_NAMETAG => [Entity::DATA_TYPE_STRING, $title], Entity::DATA_BOUNDING_BOX_WIDTH => [Entity::DATA_TYPE_FLOAT, 0], Entity::DATA_BOUNDING_BOX_HEIGHT => [Entity::DATA_TYPE_FLOAT, 0]];
		$packet->x = $player->x;
		$packet->y = $player->y;
		$packet->z = $player->z;
		$player->dataPacket($packet);
		
		$bpk = new BossEventPacket(); // This updates the bar
		$bpk->eid = $eid;
		$bpk->state = 0;
		$player->dataPacket($bpk);
	}

	/**
	 * Sets how many % the bar is full by EID
	 *
	 * @param int $percentage
	 * 0-100
	 * @param int $eid 
	 */
	public static function setPercentage(int $percentage, int $eid){
		if(!count(Server::getInstance()->getOnlinePlayers()) > 0) return;
		
		$upk = new UpdateAttributesPacket(); // Change health of fake wither -> bar progress
		$upk->entries[] = new BossBarValues(0, 300, max(0.5, min([$percentage, 100])) / 100 * 300, 'minecraft:health'); // Ensures that the number is between 0 and 100;
		$upk->entityId = $eid;
		Server::getInstance()->broadcastPacket(Server::getInstance()->getOnlinePlayers(), $upk);
		
		$bpk = new BossEventPacket(); // This updates the bar
		$bpk->eid = $eid;
		$bpk->state = 0;
		Server::getInstance()->broadcastPacket(Server::getInstance()->getOnlinePlayers(), $bpk);
	}

	/**
	 * Sets the BossBar title by EID
	 *
	 * @param string $title 
	 * @param int $eid 
	 */
	public static function setTitle(string $title, int $eid){
		if(!count(Server::getInstance()->getOnlinePlayers()) > 0) return;
		
		$npk = new SetEntityDataPacket(); // change name of fake wither -> bar text
		$npk->metadata = [Entity::DATA_NAMETAG => [Entity::DATA_TYPE_STRING, $title]];
		$npk->eid = $eid;
		Server::getInstance()->broadcastPacket(Server::getInstance()->getOnlinePlayers(), $npk);
		
		$bpk = new BossEventPacket(); // This updates the bar
		$bpk->eid = $eid;
		$bpk->state = 0;
		Server::getInstance()->broadcastPacket(Server::getInstance()->getOnlinePlayers(), $bpk);
	}

	/**
	 * Remove BossBar from players by EID
	 *
	 * @param Player[] $players 
	 * @param int $eid 
	 * @return boolean removed
	 */
	public static function removeBossBar($players, int $eid){
		if(empty($players)) return false;
		
		$pk = new RemoveEntityPacket();
		$pk->eid = $eid;
		Server::getInstance()->broadcastPacket($players, $pk);
		return true;
	}

	/**
	 * Handle player movement
	 *
	 * @param Location $pos
	 * @param unknown $eid 
	 * @return MoveEntityPacket $pk
	 */
	public static function playerMove(Location $pos, $eid){
		$pk = new MoveEntityPacket();
		$pk->x = $pos->x;
		$pk->y = $pos->y - 4;
		$pk->z = $pos->z;
		$pk->eid = $eid;
		$pk->yaw = $pk->pitch = $pk->headYaw = 0;
		return clone $pk;
	}
}
