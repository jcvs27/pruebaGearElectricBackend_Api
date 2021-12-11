<?php

// Encabezados
date_default_timezone_set('America/Bogota');
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: x-xsrf-token, X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method, X-Auth-Token");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
header("Allow: GET, POST, OPTIONS, PUT, DELETE");
header('content-type: application/json; charset=utf-8');
$method = $_SERVER['REQUEST_METHOD'];

// Funciones para las respuestas
function Info($msj)
{
    echo '{"status":"500", "msj":"' . $msj . '"}';
    exit;
}

function success($msj)
{
    echo '{"status":"200", "msj":"' . $msj . '"}';
    exit;
}

/* Modelo para la conexión con la BD*/
function conexion()
{
    $server = "localhost";
    $db     = "eventos";
    $user   = "root";
    $clave  = "root";
    $sgbd   = "mysql:host=" . $server . ";dbname=" . $db;
    $con = new PDO($sgbd, $user, $clave);
    $con->exec("SET CHARACTER SET utf8");
    return $con;
}

$cnn = conexion();

// Validaciones del metodo 
if ($method == "OPTIONS") {
    die();
} else if ($method == 'POST') {
    // registro de las asistencias
    $data = json_decode(file_get_contents("php://input"));
    $id = $data->inputIden;
    $Tipo = $data->SelecTipo;
    $nombre = $data->inputNombres;
    $apellido = $data->inputApellidos;
    $telefono = $data->inputTelefono;
    $correo = $data->inputCorreo;
    $estado = '0';
    // Se consulta que del asistente no este ya registrado
    $queryCon = "SELECT COUNT(1) FROM asistentes WHERE numerodocumento= :id";
    $queryCon = $cnn->prepare($queryCon);
    $queryCon->bindParam(':id', $id, PDO::PARAM_STR);
    $queryCon->execute();
    $dato = $queryCon->fetch();
    // Si existe no se deja registrar
    if($dato[0] > 0){
        Info("Ya existe un registro actual de ese asistente. ");
        exit;
    }

    $query = "INSERT INTO asistentes (numerodocumento, nombres, apellidos, tipodocumento, telefonomovil, correo, estado ) 
    VALUES(:num, :pn, :apel, :tip, :tel, :cor, :est )";
    $QueryIns = $cnn->prepare($query);
    $QueryIns->bindParam(':num', $id, PDO::PARAM_STR);
    $QueryIns->bindParam(':pn', $nombre, PDO::PARAM_STR);
    $QueryIns->bindParam(':apel', $apellido, PDO::PARAM_STR);
    $QueryIns->bindParam(':tip', $Tipo, PDO::PARAM_STR);
    $QueryIns->bindParam(':tel', $telefono, PDO::PARAM_INT);
    $QueryIns->bindParam(':cor', $correo, PDO::PARAM_STR);
    $QueryIns->bindParam(':est', $estado, PDO::PARAM_STR);
    $res = $QueryIns->execute();
    if ($res) {
        success("Registro existoso");
    } else {
        Info("Fallo el registro");
    }
} else if ($method == 'GET') {
    $id = isset($_GET['id'])?$_GET['id']: '';
    $tipo = isset($_GET['tipo'])?$_GET['tipo']:'0';
    if ($tipo == '1') {
        // consulta individual de un asistente
        $QueryCon = $cnn->prepare("SELECT id, nombres, apellidos, tipodocumento, numerodocumento, telefonomovil, correo
        FROM asistentes WHERE id = :id");
        $QueryCon->bindParam(':id', $id, PDO::PARAM_INT);
        $QueryCon->execute();
        $DataInfo = $QueryCon->fetchAll();
        for ($i = 0; $i < count($DataInfo); $i++) {
            # code...
            $datos[$i]['id'] =  $DataInfo[$i]['id'];
            $datos[$i]["nombres"] = $DataInfo[$i]['nombres'];
            $datos[$i]["apellidos"] = $DataInfo[$i]['apellidos'];
            $datos[$i]["tipodocumento"] = $DataInfo[$i]['tipodocumento'];
            $datos[$i]["numerodocumento"] = $DataInfo[$i]['numerodocumento'];
            $datos[$i]["telefonomovil"] = $DataInfo[$i]['telefonomovil'];
            $datos[$i]["correo"] = $DataInfo[$i]['correo'];
        }
        $res = array("datos" => $datos);
        echo json_encode($res);

    } else {
        // consulta de la información de los asistentes - todos
        $QueryCon = $cnn->prepare("SELECT id, nombres, apellidos, tipodocumento, numerodocumento, telefonomovil, correo, estado
        FROM asistentes ORDER BY 1 DESC");
        $QueryCon->execute();
        $DataInfo = $QueryCon->fetchAll();
        for ($i = 0; $i < count($DataInfo); $i++) {
            # code...
            if ($DataInfo[$i]['estado'] === '0') {
                $estado = 'Activo';
            } else {
                $estado = 'Inactivo';
            }
            $datos[$i]['id'] =  $DataInfo[$i]['id'];
            $datos[$i]["nombres"] = $DataInfo[$i]['nombres'];
            $datos[$i]["apellidos"] = $DataInfo[$i]['apellidos'];
            $datos[$i]["tipodocumento"] = $DataInfo[$i]['tipodocumento'];
            $datos[$i]["numerodocumento"] = $DataInfo[$i]['numerodocumento'];
            $datos[$i]["telefonomovil"] = $DataInfo[$i]['telefonomovil'];
            $datos[$i]["correo"] = $DataInfo[$i]['correo'];
            $datos[$i]["estado"] = $DataInfo[$i]['estado'];
            $datos[$i]["estadoTexto"] = $estado;
        }
        $res = array("datos" => $datos);
        echo json_encode($res);
    }
} else if ($method == 'PUT') {

    $data = json_decode(file_get_contents("php://input"));
    $id = $data->id;
    $estado  = $data->estado;
    if ($estado === '0') {
        $estado = '1';
    } else {
        $estado = '0';
    }
    $tipo = $data->tipo;
    // Para actualizar el estado de un asistente
    if ($tipo === '1') {
        $queryUP = "UPDATE asistentes SET estado = :est WHERE id = :id";
        $queryUP = $cnn->prepare($queryUP);
        $queryUP->bindParam(':id', $id, PDO::PARAM_INT);
        $queryUP->bindParam(':est', $estado, PDO::PARAM_STR);
        $resUp = $queryUP->execute();
    } else {
        // Para actualizar toda la información del asistente
        $nombre = $data->datos->inputNombres;
        $apellido = $data->datos->inputApellidos;
        $telefono = $data->datos->inputTelefono;
        $correo = $data->datos->inputCorreo;
        $TipoDoc = $data->datos->SelecTipo;

        $queryUP = "UPDATE asistentes SET nombres = :nomb, apellidos= :apel, telefonomovil = :tel, correo = :cor, tipodocumento= :tip WHERE id = :id";
        $queryUP = $cnn->prepare($queryUP);
        $queryUP->bindParam(':id', $id, PDO::PARAM_INT);
        $queryUP->bindParam(':nomb', $nombre, PDO::PARAM_STR);
        $queryUP->bindParam(':apel', $apellido, PDO::PARAM_STR);
        $queryUP->bindParam(':tel', $telefono, PDO::PARAM_INT);
        $queryUP->bindParam(':cor', $correo, PDO::PARAM_STR);
        $queryUP->bindParam(':tip', $TipoDoc, PDO::PARAM_STR);
        $resUp = $queryUP->execute();
    }


    if ($resUp) {
        success("Registro Actualizado. ");
    } else {
        Info("No se logro el actualizar el registro.");
    }
} else if ($method == 'DELETE') {

    // Para eliminar un registro
    $data = json_decode(file_get_contents("php://input"));
    $id = $data;
    $queryDel = "DELETE FROM asistentes WHERE id = :id";
    $queryDel = $cnn->prepare($queryDel);
    $queryDel->bindParam(':id', $id, PDO::PARAM_INT);
    $resDel = $queryDel->execute();
    if ($resDel) {
        success("Registro Eliminado. ");
    } else {
        Info("No se logro Elminar el registro.");
    }
}

