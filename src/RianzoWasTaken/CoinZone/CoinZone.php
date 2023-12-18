<?php

declare(strict_types=1);

namespace RianzoWasTaken\CoinZone;

use Exception;
use JsonException;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\Listener;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;

class CoinZone extends PluginBase implements Listener {
    private static CoinZone $instance;
    public static Config $config;

    protected function onEnable(): void
    {
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->getLogger()->info("§6Powered by §erianzo dev.");
        self::$config = new Config($this->getDataFolder()."config.yml", Config::YAML, array(   #aquiconfiguren #DarlexxNegro
            "pos1" => "8038:56:8018",
            "pos2" => "8043:56:8021",
            "world" => "hub",
            "money" => 10,
            "temps" => 30,
            "message" => array(
                "sans-permission" => "§6[§eCoin§6Zone§e] §cVous n'avez pas la permission d'effectuer cette commande.",
                "pos1" => "§6[§eCoin§6Zone§e] §fJ'ai bien configuré la §ePosition 1 §fde la §6CoinZone§f.",
                "pos2" => "§6[§eCoin§6Zone§e] §fJ'ai bien configuré la §ePosition 2 §fde la §6CoinZone§f.",
                "popup" => "§f- §6+§e{money}§6$ §f-")));
        self::$instance = $this;
        $task = new ZoneTask();
        $this->getScheduler()->scheduleRepeatingTask($task, 1*20);
    }

    protected function onLoad(): void
    {
        self::$config = new Config($this->getDataFolder()."config.yml", Config::YAML, array(
            "pos1" => "8038:56:8018",
            "pos2" => "8043:56:8021",
            "world" => "hub",
            "money" => 10,
            "temps" => 3,
            "message" => array(
                "sans-permission" => "§6[§eCoin§6Zone§e] §cVous n'avez pas la permission d'effectuer cette commande.",
                "pos1" => "§6[§eCoin§6Zone§e] §fJ'ai bien configuré la §ePosition 1 §fde la §6CoinZone§f.",
                "pos2" => "§6[§eCoin§6Zone§e] §fJ'ai bien configuré la §ePosition 2 §fde la §6CoinZone§f.",
                "popup" => "§f- §6+§e{money}§6$ §f-" )));
        self::$instance = $this;
    }

    /**
     * @throws JsonException
     * @throws Exception
     */
    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool
    {
        switch ($command->getName()){
            case "coinzone":
                if(($sender instanceof Player) and $sender->hasPermission("coinzone")) {
                    if (isset($args[0])) {
                        if ($args[0] === "pos1") {
                            CoinZone::$config->reload();
                            $x = $sender->getPosition()->x;
                            $y = $sender->getPosition()->y;
                            $z = $sender->getPosition()->z;
                            $w = $sender->getPosition()->world;
                            self::$config->set("pos1", (int)$x . ":" . (int)$y . ":" . (int)$z);
                            self::$config->set("world", $w->getDisplayName());
                            self::$config->save();
                            $sender->sendMessage(self::$config->getNested("message.pos1"));
                        } else if ($args[0] === "pos2") {
                            CoinZone::$config->reload();
                            $x = $sender->getPosition()->x;
                            $y = $sender->getPosition()->y;
                            $z = $sender->getPosition()->z;
                            $w = $sender->getPosition()->world;
                            self::$config->set("pos2", (int)$x . ":" . (int)$y . ":" . (int)$z);
                            self::$config->set("world", $w->getDisplayName());
                            self::$config->save();
                            $sender->sendMessage(self::$config->getNested("message.pos2"));

                        } else if ($args[0] === "ver" or $args[0] === "version") {
                            $sender->sendMessage("§6[§eCoin§6Zone§e] §f- §e/coinzone\n§6Author : §eNyrokGames\n§3Twitter : §b@Nyrok10");
                        }
                    } else {
                        $sender->sendMessage("§6[§eCoin§6Zone§e] §f- §6By §e@Nyrok10 §7(Twitter)\n§6/coinzone pos1 §f- §eDéfinir la première position de la zone.\n§6/coinzone pos2 §f- §eDéfinir la seconde position de la zone.");
                    }
                }
                break;
        }
        return true;
    }

    public static function onZone(Player $player): bool
    {
        $pos1 = explode(":", self::$config->get("pos1"));
        $pos2 = explode(":", self::$config->get("pos2"));
        $minX = min($pos1[0], $pos2[0]);
        $maxX = max($pos1[0], $pos2[0]);
        $minY = min($pos1[1], $pos2[1]);
        $maxY = max($pos1[1], $pos2[1]);
        $minZ = min($pos1[2], $pos2[2]);
        $maxZ = max($pos1[2], $pos2[2]);

        if($player->getPosition()->x >= $minX && $player->getPosition()->x <= $maxX
            && $player->getPosition()->y >= $minY && $player->getPosition()->y <= $maxY
            && $player->getPosition()->z >= $minZ && $player->getPosition()->z <= $maxZ) {
            return true;
        } else return false;
    }

    public static function onArea(Player $player): bool
    {
        $pos1 = explode(":", self::$config->get("pos1"));
        $pos2 = explode(":", self::$config->get("pos2"));
        $minX = min($pos1[0], $pos2[0]) - 20;
        $maxX = max($pos1[0], $pos2[0]) + 20;
        $minY = min($pos1[1], $pos2[1]);
        $maxY = max($pos1[1], $pos2[1]);
        $minZ = min($pos1[2], $pos2[2]) - 20;
        $maxZ = max($pos1[2], $pos2[2]) + 20;

        if($player->getPosition()->x >= $minX && $player->getPosition()->x <= $maxX
            && $player->getPosition()->y >= $minY && $player->getPosition()->y <= $maxY
            && $player->getPosition()->z >= $minZ && $player->getPosition()->z <= $maxZ) {
            return true;
        } else return false;
    }

    public static function setNewKing(): Player|null {
        $first = false;
        global $king;
        $king = null;
        $players = self::$instance->getServer()->getOnlinePlayers();
        shuffle($players);
        foreach ($players as $player){
            if(self::onZone($player) and !$first){
                $first = true;
                $king = $player;
            }
        }
        return $king;
    }

    public static function getInstance(): CoinZone {
        return self::$instance;
    }
}
