<?php
    class Admin implements JsonSerializable{
        private string $username;
        private string $password;
        public function __construct(string $username, string $pass) {
            $this->username = $username;
            $this->password = $pass;          
        }

        public function getUsername() {
            return $this->username;
        }

        public function getPassword() {
            return $this->password;
        }

        public function jsonSerialize(): mixed {
            return [
                'username' => $this->getUsername(),
                'password' => $this->getPassword()
            ];
        }
    }
?>