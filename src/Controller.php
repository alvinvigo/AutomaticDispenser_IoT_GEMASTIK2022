<?php
    require("MainData.php");
    require("Secret.php");

    class Controller {
        private PDO $pdo;
        private $reqMethod;
        private Secret $secret;
        private $cookie;
        private $div1;
        private $div2;
        private $div3;
        private AdminController $admin;
        private DataCharityController $charity;
        private EngineController $engine;
        private PeopleController $people;

        public function __construct($requestMethod,$db) {
            $this->pdo = $db;
            $this->cookie = getenv("COOKIE_NAME");
            $this->div1 = getenv("DIVIDER_1");
            $this->div2 = getenv("DIVIDER_2");
            $this->div3 = getenv("DIVIDER_3");
            $this->secret = new Secret();
            $this->reqMethod = $requestMethod;
            $this->admin = new AdminController($db,$requestMethod,$this->secret);
            $this->charity = new DataCharityController($db,$requestMethod,$this->secret);
            $this->engine = new EngineController($db,$requestMethod,$this->secret);
            $this->people = new PeopleController($db,$requestMethod);
        }

        public function process($uri) {
            $arrURI = explode('/',$uri);
            if ($arrURI[2] === 'admin') {
                if ($arrURI[3] === 'login') {
                    $this->admin->login();
                }
                if ($arrURI[3] === 'add') {
                    $this->admin->addAdmin();
                }
            }
            elseif ($arrURI[2] === 'people') {
                if ($arrURI[3] === 'data') {
                    if ($arrURI[4] === 'get') {
                        $this->people->getPeopleDetails();
                    }
                    if ($arrURI[4] === 'getAll') {
                        $this->people->getAllPeople();
                    }
                    if ($arrURI[4] === 'add') {
                        if ($this->checkValidation()) {
                            $this->people->addPeople();
                        }
                    }
                    if ($arrURI[4] === 'modify') {
                        if ($this->checkValidation()) {
                            $this->people->modifyPeople();
                        }
                    }
                    if ($arrURI[4] === 'delete') {
                        if ($this->checkValidation()) {
                            $this->people->deletePeople();
                        }
                    }
                } 
                elseif ($arrURI[3] === 'charity') {
                    if ($arrURI[4] === 'getAllF') {
                        $this->charity->getAllF();
                    }
                    if ($arrURI[4] === 'getAll') {
                        $this->charity->getAll();
                    }
                    if ($arrURI[4] === 'getCountDataMonth') {
                        $this->charity->getCountDataTotalMonth();
                    }
                    if ($arrURI[4] === 'getCountDataType') {
                        $this->charity->getCountDataTotalType();
                    }
                    if ($arrURI[4] === 'getAllFilter') {
                        $this->charity->getWithFilter();
                    }
                    if ($arrURI[4] === 'add') {
                        $this->charity->addNew();
                    }
                } else {
                    header('HTTP/1.1 404 Not Found');
                }  
            }
            elseif ($arrURI[2] === 'engine') {
                if ($arrURI[3] === 'get') {
                    $this->engine->getEngineDetails();
                }
                if ($arrURI[3] === 'size') {
                    $this->engine->getSizeHelp();
                }
                if ($arrURI[3] === 'getAllF') {
                    $this->engine->getAllEngineF();
                }
                if ($arrURI[3] === 'getAll') {
                    $this->engine->getAllEngine();
                }
                if ($arrURI[3] === 'add') {
                    if ($this->checkValidation()) {
                        $this->engine->addEngine();
                    }
                }
                if ($arrURI[3] === 'modify') {
                    if ($this->checkValidation()) {
                        $this->engine->modifyEngine();
                    }
                }
                if ($arrURI[3] === 'delete') {
                    if ($this->checkValidation()) {
                        $this->engine->deleteEngine();
                    }
                } 
            }
            else {
                header('HTTP/1.1 404 Not Found');
            }
        }

        public function checkValidation() {
            $cookie = $_COOKIE[$this->cookie];
            $admin = getenv("ADMIN_NAME");
            if (isset($cookie)) {
                $data = base64_decode($cookie);
                $plain = $this->secret->decrypt($data);
                $arrData = preg_split('/' . sprintf('%s|%s|%s',$this->div1,$this->div2,$this->div3) . '/',$plain);
                if (count($arrData) == 4) {
                    return true;
                }
            }
            return false;
        }
    }

    class AdminController {
        private PDO $pdo;
        private string $reqMethod;
        private $cookie;
        private $div1;
        private $div2;
        private $div3;
        private Secret $secret;
        public function __construct(PDO $db, $requestMethod, $secret) {
            $this->pdo = $db;
            $this->reqMethod = $requestMethod;
            $this->secret = $secret;
            $this->cookie = getenv("COOKIE_NAME");
            $this->div1 = getenv("DIVIDER_1");
            $this->div2 = getenv("DIVIDER_2");
            $this->div3 = getenv("DIVIDER_3");
        }

        public function login() {
            if ($this->reqMethod == 'POST') {
                $param = $_POST['username'];
                $pass = $_POST['password'];
                if (isset($param) && isset($param)) {
                    $data = getAdminInfo($this->pdo,$param);
                    $arr = str_split($data->getUsername(),2);
                    if (count($arr) !== 4) {
                        for ($i=0; $i < 5-count($arr); $i++) { 
                            array_push($arr,'xd');
                        }
                    }
                    if (password_verify($pass,$data->getPassword())) {
                        header('HTTP/1.1 200 OK');
                        setcookie($this->cookie,base64_encode($this->secret->encrypt(sprintf("%s%s%s%s%s%s%s",$arr[0],$this->div1,$arr[1],$this->div2,
                            $arr[2],$this->div3,$arr[3]))),time()+60*60*24,'/','',false,true);
                        echo json_encode(['data' => 'success']);
                        return;
                    }
                }
            }
            header('HTTP/1.1 400 Bad Request');
        }

        public function addAdmin() {
            if ($this->reqMethod == 'POST') {
                $adminName = getenv("ADMIN_NAME");
                $adminPass = getenv("ADMIN_PASSWORD");
                $data = $this->pdo -> query("select id from admin where name = '$adminName'");
                if ($data -> rowCount() <= 0) {
                    $data1 = $this->pdo -> prepare("insert into admin(name,password) values(?,?)");
                    $res = $data1 -> execute(array($adminName,password_hash($adminPass,PASSWORD_BCRYPT,array('cost' => 12))));
                    if (!is_null($res)) {
                        print("Data Admin berhasil ditambahkan");
                    } else {
                        print("Data Admin gagal ditambahkan");
                    }
                } else {
                    $row = $data->fetch(PDO::FETCH_ASSOC);
                    $data1 = $this->pdo -> prepare("update admin set name = ?, password = ? where id = ?");
                    $res = $data1 -> execute(array($adminName,password_hash($adminPass,PASSWORD_BCRYPT,array('cost' => 12)),$row["id"]));
                    if (!is_null($res)) {
                        print("Data Admin berhasil dirubah");
                    } else {
                        print("Data Admin gagal dirubah");
                    }
                }
            }
        }
    }

    class DataCharityController {
        private PDO $pdo;
        private string $reqMethod;
        private Secret $secret;
        public function __construct(PDO $db, $requestMethod, Secret $secret) {
            $this->pdo = $db;
            $this->reqMethod = $requestMethod;
            $this->secret = $secret;
        }

        public function getAllF(){
            if ($this->reqMethod == 'GET') {
                $ID = $_GET["id"];
                $sizePage = $_GET["size"];
                if (isset($ID) && isset($sizePage)) {
                    $data = getListDataPeopleCharity($this->pdo, $ID, $sizePage);
                    header('HTTP/1.1 200 OK');
                    echo json_encode($data);
                    return;
                }
            }
            header('HTTP/1.1 400 Bad Request');
        }

        public function getAll(){
            if ($this->reqMethod == 'GET') {
                $data = getListDataPeopleCharityAll($this->pdo);
                header('HTTP/1.1 200 OK');
                echo json_encode($data);
                return;
            }
            header('HTTP/1.1 400 Bad Request');
        }

        public function getCountDataTotalMonth(){
            if ($this->reqMethod == 'GET') {
                $data = getListDataDateCount($this->pdo);
                header('HTTP/1.1 200 OK');
                echo json_encode($data);
                return;
            }
            header('HTTP/1.1 400 Bad Request');
        }

        public function getCountDataTotalType(){
            if ($this->reqMethod == 'GET') {
                $data = getListDataDisplayCount($this->pdo);
                header('HTTP/1.1 200 OK');
                echo json_encode($data);
                return;
            }
            header('HTTP/1.1 400 Bad Request');
        }

        public function getWithFilter() {
            if ($this->reqMethod == 'GET') {
                $ID = 0;
                if(!is_null($_GET["id"])){$ID=$_GET["id"];}
                $pointDate = '';
                if(!is_null($_GET["date"])){$pointDate=$_GET["date"];}
                $sizePage = 0; 
                if(!is_null($_GET["size"])){$sizePage=$_GET["size"];}
                $UID = '';
                if(!is_null($_GET["uid"])){$UID=$_GET["uid"];}
                $dateFrom = '';
                if(!is_null($_GET["dateFrom"])){$dateFrom=$_GET["dateFrom"];}
                $dateTo = '';
                if(!is_null($_GET["dateTo"])){$dateTo=$_GET["dateTo"];}
                $engine = ''; 
                if(!is_null($_GET["engine"])){$engine=$_GET["engine"];}
                $sort = 'asc';
                if(!is_null($_GET["direction"])){$sort=$_GET["direction"];}
                if (isset($UID) || (isset($ID) && isset($sizePage) && isset($pointDate))) {
                    try {
                        $data = getListDataPeopleCharityFilter($this->pdo, $ID, $sizePage, $UID, $dateFrom, $dateTo, $pointDate, 
                        $engine, $sort);
                        header('HTTP/1.1 200 OK');
                        echo json_encode($data);
                        return;
                    } catch (Exception $e) {
                        header('HTTP/1.1 400 Bad Request');
                        echo json_encode(['msg' => $e->getMessage()]);
                        return;
                    }
                }
            }
            header('HTTP/1.1 400 Bad Request');
        }

        public function addNew() {
            if ($this->reqMethod == 'POST') {
                $msg = $_POST["msg"];
                $decode = base64_decode($msg);
                // $data = $this->secret->decrypt($decode);
                $arrData = explode('<>',$decode);
                if (isset($arrData[0]) && isset($arrData[1])) {
                    try {
                        $res = addDataPeopleCharity($this->pdo, $arrData[0], $arrData[1]);
                        header('HTTP/1.1 200 OK');
                        // $this->secret->encrypt($res);
                        echo json_encode(base64_encode($res));
                    } catch(Exception $e) {
                        header('HTTP/1.1 400 Bad Request');
                        // $this->secret->encrypt($e->getMessage());
                        echo json_encode(base64_encode($e->getMessage()));
                    }
                    return;
                }
            }
            header('HTTP/1.1 400 Bad Request');
        }
    }

    class EngineController {
        private PDO $pdo;
        private string $reqMethod;
        private Secret $secret;
        public function __construct(PDO $db, $requestMethod, Secret $secret) {
            $this->pdo = $db;
            $this->reqMethod = $requestMethod;
            $this->secret = $secret;
        }

        public function getSizeHelp() {
            if ($this->reqMethod == 'GET') {
                $msg = $_GET["msg"];
                $decode = base64_decode($msg);
                // $uid = $this->secret->decrypt($decode);
                // echo $decode;
                if (isset($decode)) {
                    $res = getDataSizeHelp($this->pdo,$decode);
                    header('HTTP/1.1 200 OK');
                    echo json_encode($res);
                    return;
                }
            }
            header('HTTP/1.1 400 Bad Request');
        }

        public function getEngineDetails() {
            if ($this->reqMethod == 'GET') {
                $uid = $_GET["engine"];
                if (isset($uid)) {
                    $res = getDataEngine($this->pdo,$uid);
                    header('HTTP/1.1 200 OK');
                    echo json_encode($res);
                    return;
                }
            }
            header('HTTP/1.1 400 Bad Request');
        }

        public function getAllEngineF() {
            if ($this->reqMethod == 'GET') {
                $id = $_GET["id"];
                $sizePage = $_GET["size"];
                if (isset($id) && isset($sizePage)) {
                    $res = getListDataEngineF($this->pdo,$id,$sizePage);
                    header('HTTP/1.1 200 OK');
                    echo json_encode($res);
                    return;
                }
            }
            header('HTTP/1.1 400 Bad Request');
        }

        public function getAllEngine() {
            if ($this->reqMethod == 'GET') {
                $res = getListDataEngine($this->pdo);
                header('HTTP/1.1 200 OK');
                echo json_encode($res);
                return;
            }
            header('HTTP/1.1 400 Bad Request');
        }

        public function addEngine() {
            if ($this->reqMethod == 'POST') {
                $name = $_POST["name"];
                $uid = $_POST["uid"];
                $alt = $_POST["address"];
                $size = $_POST["sizeHelp"];
                $kind = $_POST["kindHelp"];
                $pickTime = $_POST["pickTime"];
                if (isset($name) && isset($uid) && isset($alt) && isset($size) && isset($kind) && isset($pickTime)) {
                    $res = addDataEngine($this->pdo,new Engine(0,$name,$uid,$alt,$size,$kind,$pickTime));
                    if (!is_null($res)) {
                        header('HTTP/1.1 200 OK');
                        echo json_encode(['data' => $res]);
                    } else {
                        header('HTTP/1.1 400 Bad Request');
                        echo json_encode(['msg' => 'cannot add new engine']);
                    }
                    return;
                }
            }
            header('HTTP/1.1 400 Bad Request');
        }

        public function modifyEngine() {
            if ($this->reqMethod == 'POST') {
                $name = $_POST["name"];
                $uid = $_POST["uid"];
                $alt = $_POST["address"];
                $size = $_POST["sizeHelp"];
                $kind = $_POST["kindHelp"];
                $pickTime = $_POST["pickTime"];
                if (isset($name) && isset($uid) && isset($alt) && isset($size) && isset($kind) && isset($pickTime)) {
                    $res = modifyEngine($this->pdo,new Engine(0,$name,$uid,$alt,$size,$kind,$pickTime),$uid);
                    if (!is_null($res)) {
                        header('HTTP/1.1 200 OK');
                        echo json_encode(['data' => $res]);
                    } else {
                        header('HTTP/1.1 400 Bad Request');
                        echo json_encode(['msg' => 'cannot update engine']);
                    }
                    return;
                }
            }
            header('HTTP/1.1 400 Bad Request');
        }

        public function deleteEngine() {
            if ($this->reqMethod == 'POST') {
                $uid = $_POST["uid"];
                if (isset($uid)) {
                    try {
                        $res = removeEngine($this->pdo,$uid);
                        if (isset($res)) {
                            header('HTTP/1.1 200 OK');
                            echo json_encode(['data' => $res]);
                            return;
                        } 
                        header('HTTP/1.1 400 Bad Request');
                        echo json_encode(['msg' => 'cannot delete people']);  
                    } catch (Exception $e) {
                        header('HTTP/1.1 400 Bad Request');
                        echo json_encode(['msg' => $e->getMessage()]);
                    }
                }
                return;
            }
            header('HTTP/1.1 400 Bad Request');
        }
    }

    class PeopleController {
        private PDO $pdo;
        private string $reqMethod;
        public function __construct(PDO $db, $requestMethod) {
            $this->pdo = $db;
            $this->reqMethod = $requestMethod;
        }

        public function getPeopleDetails() {
            if ($this->reqMethod == 'GET') {
                $uid = $_GET["uid"];
                if (isset($uid)) {
                    $res = getDataPeopleUID($this->pdo,$uid);
                    header('HTTP/1.1 200 OK');
                    echo json_encode($res);
                    return;
                }
            }
            header('HTTP/1.1 400 Bad Request');
        }

        public function getAllPeople() {
            if ($this->reqMethod == 'GET') {
                $id = $_GET["id"];
                $sizePage = $_GET["size"];
                if (isset($id) && isset($sizePage)) {
                    $res = getListDataPeople($this->pdo,$id,$sizePage);
                    header('HTTP/1.1 200 OK');
                    echo json_encode($res);
                    return;
                }
            }
            header('HTTP/1.1 400 Bad Request');
        }

        public function addPeople() {
            if ($this->reqMethod == 'POST') {
                $name = $_POST["name"];
                $uid = $_POST["uid"];
                $alt = $_POST["address"];
                $works = $_POST["works"];
                $income = $_POST["income"];
                if (isset($uid) && isset($name) && isset($income) && isset($works)) {
                    $res = addDataPeople($this->pdo,new People(0,$uid,$name,$alt,$works,$income));
                    if (!is_null($res)) {
                        header('HTTP/1.1 200 OK');
                        echo json_encode(['data' => $res]);
                    } else {
                        header('HTTP/1.1 400 Bad Request');
                        echo json_encode(['msg' => 'cannot add new people']);
                    }
                    return;
                }
            }
            header('HTTP/1.1 400 Bad Request');
        }

        public function modifyPeople() {
            if ($this->reqMethod == 'POST') {
                $name = $_POST["name"];
                $uid = $_POST["uid"];
                $alt = $_POST["address"];
                $works = $_POST["works"];
                $income = $_POST["income"];
                if (isset($uid) && isset($name) && isset($income) && isset($works)) {
                    $res = modifyPeople($this->pdo,new People(0,$uid,$name,$alt,$works,$income),$uid);
                    if (!is_null($res)) {
                        header('HTTP/1.1 200 OK');
                        echo json_encode(['data' => $res]);
                    } else {
                        header('HTTP/1.1 400 Bad Request');
                        echo json_encode(['msg' => 'cannot update people']);
                    }
                    return;
                }
            }
            header('HTTP/1.1 400 Bad Request');
        }

        public function deletePeople() {
            if ($this->reqMethod == 'POST') {
                $uid = $_POST["uid"];
                if (isset($uid)) {
                    try {
                        $res = removePeople($this->pdo,$uid);
                        if (isset($res)) {
                            header('HTTP/1.1 200 OK');
                            echo json_encode(['data' => $res]);
                            return;
                        } 
                        header('HTTP/1.1 400 Bad Request');
                        echo json_encode(['msg' => 'cannot delete people']);  
                    } catch (Exception $e) {
                        header('HTTP/1.1 400 Bad Request');
                        echo json_encode(['msg' => $e->getMessage()]);
                    }
                }
                return;
            }
            header('HTTP/1.1 400 Bad Request');
        }
    }
?>