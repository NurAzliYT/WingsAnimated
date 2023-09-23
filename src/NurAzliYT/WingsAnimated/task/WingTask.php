<?php

declare(strict_types=1);

namespace NurAzliYT\WingsAnimated\task;

use pocketmine\scheduler\Task;
use pocketmine\player\Player;
use NurAzliYT\WingsAnimated\WingsAnimated;

class WingTask extends Task{

	public function __construct(
		private Player $player,
		private WingsAnimated $wing){}

	public function getWing() :WingsAnimated{
		return $this->wing;
	}

	public function getPlayer() :?Player{
		return $this->player;
	}

	public function onRun() :void{
		if($this->getPlayer() == null){
			$this->getHandler()->cancel();
		}
		$this->getWing()->draw($this->getPlayer()->getLocation());
	}
}
