<?php
    class People implements JsonSerializable{
        private int $id;
        private string $uid;
        private string $name;
        private string $address;
        private string $work;
        private float $income;
        
        public function __construct(int $id, string $uid, string $name, string $address, string $works, float $income) {
            $this -> id = $id;
            $this -> uid = $uid;
            $this -> name = $name;
            $this -> address = $address;
            $this -> work = $works;
            $this -> income = $income;
        }

        public function getId() {
            return $this -> id;
        }
        public function getUId() {
            return $this -> uid;
        }
        public function getName() {
            return $this -> name;
        }
        public function getAddress() {
            return $this -> address;
        }
        public function getWorks() {
            return $this -> work;
        }
        public function getIncome() {
            return $this -> income;
        }

        public function jsonSerialize(): mixed {
            return[
                'id' => $this->getId(),
                'uid' => $this->getUId(),
                'name' => $this->getName(),
                'address' => $this->getAddress(),
                'work' => $this->getWorks(),
                'income' => $this->getIncome()
            ];
        }
    }
?>