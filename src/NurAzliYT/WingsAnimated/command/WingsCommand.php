<?php

namespace NurAzliYT\WingsAnimated\command;

use pocketmine\command\Command;
use pocketmine\command\CommandSender
use pocketmine\player\Player;
use NurAzliYT\WingsAnimated\form\WingsForm;

Class WingsCommand extends Command{

	public function __construct(){
		parent::__construct("wings", "open wings form");
	}

	public function execute(CommandSender $sender, string $label, array $args) :bool{
		if(!$sender instanceof Player) return false;
		$form = new WingsForm($sender);
		$form->send();
		return true;
	}
}
