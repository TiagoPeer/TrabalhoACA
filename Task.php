<?php

require_once "baseDados.php";
class Task
{
    private $mAutor, $dataInicio, $mDataFim, $mDuracao;

    public function calculaDuracao($pTaskID)
    {

        $bd = new baseDados();
        $bd->selecionarTarefaOnde($pTaskID);

        $row = mysqli_fetch_array($bd->selecionarTarefaOnde($pTaskID));
        $mDataInicio = $row['dataInicio'];

        $dataFim = date_create(date("Y/m/d"));
        $dataInicio = date_create($mDataInicio);
        $mDuracao = date_diff($dataInicio, $dataFim);

        return $mDuracao->format('%a');
    }
}