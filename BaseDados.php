<?php

class BaseDados
{
    private $mHost, $mUser, $mPass, $mDataBase;
    public $mDB;

    const CREATE_SCHEMA = "CREATE SCHEMA IF NOT EXISTS `todo`;";

    const CRIAR_TABELA_TAREFA = "
        CREATE TABLE IF NOT EXISTS `todo`.`tasks` (
        `id` INT NOT NULL AUTO_INCREMENT,
        `descricao` VARCHAR(45) NULL,
        `autor` VARCHAR(45) NULL,
        `dataInicio` DATE NULL,
        PRIMARY KEY (`id`));
    ";

    const CRIAR_TABELA_TAREFA_COMPLETA = "
        CREATE TABLE IF NOT EXISTS `todo`.`completedTask` (
        `id` INT NOT NULL AUTO_INCREMENT,
        `descricao` VARCHAR(45) NULL,
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
            exit;
        }//if
        $this->install();
    }//__construct

    public function install()
    {
        $installProcedure = [
            self::CREATE_SCHEMA,
            self::CRIAR_TABELA_TAREFA_COMPLETA,
            self::CRIAR_TABELA_TAREFA
        ];

        for ($idx = 0; $idx <= count($installProcedure); $idx++) {
            @$i = $installProcedure[$idx];
            $r = $this->queryExecutor($i, $e, $eM, $strFeedback);
        }//for
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
            }//if
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
        }//else
    }//queryExecutor

    public function inserirTarefa($pDescricaoTarefa)
    {
        $idOndeATarefaExiste = $this->idParaTarefa($pDescricaoTarefa);
        if ($idOndeATarefaExiste === false) {

            $data = date("Y/m/d");
            $q = "INSERT INTO tasks (descricao,autor,dataInicio) VALUES ('$pDescricaoTarefa','Tiago','$data');";
            $r = $this->queryExecutor($q, $e, $eM, $strFeedback);

            if (is_bool($r) && ($r === true) && ($e === 0)) {
                $idOndeATarefaFoiInserida = $this->mDB->insert_id;
                return $idOndeATarefaFoiInserida;
            }//if
        }//if
        return false;
    }//inserirTarefa

    public function inserirTarefaCompleta($pIdTarefa, $pDuracaoTarefa)
    {
        $idOndeATarefaExiste = $this->idParaTarefa($pIdTarefa);
        if ($idOndeATarefaExiste === false) {

            $arrayCamposTarefa = mysqli_fetch_array($this->selecionarTarefaOnde($pIdTarefa));
            $dataInicio = $arrayCamposTarefa['dataInicio'];
            $descricaoTarefa = $arrayCamposTarefa['descricao'];
            $dataFim = date("Y/m/d");
            $q = "INSERT INTO completedTask (descricao,autor,dataInicio,dataFim,duracao) VALUES ('$descricaoTarefa','Tiago','$dataInicio','$dataFim','$pDuracaoTarefa');";
            $r = $this->queryExecutor($q, $e, $eM, $strFeedback);

            if (is_bool($r) && ($r === true) && ($e === 0)) {
                $idOndeATarefaFoiInserida = $this->mDB->insert_id;
                return $idOndeATarefaFoiInserida;
            }//if
        }//if
        return false;
    }//inserirTarefaCompleta

    public function removerTarefa($pIdTarefa)
    {
        $q = "DELETE FROM tasks WHERE id=" . $pIdTarefa;
        $r = $this->queryExecutor($q, $e, $eM, $strFeedback);
        return false;
    }//removerTarefa

    public function idParaTarefa($pDescricaoTarefa)
    {
        $q = "SELECT id FROM tasks WHERE descricao='$pDescricaoTarefa' limit 1;";
        $r = $this->queryExecutor($q, $e, $eM, $strF);

        if ($e === 0 && ($r instanceof mysqli_result)) {
            $aAllResults = $r->fetch_all(MYSQLI_ASSOC);
            $bOK = is_array($aAllResults) && count($aAllResults) === 1;
            if ($bOK) {
                $id = $aAllResults[0]['id'];
                return $id;
            }//if
        }//if
        return false;
    }//idParaTarefa

    public function selecionarTodasAsTarefas()
    {
        return mysqli_query($this->mDB, "SELECT * FROM tasks");
    }//selecionarTarefa

    public function selecionarTarefaPorAutor($pAutorTarefa)
    {
        return mysqli_query($this->mDB, "SELECT * FROM tasks WHERE autor='$pAutorTarefa'");
    }//selecionarTarefaPorAutor

    public function selecionarTarefaOnde($pIdTarefa)
    {
        return mysqli_query($this->mDB, "SELECT dataInicio,descricao FROM tasks WHERE id='$pIdTarefa'");
    }//selecionarTarefaOnde

    public function selecionarTarefasCompletasOnde($pIdTarefa)
    {
        return mysqli_query($this->mDB, "SELECT dataInicio,dataFim FROM completedTask WHERE id='$pIdTarefa'");
    }//selecionarTarefasCompletasOnde

    public function selecionarTodasAsTarefasCompletas()
    {
        return mysqli_query($this->mDB, "SELECT * FROM completedTask");
    }//selecionarTarefasCompletas
}

