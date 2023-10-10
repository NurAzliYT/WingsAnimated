<?php

namespace NurAzliYT\WingsAnimated;

use pocketmine\plugin\PluginBase;
use pocketmine\player\Player;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\utils\SingletonTrait;
use pocketmine\utils\Config;
use NurAzliYT\WingsAnimated\task\WingTask;
use NurAzliYT\WingsAnimated\command\WingsCommand;
use NurAzliYT\WingsAnimated\utils\Utils;

class Loader extends PluginBase implements Listener {
    use SingletonTrait;

    private array $equipPlayers = [];
    private array $wings = [];
    private Config $config;

    public function onLoad(): void {
        self::setInstance($this);
    }

    public function onEnable(): void {
        $this->saveDefaultConfig();
        $this->saveResource("wings/example.yml");
        $this->getServer()->getCommandMap()->register("WingsAnimated", new WingsCommand());
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->config = new Config($this->getDataFolder() . "/config.yml", Config::YAML);

        foreach (glob($this->getDataFolder() . "wings/*.yml") as $wingPath) {
            $wingName = pathinfo($wingPath, PATHINFO_FILENAME);
            $this->wings[$wingName] = new Config($wingPath, Config::YAML);
        }
    }

    public function onQuit(PlayerQuitEvent $event): void {
        $player = $event->getPlayer();
        $this->unEquip($player);
    }

    public function getWings(): array {
        return $this->wings;
    }

    public function getWingData(string $name): ?Config {
        return $this->wings[$name] ?? null;
    }

    public function getWing(string $name): WingsAnimated {
        $wingData = $this->getWingData($name);
        $shape = $wingData->get("shape");
        $scale = $wingData->get("scale");
        return new WingsAnimated($name, $shape, $scale);
    }

    public function getSetting(): Config {
        return $this->config;
    }

    public function equipWing(Player $player, WingsAnimated $wing): void {
        if (!Utils::hasPermission($player, $wing->getName())) {
            $player->sendMessage("You don't have permission");
            return;
        }

        $tickUpdate = $this->getSetting()->get("tick-update");
        $playerName = $player->getName();
        $wingTask = new WingTask($player, $wing);

        if (!isset($this->equipPlayers[$playerName])) {
            $this->getScheduler()->scheduleRepeatingTask($wingTask, $tickUpdate);
            $this->equipPlayers[$playerName]["task"] = $wingTask;
            $this->equipPlayers[$playerName]["name"] = $wing;
            $player->sendMessage($this->getSetting()->get("turn-on"));
            return;
        }

        if ($this->equipPlayers[$playerName]["name"] == $wing) {
            $this->unEquip($player);
        } else {
            $this->unEquip($player);
            $this->getScheduler()->scheduleRepeatingTask($wingTask, $tickUpdate);
            $this->equipPlayers[$playerName]["task"] = $wingTask;
            $this->equipPlayers[$playerName]["name"] = $wing;
            $player->sendMessage($this->getSetting()->get("turn-on"));
        }
    }

    public function unEquip(Player $player): void {
        $playerName = $player->getName();

        if (isset($this->equipPlayers[$playerName])) {
            $task = $this->equipPlayers[$playerName]["task"];
            $task->getHandler()->cancel();
            unset($this->equipPlayers[$playerName]);
            $player->sendMessage($this->getSetting()->get("turn-off"));
        }
    }
}
