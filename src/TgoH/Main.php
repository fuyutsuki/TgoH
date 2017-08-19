<?php
namespace TgoH;

#Base
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\Player;
use pocketmine\Server;

#Entity
use pocketmine\entity\Entity;

#etc
use pocketmine\entity\Item as ItemEntity;
use pocketmine\network\mcpe\protocol\ChangeDimensionPacket;
use pocketmine\scheduler\PluginTask;

#Commands
use pocketmine\command\Command;
use pocketmine\command\CommandSender;

#Utils
use pocketmine\utils\TextFormat as Color;
use TgoH\utils\tunedConfig as Config;

class Main extends PluginBase implements Listener{
  const NAME = 'The gate of Hell',
        VERSION = '√3';

  private $pk;

  public function onEnable(){
    $this->initialize();
    $this->makePacket();
    $sec = new sec($this);
    $this->getServer()->getScheduler()->scheduleRepeatingTask($sec, 60);
    $this->getServer()->getPluginManager()->registerEvents($this,$this);
    $this->getLogger()->info(Color::GREEN.self::NAME." ".self::VERSION." が読み込まれました。");
  }

  public function onCommand(CommandSender $s, Command $command, string $label, array $args): bool{
    if (strtolower($label) === "hell") {
      if (isset($args[0])) {
        $target = $this->getServer()->getPlayer($args[0]);
        if (is_null($target)) {
          if (isset($this->black[strtolower($args[0])])) {
            $s->sendMessage("§4[TgoH] そのプレイヤーは既にブラックリストに登録されています");
          }else {
            $s->sendMessage("§4[TgoH] 指定されたプレイヤーをブラックリストに登録しました");
            $date = new \DateTime();
            $date->setTimeZone(new \DateTimeZone('Asia/Tokyo'));
            $date_time = $date->format('Y-m-d H:i:s');
            $tn = strtolower($args[0]);
            $this->black[$tn] = $date_time;
            $this->blackList->set($tn, $date_time);
            $this->blackList->save();
          }
        }else {
          $tn = strtolower($target->getName());
          $s->sendMessage("§4[TgoH] 指定されたプレイヤーをブラックリストに登録し、地獄の門を開きました。");
          $this->theEnd($target);
          $date = new \DateTime();
          $date->setTimeZone(new \DateTimeZone('Asia/Tokyo'));
          $date_time = $date->format('Y-m-d H:i:s');
          $this->black[$tn] = $date_time;
          $this->blackList->set($tn, $date_time);
          $this->blackList->save();
          $s->sendMessage("§4[TgoH] The end.");
        }
      }else {
        return false;
      }
    }
    return true;
  }

  private function theEnd($target){
    $target->dataPacket($this->pk);
  }

  public function phantomKiller(){
    foreach ($this->getServer()->getOnlinePlayers() as $p) {
      if (isset($this->black[strtolower($p->getName())])) {
        $this->theEnd($p);
      }
    }
  }

  public function onDisable(){
    $this->getLogger()->info(Color::RED.self::NAME." が無効化されました。");
  }

  /****************************************************************************/
  //適当
  private function initialize(){
    $dir = $this->getDataFolder();
    $black = "blackList.json";
    //
    if(!file_exists($dir)) mkdir($dir);
    if(!file_exists($dir.$black)) file_put_contents($dir.$black, []);
    $this->blackList = new Config($dir.$black, Config::JSON);
    $this->black = $this->blackList->getAll();
  }

  private function makePacket(){
    $pk = new ChangeDimensionPacket();
    $pk->dimension = -1;
    $pk->x = 0;
    $pk->y = 0;
    $pk->z = 0;
    $this->pk = $pk;
  }
}

class sec extends PluginTask{
  function __construct($main){
    parent::__construct($main);
    $this->main = $main;
  }
  function onRun(int $tick){
    $this->main->phantomKiller();
  }
}
