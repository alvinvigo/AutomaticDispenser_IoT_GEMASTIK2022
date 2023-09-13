<?php

use function PHPSTORM_META\map;

    require("dto/Admin.php");
    require("dto/DataCharityResponse.php");
    require("dto/Engine.php");
    require("dto/People.php");

    function getDataPeopleUID(PDO $conn, string $uid) {
        $data = $conn->query("select * from people where UID = '$uid'");
        if($data->rowCount() > 0){
            $row = $data->fetch(PDO::FETCH_ASSOC);
            return new People($row["id"],$row["UID"],$row["nama"],$row["alamat"],$row["pekerjaan"],$row["gaji"]);
        }
        return null;
    }

    function getListDataPeople(PDO $conn, int $id, int $size) {
        $arrData = array();
        $data = $conn->query("select * from people where id > $id limit $size");
        if($data->rowCount() > 0){
            while($row = $data->fetch(PDO::FETCH_ASSOC)) {
                array_push($arrData,new People($row["id"],$row["UID"],$row["nama"],$row["alamat"],$row["pekerjaan"],$row["gaji"]));
            }
        }
        return $arrData;
    }

    function getListDataPeopleCharity(PDO $conn, int $id, int $size) {
        $arrData = array();
        $data = $conn->query("select c.id as id,d.nama as nama,d.alamat as alamat,c.date_taken as date_taken,c.size_help as size_help,
            c.kind_help as kind_help, e.uid as uid from charity as c inner join people as d on c.people = d.id 
            inner join engine e on c.kode_mesin = e.id where c.id > $id limit $size");
        if($data->rowCount() > 0){
            while($row = $data->fetch(PDO::FETCH_ASSOC)) {
                array_push($arrData,new DataCharityResponse(
                    $row["id"],$row["nama"],$row["alamat"],$row["date_taken"],$row["size_help"],$row["kind_help"],$row["uid"]));
            }
        }
        return $arrData;
    }

    function getListDataPeopleCharityAll(PDO $conn) {
        $arrData = array();
        $data = $conn->query("select c.id as id,d.nama as nama,d.alamat as alamat,c.date_taken as date_taken,c.size_help as size_help,
            c.kind_help as kind_help, e.uid as uid from charity as c inner join people as d on c.people = d.id 
            inner join engine e on c.kode_mesin = e.id");
        if($data->rowCount() > 0){
            while($row = $data->fetch(PDO::FETCH_ASSOC)) {
                array_push($arrData,new DataCharityResponse(
                    $row["id"],$row["nama"],$row["alamat"],$row["date_taken"],$row["size_help"],$row["kind_help"],$row["uid"]));
            }
        }
        return $arrData;
    }

    function getListDataDateCount(PDO $conn) {
        $arrData = [];
        $year = date("Y");
        $data = $conn->query("select month(date_taken) as month, count(id) as cid from charity 
            where date_taken >= '$year-01-01' and date_taken <= '$year-12-31' 
            group by month(date_taken)");
        if($data->rowCount() > 0){
            while($row = $data->fetch(PDO::FETCH_ASSOC)) {
                array_push($arrData,[
                    "month" => $row["month"], "count" => $row["cid"]
                ]);
            }
        }
        return $arrData;
    }

    function getListDataDisplayCount(PDO $conn) {
        $arrData = [];
        $data = $conn->query("select kind_help, count(id) as cid from charity group by kind_help");
        if($data->rowCount() > 0){
            while($row = $data->fetch(PDO::FETCH_ASSOC)) {
                array_push($arrData,[
                    "kind_help" => $row["kind_help"], "count" => $row["cid"]
                ]);
            }
        }
        return $arrData;
    }

    function getListDataPeopleCharityFilter(PDO $conn, $id, $size, $uid, $date_taken_from, 
        $date_taken_to, $date_point, $uidEngine, $directionOrder = "asc") {
        $arrData = array();
        $query1 = " where";
        $query2 = " order by c.date_taken,c.id $directionOrder limit $size";
        if (!empty($uid)) {
            $query1 .= " d.UID = '$uid'";
            $query2 = "";
        } else {
            if (!empty($date_point) && ($id !== 0) && ($size !== 0)) {
                $query1 .= " (";
                $datePoint = date("Y-m-d",strtotime($date_point));
                $add = "((c.date_taken,c.id) > ('$datePoint',$id))";
                if (!empty($date_taken_from) && !empty($date_taken_to)) {
                    $dateFrom = date("Y-m-d",strtotime($date_taken_from));
                    $dateTo = date("Y-m-d",strtotime($date_taken_to));
                    $query1 .= "c.date_taken between '$dateFrom' and '$dateTo' and ";
                }
                if (!empty($uidEngine)) {
                    $query1 .= "e.uid = '$uidEngine' and ";
                }
                $query1 .= "$add)";
                if (substr($query1, -4) == "and ") {
                    $query1 = preg_replace('/ and $/','',$query1);
                } 
            } else {
                throw new RuntimeException("parameter utama(id,date,size) tidak ada/belum lengkap !!!");
            }
        }
        $data = $conn->query("select c.id as id,d.nama as nama,d.alamat as alamat,c.date_taken as date_taken,c.size_help as size_help,
                c.kind_help as kind_help, e.uid as uid from charity as c inner join people as d on c.people = d.id 
                inner join engine e on c.kode_mesin = e.id" . $query1 . $query2);
        if($data->rowCount() > 0){
            while($row = $data->fetch(PDO::FETCH_ASSOC)) {
                array_push($arrData,new DataCharityResponse(
                    $row["id"],$row["nama"],$row["alamat"],$row["date_taken"],$row["size_help"],$row["kind_help"],$row["uid"]));
            }
        }
        return $arrData;
    }

    function getDataSizeHelp(PDO $conn, string $uid) {
        $data = $conn->query("select * from engine where uid = '$uid'");
        if($data->rowCount() > 0){
            $row = $data->fetch(PDO::FETCH_ASSOC);
            return (float) $row["size_help"];
        }
        return -1.0;
    }

    function getDataEngine(PDO $conn, string $uid) {
        $data = $conn->query("select * from engine where uid = '$uid'");
        if($data->rowCount() > 0){
            $row = $data->fetch(PDO::FETCH_ASSOC);
            return new Engine($row["id"],$row["name"],$row["uid"],$row["alamat"],$row["size_help"],$row["kind_help"],$row["pick_time"]);
        }
        return null;
    }

    function getListDataEngineF(PDO $conn, int $id, int $size) {
        $arrData = array();
        $data = $conn->query("select * from engine where id > $id limit $size");
        if($data->rowCount() > 0){
            while($row = $data->fetch(PDO::FETCH_ASSOC)) {
                array_push($arrData,new Engine($row["id"],$row["name"],$row["uid"],$row["alamat"],$row["size_help"],$row["kind_help"],$row["pick_time"]));
            }
        }
        return $arrData;
    }

    function getListDataEngine(PDO $conn) {
        $arrData = array();
        $data = $conn->query("select * from engine");
        if($data->rowCount() > 0){
            while($row = $data->fetch(PDO::FETCH_ASSOC)) {
                array_push($arrData,new Engine($row["id"],$row["name"],$row["uid"],$row["alamat"],$row["size_help"],$row["kind_help"],$row["pick_time"]));
            }
        }
        return $arrData;
    }

    function addDataPeople(PDO $conn, People $people) {
        $data = $conn->prepare("insert into people(UID,nama,alamat,pekerjaan,gaji) values(?,?,?,?,?)");
        $res = $data->execute(array($people->getUId(),$people->getName(),$people->getAddress(),$people->getWorks(),$people->getIncome()));
        if (isset($res)) {
            return "success";    
        }
        return null;
    }

    function addDataPeopleCharity(PDO $conn, string $uid, string $engine) {
        //decrypt harusnya
        $data1 = $conn->query("select * from engine where uid = '$engine'");
        $data2 = $conn->query("select * from people where UID = '$uid'");
        $obj = getenv("RESPONSE_CHARITY");
        if($data1->rowCount() > 0 && $data2->rowCount() > 0 ){
            $row1 = $data1->fetch(PDO::FETCH_ASSOC);
            $row2 = $data2->fetch(PDO::FETCH_ASSOC);
            $hiw = date("Y-m-d");
            if (!is_null($row2["last_taken"])) {
                $hiw = date("Y-m-d",strtotime(str_replace('-','/',$row2["last_taken"]) . "+" . $row1["pick_time"] . " days"));
            }
            $date = date("Y-m-d");
            if ($date >= $hiw) {
                $data3 = $conn -> prepare("insert into charity(date_taken,size_help,kind_help,people,kode_mesin) values(?,?,?,?,?)");
                $res = $data3 -> execute(array($date, $row1["size_help"], $row1["kind_help"], $row2["id"], $row1["id"]));
                $data3 = $conn -> prepare("update people set last_taken = ? where id = ?");
                $res1 = $data3 -> execute(array($date, $row2["id"]));
                if ($res && $res1) {
                    // encrypt harusnya
                    return $uid . $obj . $engine;
                }
                throw new RuntimeException("gagal menyimpan data, silahkan menuju bagian informasi");    
            }
            throw new RuntimeException("sepertinya anda sudah mengambil, silahkan menuju bagian informasi untuk detailnya");
        }
        throw new RuntimeException("data kosong, silahkan mendaftar terlebih dahulu di bagian informasi");
    }

    function addDataEngine(PDO $conn, Engine $engine) {
        $data = $conn->prepare("insert into engine(name,uid,alamat,size_help,kind_help,pick_time) values(?,?,?,?,?,?)");
        $bl = $data -> execute(array($engine->getName(),$engine->getUID(),$engine->getAddress(),
            $engine->getSizeHelp(),$engine->getKindHelp(),$engine->getPickTime()));
        if($bl) {
            return "success";
        }
        return null;
    }

    function modifyPeople(PDO $conn, People $people, string $uid) {
        $data = $conn->prepare("update people set nama = ?,alamat = ?,pekerjaan = ?,gaji = ? where UID = ?");
        $bl = $data -> execute(array($people->getName(),$people->getAddress(),$people->getWorks(),
            $people->getIncome(),$uid));
        if($bl) {
            return "success";
        }
        return null;
    }

    function modifyEngine(PDO $conn, Engine $engine, string $uid) {
        $data = $conn->prepare("update engine set name = ?,alamat = ?,size_help = ?,kind_help = ?,pick_time =? where uid = ?");
        $bl = $data -> execute(array($engine->getName(),$engine->getAddress(),$engine->getSizeHelp(),
            $engine->getKindHelp(),$engine->getPickTime(),$uid));
        if($bl) {
            return "success";
        }
        return null;
    }

    function backupCharity(PDO $conn, string $date_taken, float $size_help, string $kind_help, string $last_taken, Engine $engine, People $people){
        $data = $conn->prepare("insert backupcharity(date_taken,size_help,kind_help,people_UID,people_name,people_alamat,people_pekerjaan,
            people_gaji,last_taken,engine_name,engine_uid,engine_alamat,engine_size_help,engine_kind_help,engine_pick_time) values(
            ?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
        $bl = $data -> execute(array($date_taken,$size_help,$kind_help,$people->getUId(),$people->getName(),$people->getAddress(),
            $people->getWorks(),$people->getIncome(),$last_taken, $engine->getName(),$engine->getUID(),$engine->getAddress(),$engine->getSizeHelp(),
            $engine->getKindHelp(),$engine->getPickTime()));
        if($bl) {
            return true;
        }
        return false;
    }

    function removePeople(PDO $conn, string $uid) {
        #backup
        $data = $conn->query("select a.id as id,a.date_taken as taken,a.size_help as size,a.kind_help as kind,p.UID as people_UID,
        p.nama as people_name,p.alamat as people_alamat,p.pekerjaan as people_pekerjaan,p.gaji as people_gaji,
        p.last_taken as people_last_taken,e.name as engine_name,e.uid as engine_uid,e.alamat as engine_alamat,
        e.size_help as engine_size_help,e.kind_help as engine_kind_help,e.pick_time as engine_pick_time from charity as a
        inner join people as p on a.people = p.id inner join engine as e on a.kode_mesin = e.id where p.UID = '$uid'");
        $bl = false;
        if ($data->rowCount() > 0) {
            while($row = $data->fetch(PDO::FETCH_ASSOC)) {
                if(!backupCharity($conn,$row["taken"],$row["size"],$row["kind"],$row["people_last_taken"],
                new Engine(0,$row["engine_name"],$row["engine_uid"],$row["engine_alamat"],$row["engine_size_help"],
                $row["engine_kind_help"],$row["engine_name"],$row["engine_pick_time"]),new People(0,$row["people_UID"],
                $row["people_name"],$row["people_alamat"],$row["people_pekerjaan"],$row["people_gaji"]))) {
                    $bl = false;
                    throw new RuntimeException("failed to remove because cannot make backup data");
                }
                $remove = $conn->prepare("delete from charity where id=?");
                $bn = $remove->execute(array($row["id"]));
                if (!isset($bn)) {
                    $bl = false;
                    throw new RuntimeException("failed to remove, check your charity data");
                }
                $bl = true;
            }
        }
        if ($bl) {
            $remove = $conn->prepare("delete from people where UID=?");
            $bn = $remove->execute(array($uid));
            if (!isset($bn)) {
                throw new RuntimeException("failed to remove people");
            }
        } else {
            throw new RuntimeException("failed to remove people");
        }
        return "success";
    }

    function removeEngine(PDO $conn, string $uid) {
        $data = $conn->query("select a.id as id,a.date_taken as taken,a.size_help as size,a.kind_help as kind,p.UID as people_UID,
        p.nama as people_name,p.alamat as people_alamat,p.pekerjaan as people_pekerjaan,p.gaji as people_gaji,
        p.last_taken as people_last_taken,e.name as engine_name,e.uid as engine_uid,e.alamat as engine_alamat,
        e.size_help as engine_size_help,e.kind_help as engine_kind_help,e.pick_time as engine_pick_time from charity as a
        inner join people as p on a.people = p.id inner join engine as e on a.kode_mesin = e.id where e.uid = '$uid'");
        $bl = false;
        if ($data->rowCount() > 0) {
            while($row = $data->fetch(PDO::FETCH_ASSOC)) {
                if(!backupCharity($conn,$row["taken"],$row["size"],$row["kind"],$row["people_last_taken"],
                new Engine(0,$row["engine_name"],$row["engine_uid"],$row["engine_alamat"],$row["engine_size_help"],
                $row["engine_kind_help"],$row["engine_name"],$row["engine_pick_time"]),new People(0,$row["people_UID"],
                $row["people_name"],$row["people_alamat"],$row["people_pekerjaan"],$row["people_gaji"]))) {
                    $bl = false;
                    throw new RuntimeException("failed to remove because cannot make backup data");
                }
                $remove = $conn->prepare("delete from charity where id=?");
                $bn = $remove->execute(array($row["id"]));
                if (!isset($bn)) {
                    $bl = false;
                    throw new RuntimeException("failed to remove, check your charity data");
                }
                $bl = true;
            }
        }
        if ($bl) {
            $remove = $conn->prepare("delete from engine where uid=?");
            $bn = $remove->execute(array($uid));
            if (!isset($bn)) {
                throw new RuntimeException("failed to remove engine");
            }
        } else {
            throw new RuntimeException("failed to remove engine");
        }
        return "success";
    }
    
    function getAdminInfo(PDO $conn, string $username) {
        $data = $conn->query("select * from admin where name = '$username'");
        if ($data->rowCount() > 0) {
            $row = $data->fetch(PDO::FETCH_ASSOC);
            return new Admin($row["name"],$row["password"]);
        }
        return null;
    }
?>