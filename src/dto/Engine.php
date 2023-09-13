<?php
    class Engine implements JsonSerializable{
        private int $id;
        private string $name;
        private string $uid;
        private string $address;
        private float $size_help;
        private string $kind_help;
        private int $pick_time;
        
        public function __construct(int $id, string $name, string $uid, string $address, float $size_help, string $kind_help, int $pick_time) {
            $this->id=$id;
            $this->name=$name;
            $this->uid=$uid;
            $this->address=$address;
            $this->size_help=$size_help;
            $this->kind_help=$kind_help;
            $this->pick_time=$pick_time;
        }

        public function getId() {
            return $this->id;
        }
        public function getName() {
            return $this->name;
        }
        public function getUID() {
            return $this->uid;
        }
        public function getAddress() {
            return $this->address;
        }
        public function getSizeHelp() {
            return $this->size_help;
        }
        public function getKindHelp() {
            return $this->kind_help;
        }
        public function getPickTime() {
            return $this->pick_time;
        }
        public function jsonSerialize(): mixed {
            return [
                'id' => $this->getId(),
                'uid' => $this->getUID(),
                'name' => $this->getName(),
                'address' => $this->getAddress(),
                'kind' => $this->getKindHelp(),
                'size' => $this->getSizeHelp(),
                'pickTime' => $this->getPickTime()
            ];
        }
    }
?>