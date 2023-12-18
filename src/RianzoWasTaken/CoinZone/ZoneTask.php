<?php



namespace RianzoWasTaken\CoinZone;



use JsonException;

use pocketmine\scheduler\Task;

use onebone\coinapi\CoinAPI;



class ZoneTask extends Task

{

    /**

     * @throws JsonException

     */

    public function onRun(): void

    {

        CoinZone::$config->reload();

        $money = CoinZone::$config->get("money") ?? 5;

        $interval = CoinZone::$config->get("temps") ?? 60;

        $player = CoinZone::getInstance()->getServer()->getPlayerExact(CoinZone::$config->getNested("coinzone.king") ?? "");

        $players = CoinZone::getInstance()->getServer()->getOnlinePlayers();

        if(!$player or !CoinZone::onZone($player)){

            $king = CoinZone::setNewKing();

            if($king){

                CoinZone::$config->setNested("coinzone.king", $king->getName());

                CoinZone::$config->setNested("coinzone.time", 0);

                CoinZone::$config->setNested("coinzone.money", 0);

                CoinZone::$config->save();

                foreach ($players as $p){

                    if(CoinZone::onArea($p)) $p->sendPopup("§f[ §eCoin§6Zone §f]\n§6".$king->getName()." §f- §e".CoinZone::$config->getNested("coinzone.money")."§6©");

                }

            }

            else {

                CoinZone::$config->setNested("coinzone.time", 0);

                CoinZone::$config->setNested("coinzone.money", 0);

                CoinZone::$config->save();

                foreach ($players as $p){

                    if(CoinZone::onArea($p)) $p->sendPopup("§f[ §eCoin§6Zone §f]\n§6Player §f- §e*§6©");

                }

            }

        }

        else if(CoinZone::onZone($player)){

            $time = CoinZone::$config->getNested("coinzone.time");

            if(gettype($time / $interval) === "integer" and $time / $interval !== 0){

                CoinAPI::getInstance()->addCoin($player->getName(), $money);

                CoinZone::$config->setNested("coinzone.money", CoinZone::$config->getNested("coinzone.money") + $money);

                $player->sendTip(str_replace("{money}", $money, CoinZone::$config->getNested("message.popup")));

            }

            foreach ($players as $p){

                if(CoinZone::onArea($p)) $p->sendPopup("§f[ §eCoin§6Zone §f]\n§6".$player->getName()." §f- §e".CoinZone::$config->getNested("coinzone.money")."§©$");

            }

            CoinZone::$config->setNested("coinzone.time", ($time ?? 1) + 1);

            CoinZone::$config->save();

        }

    }

}
