<?php


class baseDados
{
    private $mHost, $mUser, $mPass, $mDataBase;
    public $mDB;

    const DEBUG = true;

    const CREATE_SCHEMA = "CREATE SCHEMA IF NOT EXISTS `todo`;";

    const CREATE_TABLE_TASK = "
        CREATE TABLE IF NOT EXISTS `todo`.`tasks` (
        `id` INT NOT NULL AUTO_INCREMENT,
        `task` VARCHAR(45) NULL,
        `autor` VARCHAR(45) NULL,
        `dataInicio` DATE NULL,
        PRIMARY KEY (`id`));
    ";

    const CREATE_TABLE_COMPLETED_TASKS = "
        CREATE TABLE IF NOT EXISTS `todo`.`completedTask` (
        `id` INT NOT NULL AUTO_INCREMENT,
        `task` VARCHAR(45) NULL,
        `autor` VARCHAR(45) NULL,
       `dataInicio` DATE NULL,
       `dataFim` DATE NULL,
       `duracao` INTEGER NULL,
        PRIMARY KEY (`id`));
    ";

    public function __construct($pHost = "127.0.0.1:3306", $pUser = "aca1920", $pPass = "1234", $pDataBase = "todo")
    {
        $this->mDB = mysqli_connect(
            $this->mHost = $pHost,
            $this->mUser = $pUser,
            $this->mPass = $pPass,
            $this->mDataBase = $pDataBase
        );

        $e = mysqli_connect_errno(); //connect error code
        $eM = mysqli_connect_error();//connect error message
        if ($e !== 0) {
            //TODO: why???
            exit;
        }//if
        $this->install();
    }//__construct

    public function install()
    {
        $installProcedure = [
            self::CREATE_SCHEMA,
            self::CREATE_TABLE_COMPLETED_TASKS,
            self::CREATE_TABLE_TASK
        ];

        for ($idx = 0; $idx <= count($installProcedure); $idx++) {
            @$i = $installProcedure[$idx];
            $r = $this->queryExecutor($i, $e, $eM, $strFeedback);
            //echo $strFeedback;
        }
    }//install

    private function queryExecutor(
        $pQ, //the query
        &$pE, //error code
        &$pEMsg, //error msg
        &$pStrFeedback //description of everything that happened
    )
    {
        if ($this->mDB && !empty($pQ)) {
            $r = $this->mDB->query($pQ);
            $pE = $this->mDB->errno; //error code
            $pEMsg = $this->mDB->error; //error message
            $strResult = gettype($r) . " ";
            if (is_bool($r)) {
                $strResult .= $r ? "true" : "false";
            }
            $strResult .= PHP_EOL;

            $pStrFeedback = sprintf(
                "query= %s\nerror code=%d (%s)\n" .
                "result= %s",
                $pQ,
                $pE,
                $pEMsg,
                $strResult
            );

            return $r;
        }//if
        else {
            $pEMsg = "No database pointer!";
            return false;
        }
    }//queryExecutor

    public function insertBoard($pTask)
    {
        $idWhereBoardAlreadyExists = $this->idForBoard($pTask);
        if ($idWhereBoardAlreadyExists === false) {

            //prepared statements?
            $pData = date("Y/m/d");
            $q = "INSERT INTO tasks (task,autor,dataInicio) VALUES ('$pTask','Tiago','$pData');";
            $r = $this->queryExecutor($q, $e, $eM, $strFeedback);

            if (is_bool($r) && ($r === true) && ($e === 0)) {
                $idWhereInserted = $this->mDB->insert_id;
                echo "1";
                return $idWhereInserted;

            }//if

        }//if
        return false;
    }//insertBoard

    public function insertBoardCompleted($pTask, $pDuracao)
    {
        $idWhereBoardAlreadyExists = $this->idForBoard($pTask);
        if ($idWhereBoardAlreadyExists === false) {
            $row = mysqli_fetch_array($this->selectTaskBoardWhere($pTask));
            $mDataInicio = $row['dataInicio'];
            $mTask = $row['task'];
            $pDataFim = date("Y/m/d");
            $q = "INSERT INTO completedTask (task,autor,dataInicio,dataFim,duracao) VALUES ('$mTask','Tiago','$mDataInicio','$pDataFim','$pDuracao');";
            $r = $this->queryExecutor($q, $e, $eM, $strFeedback);

            if (is_bool($r) && ($r === true) && ($e === 0)) {
                $idWhereInserted = $this->mDB->insert_id;
                return $idWhereInserted;
            }//if
        }//if
        return false;
    }//insertBoard

    public function removeBoard($pId)
    {
        $q = "DELETE FROM tasks WHERE id=" . $pId;
        $r = $this->queryExecutor($q, $e, $eM, $strFeedback);

        return false;
    }//insertBoard

    public function idForBoard($pTask)
    {
        $q = "SELECT id FROM tasks WHERE task='$pTask' limit 1;";
        $r = $this->queryExecutor($q, $e, $eM, $strF);

        if ($e === 0 && ($r instanceof mysqli_result)) {
            $aAllResults = $r->fetch_all(MYSQLI_ASSOC);
            $bOK = is_array($aAllResults) && count($aAllResults) === 1;
            if ($bOK) {
                $id = $aAllResults[0]['id'];
                return $id;
            }
        }
        return false;
    }//idForBoard

    public function selectTaskBoard()
    {
        return mysqli_query($this->mDB, "SELECT * FROM tasks");
    }//selectAllBoards

    public function selectTaskBoardForAutor($pAutor)
    {
        return mysqli_query($this->mDB, "SELECT * FROM tasks WHERE autor='$pAutor'");
    }//selectAllBoards

    public function selectTaskBoardWhere($pTask)
    {
        return mysqli_query($this->mDB, "SELECT dataInicio,task FROM tasks WHERE id='$pTask'");
    }//selectTaskBoardWhere

    public function selectCompletedTaskBoardWhere($pTask)
    {
        return mysqli_query($this->mDB, "SELECT dataInicio,dataFim FROM completedTask WHERE id='$pTask'");
    }//selectCompletedTaskBoardWhere

    /*public function getDates($pTask)
    {

        $row = mysqli_fetch_array($this->selectTaskBoardWhere($pTask));
        $mDataInicio = $row['dataInicio'];

        echo $mDataInicio;
        return $mDataInicio;
    }//getDates*/

    public function selectCompletedTaskBoard()
    {
        return mysqli_query($this->mDB, "SELECT * FROM completedTask");
    }//selectCompletedTaskBoard
}

