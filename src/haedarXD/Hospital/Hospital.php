<?php

declare(strict_types=1);

namespace haedarXD\Hospital;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerRespawnEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\world\Position;
use pocketmine\world\sound\NoteSound;
use pocketmine\block\Bed;
use pocketmine\utils\Config;
use pocketmine\scheduler\ClosureTask;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\entity\effect\EffectInstance;

class Hospital extends PluginBase implements Listener{

    private ?Position $hospital = null;
    private ?Position $bed = null;

    private array $timers = [];
    private array $tasks = [];
    private array $bedSetter = [];

    private Config $config;

    public function onEnable(): void{
        @mkdir($this->getDataFolder());
        $this->config = new Config($this->getDataFolder() . "config.yml", Config::YAML);

        if(is_array($h = $this->config->get("hospital_location"))){
            $w = $this->getServer()->getWorldManager()->getWorldByName($h["world"]);
            if($w !== null){
                $this->hospital = new Position($h["x"], $h["y"], $h["z"], $w);
            }
        }

        if(is_array($b = $this->config->get("bed_location"))){
            $w = $this->getServer()->getWorldManager()->getWorldByName($b["world"]);
            if($w !== null){
                $this->bed = new Position($b["x"], $b["y"], $b["z"], $w);
            }
        }

        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool{
        if(!$sender instanceof Player) return true;

        switch($command->getName()){
            case "sethospital":
                $p = $sender->getPosition();
                $this->hospital = $p;
                $this->config->set("hospital_location", [
                    "x"=>$p->getX(),
                    "y"=>$p->getY(),
                    "z"=>$p->getZ(),
                    "world"=>$p->getWorld()->getFolderName()
                ]);
                $this->config->save();
                $sender->sendMessage("§a✔ Hospital location set!");
                $sender->sendTitle("§a✔", "Hospital set successfully");
                return true;

            case "setbedh":
                $this->bedSetter[$sender->getName()] = true;
                $sender->sendMessage("§e🛏 Click on a bed to set it as hospital bed");
                $sender->sendTitle("§e🛏", "Click a bed");
                return true;
        }
        return false;
    }

    public function onInteract(PlayerInteractEvent $event): void{
        $player = $event->getPlayer();
        if(!isset($this->bedSetter[$player->getName()])) return;

        $block = $event->getBlock();
        if(!$block instanceof Bed){
            $player->sendMessage("§c❌ That's not a bed!");
            $player->sendTitle("§c❌", "Not a bed");
            return;
        }

        $pos = $block->getPosition();
        $this->bed = $pos;

        $this->config->set("bed_location", [
            "x"=>$pos->getX(),
            "y"=>$pos->getY(),
            "z"=>$pos->getZ(),
            "world"=>$pos->getWorld()->getFolderName()
        ]);
        $this->config->save();

        unset($this->bedSetter[$player->getName()]);
        $player->sendMessage("§a✔ Hospital bed set!");
        $player->sendTitle("§a✔", "Bed location saved");
    }

    public function onRespawn(PlayerRespawnEvent $event): void{
        if($this->bed === null || $this->hospital === null) return;

        $player = $event->getPlayer();
        $event->setRespawnPosition($this->bed);

        $this->getScheduler()->scheduleDelayedTask(new ClosureTask(function() use ($player): void{
            if(!$player->isOnline()) return;

            $player->sleepOn($this->bed);
            $player->sendTitle("§c💀 You died!", "§7Resting in hospital bed...");

            $this->getScheduler()->scheduleDelayedTask(
                new ClosureTask(fn() => $player->isOnline() ? $this->sendToHospital($player) : null),
                20 * 10
            );
        }), 20);
    }

    private function sendToHospital(Player $player): void{
        $player->stopSleep();
        $player->teleport($this->hospital);

        $player->getEffects()->add(new EffectInstance(VanillaEffects::NAUSEA(), 20 * 10, 1));
        $player->getEffects()->add(new EffectInstance(VanillaEffects::SLOWNESS(), 20 * 10, 2));
        $player->getEffects()->add(new EffectInstance(VanillaEffects::BLINDNESS(), 20 * 3, 0));

        $name = $player->getName();
        $this->timers[$name] = 50;

        $this->tasks[$name] = $this->getScheduler()->scheduleRepeatingTask(
            new ClosureTask(function() use ($player, $name): void{
                if(!$player->isOnline() || !isset($this->timers[$name])) return;

                $this->timers[$name]--;
                $t = $this->timers[$name];

                $bar = $this->getProgressBar($t, 50);
                $player->sendTitle("§c⏳ " . $bar, "§7Time remaining: §c" . $t . "s");

                if($t <= 10 && $t > 0){
                    $player->getWorld()->addSound($player->getPosition(), new NoteSound($t));
                }

                if(in_array($t, [50,40,30,20,10,5,4,3,2,1])){
                    $player->sendMessage("§c⏳ Hospital: §f" . $t . " seconds left");
                }

                if($t <= 0){
                    $this->release($player);
                }
            }),
            20
        );
    }

    private function getProgressBar(int $current, int $max): string{
        $bars = 20;
        $filled = (int)round(($current / $max) * $bars);
        $empty = $bars - $filled;

        return "§a" . str_repeat("█", $filled) . "§7" . str_repeat("█", $empty);
    }

    private function release(Player $player): void{
        $n = $player->getName();
        if(isset($this->tasks[$n])) $this->tasks[$n]->cancel();
        unset($this->tasks[$n], $this->timers[$n]);

        $player->sendTitle("§a✔ Released!", "§fYou can now play");
        $player->sendMessage("§a✔ You have been released from hospital!");
        $player->getWorld()->addSound($player->getPosition(), new NoteSound(24));

        $this->getServer()->dispatchCommand($player, "lobby");
    }

    public function onMove(PlayerMoveEvent $event): void{
        $p = $event->getPlayer();
        $n = $p->getName();
        if(!isset($this->timers[$n]) || $this->hospital === null) return;

        if($event->getTo()->distance($this->hospital) > 10){
            $p->teleport($this->hospital);
            $p->sendTip("§c🚫 You cannot leave the hospital yet!");
        }
    }

    public function onQuit(PlayerQuitEvent $event): void{
        $n = $event->getPlayer()->getName();
        if(isset($this->tasks[$n])) $this->tasks[$n]->cancel();
        unset($this->tasks[$n], $this->timers[$n]);
    }
}
