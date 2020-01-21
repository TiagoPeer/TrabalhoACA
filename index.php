<?php

require_once "NasaHelper.php";
require_once "baseDados.php";

class Task
{
    private $mAutor, $dataInicio, $mDataFim, $mDuracao;

    public function calculaDuracao($pTaskID)
    {

        $bd = new baseDados();
        $bd->selectTaskBoardWhere($pTaskID);

        $row = mysqli_fetch_array($bd->selectTaskBoardWhere($pTaskID));
        $mDataInicio = $row['dataInicio'];

        $dataFim = date_create(date("Y/m/d"));
        $dataInicio = date_create($mDataInicio);
        $mDuracao = date_diff($dataInicio, $dataFim);

        return $mDuracao->format('%a');
    }
}

$errors = "";

// connect to database
$tarefa = new Task();
$dbEx = new baseDados();


// insert a quote if submit button is clicked
if (isset($_POST['submit'])) {

    if (empty($_POST['task'])) {
        $errors = "You must fill in the task";
    } else {
        $dbEx->insertBoard($_POST['task']);
    }
}

// delete task
if (isset($_GET['del_task'])) {
    //$id = $_GET['del_task'];
    $dbEx->insertBoardCompleted($_GET['del_task'], $tarefa->calculaDuracao($_GET['del_task']));
    $dbEx->removeBoard($_GET['del_task']);
}

// select all tasks if page is visited or refreshed
$tasks = $dbEx->selectTaskBoard();
$tasksCompleted = $dbEx->selectCompletedTaskBoard();

?>
<!DOCTYPE html>
<html>

<head>
    <title>ToDo List Application PHP and MySQL</title>
    <link rel="stylesheet" type="text/css" href="style.css">
</head>
<style>
    div {
        background-image: url(<?php NasaHelper::downloadAndDisplay(date("Y/m/d"))?>);
        background-repeat: no-repeat;
        background-size: cover;

    }
</style>

<body>

<div class="global">
    <div class="heading">
        <h2>Meu Organizador de Tarefas</h2>
    </div>

    <form method="post" action="1.php" class="input_form">
        <?php if (isset($errors)) { ?>
            <p><?php echo $errors; ?></p>
        <?php } ?>
        <input type="text" name="task" class="task_input">
        <button type="submit" name="submit" id="add_btn" class="add_btn">Add Task</button>
    </form>

    <div class="heading">
        <h2>Tarefas Incompletas</h2>
    </div>
    <table class="tabela">
        <thead>
        <tr>
            <th class="trNumber">Número</th>
            <th class="trNumber">Autor</th>
            <th class="trData">Data Inicio</th>
            <th>Tarefa</th>
            <th style="width: 60px;">Ações</th>
        </tr>
        </thead>

        <tbody>
        <?php $i = 1;
        while ($row = mysqli_fetch_array($tasks)) { ?>
            <tr>
                <td> <?php echo $i; ?> </td>
                <td class="task"> <?php echo $row['autor']; ?> </td>
                <td class="task"> <?php echo $row['dataInicio']; ?> </td>
                <td class="task"> <?php echo $row['task']; ?> </td>
                <td class="delete">
                    <a href="1.php?del_task=<?php echo $row['id'] ?>">x</a>
                </td>
            </tr>
            <?php $i++;
        } ?>
        </tbody>
    </table>
    <div class="heading">
        <h2>Tarefas Completas</h2>
    </div>
    <table class="tabela">
        <thead>
        <tr>
            <th class="trNumber">Número</th>
            <th class="trNumber">Autor</th>
            <th class="trData">Data Inicio</th>
            <th class="trData">Data Fim</th>
            <th class="trData">Duração</th>
            <th>Tarefa</th>

        </tr>
        </thead>

        <tbody>
        <?php $i = 1;
        while ($row = mysqli_fetch_array($tasksCompleted)) { ?>
            <tr>
                <td> <?php echo $i; ?> </td>
                <td class="task"> <?php echo $row['autor']; ?> </td>
                <td class="task"> <?php echo $row['dataInicio']; ?> </td>
                <td class="task"> <?php echo $row['dataFim']; ?> </td>
                <td class="task"> <?php echo $row['duracao']; ?> </td>
                <td class="task"> <?php echo $row['task']; ?> </td>

            </tr>
            <?php $i++;
        } ?>
        </tbody>
    </table>
</div>
</body>
</html>