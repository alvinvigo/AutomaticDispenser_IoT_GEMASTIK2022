<?php
    require("algo.php");

    class Secret {
        private string $algo;
        private string $key;
        private string $iv;
        
        public function __construct() {
            $this->algo = getenv("CHIPER_ALGO");
            $this->key = getenv("SECRET_KEY");
            $this->iv = getenv("IV_KEY");
        }

        public function hashMsg(string $msg) {
            return hash("sha512",$msg);
        }

        public function verifyHash($hashedMsg, $msg) {
            return password_verify($hashedMsg, $msg);
        }

        public function encrypt(string $msg) {
            return openssl_encrypt($msg,$this->algo,$this->key,0,$this->iv);
        }

        public function decrypt(string $msg) {
            return openssl_decrypt($msg,$this->algo,$this->key,0,$this->iv);
        }
    }
?>