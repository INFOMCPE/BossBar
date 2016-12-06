<?php //by SalmonGER (https://github.com/SalmonGER)
namespace opkiler22789;

use pocketmine\scheduler\PluginTask;
use pocketmine\utils\TextFormat as TF;
use pocketmine\utils\Utils;
use pocketmine\utils\Config;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\Server;
use Casino\Casino;

class UpdaterTask extends PluginTask
{
    public function __construct($owner, $version){
        $this->name = $owner->getDescription()->getName();
        parent::__construct($owner);
        $urlh = file_get_contents('http://infomcpe.ru/updater.php?pluginname='.$this->name.''); 
        $urldata = json_decode($urlh); 
        $this->url = $urldata->downloadurl;
        $this->version = $owner->getDescription()->getVersion();
        $this->newversion = $urldata->version;
        $lang = $owner->getConfig()->get("lang");
        
    }

    public function onRun($currenttick){
        $file = Utils::getURL($this->url);
        $lang = 2;
        if($lang == 1){
$urlh = file_get_contents('http://infomcpe.ru/updater.php?pluginname=Casino_RU'); 
        $urll = json_decode($urlh);
        }
    if($lang == 2){
$urlh = file_get_contents('http://infomcpe.ru/updater.php?pluginname=Casino_EN'); 
        $urll = json_decode($urlh); 
                } 
        if($file){
            
                foreach(glob("plugins/*".$this->name."*.phar") as $phar){
                    unlink($phar);
                
                file_put_contents('plugins/'.$this->name.' v'.$this->newversion.'.phar', $file);
                if(!file_exists('plugins/'.$this->name.' v'.$this->newversion.'.phar')){
                        $this->getOwner()->getLogger()->error('Failed to download the update!');
                }else{
                    $this->getOwner()->getServer()->broadcastMessage(TF::RED.TF::BOLD."$urll->restart");
                    $this->getOwner()->getServer()->broadcastTip(TF::RED.TF::BOLD."$url->restart");
                    sleep(7);
                    // $command3 = "reload";
                        //  $this->getOwner()->dispatchCommand(new ConsoleCommandSender($command3));
                    $this->getOwner()->getServer()->shutdown();
                }
            }
        }else{
            $this->getOwner()->getLogger()->error('Error while downloading new phar!');
        }
    }
}

		
		
		
		
		
		public function onCommand(CommandSender $sender, Command $command, $label, array $args){
			$cfg = $this->getConfig();
			switch($command->getName()){
				
				case "bb": 
				if(count($args) == 0){
					$sender->sendMessage("§9§l—————§4Boss§eBar§9—————\n§6/bb set (id) (text) [%] Установка BossBar\n§6/bb del (id) Удаление BoosBar "); 
					}
					switch($args [0]){
				
				
				
				case "set": 
				if($sender->hasPermission("bossbar.add")){
					$args[2] = str_replace("%g", " ", $args[2]); 
					$per = $args[3];
					$text = $args[2];
					$id = $args[1]; 
					if($text != null){
					if ($per != null){
					$this->data["$id"]['per'] = $per; 
					}else{
						$this->data["$id"]['per'] = 0; 
						}
					$this->data["$id"]['text'] = $text; 
					$this->data["id"] = $id; 
					
					$BossText = $this->data["$id"]['text']; 
					$geto = Server::getInstance()->getOnlinePlayers();
					$this->eid = API::addBossBar($geto, "§a{$BossText}"); 
					
					API::setTitle(sprintf("§e{$BossText}"), $this->eid); 
					$per = $this->data["$id"]['per'];
					if($per != null){
					API::setPercentage($per, $this->eid); 
					}
					$sender->sendMessage("[§4Boss§eBar] Текст: {$text} выставлен на id: {$id}"); 
					}else{
						$sender->sendMessage("[§4Boss§eBar]Ты не указал(а) текст"); 
						}
						}else{
							$sender->sendMessage("[§4Boss§eBar]Недостаточно прав"); 
							}
					return true;
					
					case "del": 
					if($sender->hasPermission("bossbar.del")){
					$id = $args[1]; 
					
					$BossText = $this->data["$id"]['text'];
					if ($BossText != null){
                    
					$geto = Server::getInstance()->getOnlinePlayers();
					//$this->eid = API::addBossBar($geto, "§a{$BossText}"); 
					API::removeBossBar($geto, $this->eid);
					API::removeBossBar([$sender->getPlayer()], $this->eid);
					$this->data["$id"] = null; 
					$sender->sendMessage("[§4Boss§eBar] Успешно: id: {$id} удалено"); 
					}else{
						$sender->sendMessage("[§4Boss§eBar]§4Ошибка id введен  не верно"); 
						}
						}else{
							$sender->sendMessage("[§4Boss§eBar]Недостаточно прав"); 
							}
					
					
					return true;
					
				}
			}
		}
		
		
		
		public function save(){
			$cfg = new Config($this->getDataFolder().'data.yml', Config::YAML);
			$cfg->setAll($this->data);
			$cfg->save();
		}
		
		public function onDisable()
		{
			$cfg = new Config($this->getDataFolder().'data.yml', Config::YAML);
			$cfg->setAll($this->data);
			$cfg->save();
		}	
	}
	
?>
