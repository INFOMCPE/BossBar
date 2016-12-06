<?php

	namespace opkiler22789;

	use pocketmine\scheduler\PluginTask;

	class SaveSystem extends PluginTask {

		public function __construct(BossBar $plugin) {
			parent::__construct($plugin);
			$this->p = $plugin;
			$plugin->getLogger()->info('INFO: Сохранение включено');
		}

		public function onRun($tick) {
			$this->p->save();
		}

	}

?>
