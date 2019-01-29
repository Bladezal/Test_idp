<?php
    class Mso{
        private $name;
        private $country;
        private $authNStatus;
        private $authZStatus;
        private $delayAuthN;
        private $delayAuthZ;

        public function __construct(){
            $this->name = '';
            $this->country = '';
            $this->authNStatus = '';
            $this->authZStatus = '';
            $this->delayAuthN = 0;
            $this->delayAuthZ = 0;
        }

        public function initMSO($name, $country, $authNStatus, $authZStatus, $delayAuthN, $delayAuthZ){
            $this->name = $name;
            $this->country = $country;
            $this->authNStatus = $authNStatus;
            $this->authZStatus = $authZStatus;
            $this->delayAuthN = $delayAuthN;
            $this->delayAuthZ = $delayAuthZ;
        }

        public function setName($name){
            $this->name = $name;
        }

        public function getName(){
            return $this->name;
        }

        public function setCountry($country){
            $this->country = $country;
        }

        public function getCountry(){
            return $this->country;
        }

        public function setAuthNStatus($authNStatus){
            $this->authNStatus = $authNStatus;
        }

        public function getAuthNStatus(){
            return $this->authNStatus;
        }

        public function setAuthZStatus($authZStatus){
            $this->authZStatus = $authZStatus;
        }

        public function getAuthZStatus(){
            return $this->authZStatus;
        }

        public function setDelayAuthN($delayAuthN){
            $this->delayAuthN = $delayAuthN;
        }

        public function getDelayAuthN(){
            return $this->delayAuthN;
        }

        public function setDelayAuthZ($delayAuthZ){
            $this->delayAuthZ = $delayAuthZ;
        }

        public function getDelayAuthZ(){
            return $this->delayAuthZ;
        }

    }

    class LastAction{
        private $name;
        private $country;
        private $actionStatus;
        private $date;

        public function __construct(){
            $this->name = '';
            $this->country = '';
            $this->actionStatus = '';
            $this->date = '';
        }

        public function initLastAction($name, $country, $actionStatus, $date){
            $this->name = $name;
            $this->country = $country;
            $this->actionStatus = $actionStatus;
            $this->date = $date;
        }

        public function setName($name){
            $this->name = $name;
        }

        public function getName(){
            return $this->name;
        }

        public function setCountry($country){
            $this->country = $country;
        }

        public function getCountry(){
            return $this->country;
        }

        public function setActionStatus($actionStatus){
            $this->actionStatus = $actionStatus;
        }

        public function getActionStatus(){
            return $this->actionStatus;
        }

        public function setDate($date){
            $this->date = $date;
        }

        public function getDate(){
            return $this->date;
        }

    }

    class ConnDB {
        private $host;
        private $user;
        private $pass;
        private $dbName;
        private $conn;

        public function __construct($host, $user, $pass, $dbName){
            $this->host = $host;
            $this->user = $user;
            $this->pass = $pass;
            $this->dbName = $dbName;

            $dns = "mysql:host=".$this->host.";dbname=".$this->dbName;
            $options = [
                PDO::ATTR_EMULATE_PREPARES   => false, // turn off emulation mode for "real" prepared statements
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, //turn on errors in the form of exceptions
                ];
            try{
                $this->conn = new PDO($dns, $this->user, $this->pass, $options);
                // return $this->conn;
            }catch (\PDOException $e){
                throw new \PDOException($e->getMessage(), (int)$e->getCode());
            }
        }

        public function insMSO($mso){
            $sql = 'INSERT INTO msolist (name, country, authNStatus , authZStatus, delayAuthN, delayAuthZ, date) VALUES ';
            $insertQuery = array();
            $insertData = array();
            foreach ($mso as $data) {
                $insertQuery[] = '(?, ?, ?, ?, ?, ?, NOW())';
                $insertData[] = $data->getName();
                $insertData[] = $data->getCountry();
                $insertData[] = $data->getAuthNStatus();
                $insertData[] = $data->getAuthZStatus();
                $insertData[] = $data->getDelayAuthN();
                $insertData[] = $data->getDelayAuthZ();
            }
            $sql .= implode(', ', $insertQuery);
            $stmt = $this->conn->prepare($sql);
            $result = $stmt->execute($insertData);
        }

        public function insLastAction($lastAction){
            $sql = 'INSERT INTO lastactions (name, country, actionStatus, date) VALUES ';
            $insertQuery = array();
            $insertData = array();
            foreach ($lastAction as $action) {
                $insertQuery[] = '(?, ?, ?, ?)';
                $insertData[] = $action->getName();
                $insertData[] = $action->getCountry();
                $insertData[] = $action->getActionStatus();
                $insertData[] = $action->getDate();
            }
            $sql .= implode(', ', $insertQuery);
            $stmt = $this->conn->prepare($sql);
            $result = $stmt->execute($insertData);
        }

        public function getLastActions($dates){
            $stmt = $this->conn->prepare("SELECT name, country, actionStatus, date FROM lastactions WHERE date BETWEEN ? AND ?");
            $stmt->execute([$dates->fecdesde, $dates->fechasta]);
            $result = $stmt->fetchAll(PDO::FETCH_CLASS | PDO::FETCH_PROPS_LATE, 'LastAction');
            return $result;
        }

        public function getMSOlist($dates){
            $stmt = $this->conn->prepare("SELECT name, country, authNStatus, delayAuthN, authZStatus, delayAuthZ FROM msolist WHERE date BETWEEN ? AND ?");
            $stmt->execute([$dates->fecdesde, $dates->fechasta]);
            $result = $stmt->fetchAll(PDO::FETCH_CLASS | PDO::FETCH_PROPS_LATE, 'Mso');
            return $result;
        }
    }

    function MSOList($conn, $msoList){
        $msoObject = new Mso();
        $msoArray = [];
        foreach ($msoList as $mso) {
            foreach ($mso->country as $country => $data) {
                if (is_object($data)) {
                    if (($data->authN->status !== "OK") || ($data->authN->responseTimeAvg > 2000)) {
                        $msoObject->initMSO($mso->name, $country, $data->authN->status, $data->authZ->status, $data->authN->responseTimeAvg, $data->authZ->responseTimeAvg);
                        $msoArray[] = clone $msoObject;
                    } else {
                        if (($data->authZ->status !== "OK") || ($data->authZ->responseTimeAvg > 2000)) {
                            $msoObject->initMSO($mso->name, $country, $data->authN->status, $data->authZ->status, $data->authN->responseTimeAvg, $data->authZ->responseTimeAvg);
                            $msoArray[] = clone $msoObject;
                        }
                    }
                }
            }
        }
        try {
            $conn->insMSO($msoArray);
        } catch (\PDOException $e) {
            throw new \PDOException($e->getMessage(), (int)$e->getCode());
        }
    }

    function lastActions($conn, $lastActionsList){
        $actionObject = new LastAction();
        $actionArray = [];
        foreach ($lastActionsList as $action) {
            $actionObject->initLastAction($action->name, $action->country, $action->actions->CloudPassHook->status, date("Y/m/d H:i:s", strtotime($action->date))); //date_format($date, 'd/m/Y H:i:s')
            $actionArray[] = clone $actionObject; 
        }
        try {
            $conn->insLastAction($actionArray);
        } catch (\PDOException $e) {
            throw new \PDOException($e->getMessage(), (int)$e->getCode());
        }
        
    }

    function genReport($conn, $data){
        $msoList = $conn->getMSOlist($data);
        $lastActionsList = $conn->getLastActions($data);
    }

    $connection = new ConnDB("localhost", "root", "123", "idp_monitoring");
    $option = $_POST["option"];
    $objectList = json_decode($_POST["objectList"]);

    switch ($option) {
        case 'list':
            MSOList($connection, $objectList);
            break;
        
        case 'last':
            lastActions($connection, $objectList);
            break;
        case 'report':
            genReport($connection, $objectList);
            break;
    }

?>