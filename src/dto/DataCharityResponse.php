<?php
    class DataCharityResponse implements JsonSerializable{
        private int $id;
        private string $people_name;
        private string $people_address;
        private string $date_taken;
        private float $size_help;
        private string $kind_help;
        private string $kode_mesin;
        
        public function __construct(int $id, string $people_name, string $people_address, 
            string $date_taken, float $size_help, string $kind_help, string $kode_mesin) {
            $this->id = $id;
            $this->people_name = $people_name;
            $this->people_address = $people_address;       
            $this->date_taken = $date_taken;
            $this->size_help = $size_help;
            $this->kind_help = $kind_help;
            $this->kode_mesin = $kode_mesin;
        }

        public function getId() {
            return $this->id;
        }
        public function getPeople() {
            return $this->people;
        }
        public function getDateTaken() {
            return $this->date_taken;
        }
        public function getSizeHelp() {
            return $this->size_help;
        }
        public function getKindHelp() {
            return $this->kind_help;
        }
        public function getKodeMesin() {
            return $this->kode_mesin;
        }
        public function getPeopleName() {
            return $this->people_name;
        }
        public function getPeopleAddress() {
            return $this->people_address;
        }

        public function jsonSerialize(): mixed {
            return [
                'id' => $this->getId(),
                'name' => $this->getPeopleName(),
                'address' => $this->getPeopleAddress(),
                'date' => $this->getDateTaken(),
                'size' => $this->getSizeHelp(),
                'kind' => $this->getKindHelp(),
                'engine' => $this->getKodeMesin()
            ];
        }
    }
?>