<?php

	namespace opkiler22789;

	use pocketmine\plugin\PluginBase;
	use pocketmine\utils\Config;
	use pocketmine\event\Listener;
	use pocketmine\command\Command;
	use pocketmine\command\CommandSender;
	use pocketmine\event\player\PlayerJoinEvent;
	use pocketmine\event\player\PlayerRespawnEvent;
	use pocketmine\utils\TextFormat;
	use pocketmine\Player;
	use pocketmine\command\ConsoleCommandSender; //Консоль КМД
	use pocketmine\Server;
	use pocketmine\math\Vector3;
	use pocketmine\event\player\PlayerMoveEvent;
	//use _64FF00\PurePerms\PurePerms;
	use opkiler22789\Epic;
	use BossBarAPI\API;

	class BossBar extends PluginBase implements Listener {
		

		public function onEnable()
		{
			$folder = $this->getDataFolder();
			if(!is_dir($folder))
				@mkdir($folder);
			$this->saveDefaultConfig();
			$this->getServer()->getPluginManager()->registerEvents($this, $this);
			$this->getServer()->getScheduler()->scheduleAsyncTask(new CheckVersionTask($this));
			$this->data = (new Config($folder.'data.yml', Config::YAML))->getAll();
			$this->getServer()->getScheduler()->scheduleRepeatingTask(new SaveSystem($this), 150);
			
		}
   public function update(){
		    $this->getServer()->getScheduler()->scheduleTask(new UpdaterTask($this, $this->getDescription()->getVersion()));
	  }
		

		public function onPlayerJoin(PlayerJoinEvent $event) {
			error_reporting(0);
		    $id = $this->data["id"] ;
			$BossText = $this->data['bossbars']["$id"]['text']; 
		    
		if($BossText != null){
		   $this->eid = API::addBossBar([$event->getPlayer()], "§a{$BossText}"); 
		    API::setTitle(sprintf("§e{$BossText}"), $this->eid); 
		}
		
		
			$per = $this->data['bossbars']["$id"]['per'];
					if($per != null){
					API::setPercentage($per, $this->eid); 
					}
		}
		
		
		
		
		
		public function onCommand(CommandSender $sender, Command $command, $label, array $args){
			$cfg = $this->getConfig();
			switch($command->getName()){
				
				case "bb": 
				if(count($args) == 0){
					$sender->sendMessage("§9§l—————§4Boss§eBar§9—————\n§6/bb set (id) (text) [%] Установка BossBar\n§6/bb del (id) Удаление BoosBar \n§6 /bb list - Получить список BossBar'ов \n §6/bb def (id) - установить id по умолчанию"); 
					}
					switch($args [0]){
				
				
				
				case "set": 
				if($sender->hasPermission("bossbar.add")){
					$args[2] = str_replace("%g", " ", $args[2]); 
					$epic = $args[4];
					$per = $args[3];
					$text = $args[2];
					$id = $args[1]; 
					if($text != null){
					if ($per != null){
					$this->data['bossbars']["$id"]['per'] = $per; 
					}else{
						$this->data['bossbars']["$id"]['per'] = 0; 
						}
					$this->data['bossbars']["$id"]['text'] = $text; 
					$this->data["id"] = $id; 
					
					$BossText = $this->data['bossbars']["$id"]['text']; 
					$geto = Server::getInstance()->getOnlinePlayers();
					$this->eid = API::addBossBar($geto, "§a{$BossText}"); 
					
					API::setTitle(sprintf("§e{$BossText}"), $this->eid); 
					$per = $this->data['bossbars']["$id"]['per'];
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
					
					$BossText = $this->data['bossbars']["$id"]['text'];
					if ($BossText != null){
                    
					$geto = Server::getInstance()->getOnlinePlayers();
					//$this->eid = API::addBossBar($geto, "§a{$BossText}"); 
					API::removeBossBar($geto, $this->eid);
					API::removeBossBar([$sender->getPlayer()], $this->eid);
					$this->data["bossbars"]["$id"] = null; 
					$sender->sendMessage("[§4Boss§eBar] Успешно: id: {$id} удалено"); 
					}else{
						$sender->sendMessage("[§4Boss§eBar]§4Ошибка id введен  не верно"); 
						}
						}else{
							$sender->sendMessage("[§4Boss§eBar]Недостаточно прав"); 
							}
							
					
					
					return true;
					case "list":
					if($sender->hasPermission("bossbar.list")){
					arsort($this->data["bossbars"] );
					foreach ($this->data["bossbars"] as $id => $data) {
						$sender->sendMessage("ID: {$id} Текст: {$data['text']} Заполнено на {$data['per']} %");
						}
						}else{
							$sender->sendMessage("[§4Boss§eBar]Недостаточно прав"); 
							}
					return true;
					case "def":
					if($sender->hasPermission("bossbar.def")){
					$this->data['id'] = $args[1];
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
