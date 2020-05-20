<?php
namespace bingbing;
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\utils\Config;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\protocol\ModalFormResponsePacket;
use pocketmine\network\mcpe\protocol\ModalFormRequestPacket;

class mailing extends PluginBase implements Listener{
    public function onEnable(){
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->db = new Config($this->getDataFolder()."data.yml" , Config::YAML , []);
        $this->d = $this->db->getAll();
    }
    public function onJoin(PlayerJoinEvent $event){
        if (empty ( $this->d [ $event->getPlayer()->getName()] )) {
            
            $pk = new ModalFormRequestPacket();
            $pk->formId = 20200520;
            $pk->formData = $this->OpenUI();
            $event->getPlayer()->sendDataPacket($pk);
        }
    }
    public function response (DataPacketReceiveEvent $event){
        $pk = $event->getPacket();
        if ($pk instanceof ModalFormResponsePacket) {
            $pk->formId = 20200520;
            $data = json_decode($pk->formData , true);
            $this->d[$event->getPlayer()->getName()] = $data;
            $event->getPlayer()->sendMessage($data. "로 등록완료");
            $this->db->setAll( $this->d ) ;
            $this->db->save();
        }
    }
    public function OpenUI(){
        $a = [ "type" => "custom_form",
            "title" => "빙빙 안내 메일 동의",
            "content" => [
                "type"=> "label",
                "text" => "아래에 메일을 입력하시면 메일을 수신하는 것에 대하여 동의하십니다 . 개인정보는 쉽게 이용당할 수 있으니 믿을만한 서버에서만 작성 해주세요. \n 메일 작성법 : ****@naver.com 까지 다 입력해주세요 ",
                "type"=> "input"
                
                
            ]];
        return json_encode($a) ;
    }
    public function onCommand(CommandSender $sender, Command $command, string $label, array $args) : bool { 
        if ($command == "메일보내기" && $sender->isOp()) {
            $c = 0;
            foreach ($this->d as $b => $a)  {
                mail($a  , $args[0], $args);
                $c++ ;
            }
            $sender->sendMessage($c."건 전송 완료");
        }
    }
}
