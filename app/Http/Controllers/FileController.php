<?php

namespace App\Http\Controllers;

use App\User;
use App\Http\Controllers\Controller;
use App\Http\Controllers\File;
use App\Http\Controllers\Storage;
use App\Http\Controllers\Exception;
//use Illuminate\Support\Facades\Storage;

class FileController extends Controller
{
    /**
     * Procesa un archivo.
     *
     * @return Response
     */
    public function index()
    {
        try
        {
            $deudores = array();
            
            $filename = 'deudores.txt';

            $file = storage_path('app\public\/'.$filename);

            foreach(file($file) as $line) 
            {
                $row = explode(',', $line);

                $linea = $this->armarLineas($row[0]);

                $registros[] = $this->armarRegistros($linea);
            }

            // Ordena los registros para facilitar el proceso
            sort($registros);

            $entidad = $this->armarEntidad($registros);

            $deudores = $this->armarDeudores($registros);

            echo "<pre>"; 
            print_r($entidad);
            print_r($deudores);
            echo "</pre>";

        }
        catch (\Exception $e)
        {   
            echo $e->getMessage();
        }
    }

    /*
     * Arma la estructura de registro de deudores
     * $array de los registros
     * retorna array
    */
    private function armarRegistros($array)
    {
        $deudores = array(
                            $array[0],
                            $array[1],
                            $array[2],
                            $array[3]?$array[3]:0                            
                            );
        return $deudores;
    }

    /*
     * Arma la linea a medida que avanza la lectura del archivo
     * $row: de cada linea del archivo leído
     * retorna array de 4 campos: Entidad, CUIT, Situación más desfavorable y Suma de Préstamos
    */
    private function armarLineas($row)
    {
        $linea = array();

        array_push($linea, substr($row, 0, 5)); // Entidad

        array_push($linea, substr($row, 13, 11)); // CUIT

        array_push($linea, substr($row, 27, 2)); // Situación más desfavorable 

        array_push($linea, substr($row, 29)); // Suma de Préstamos

        return $linea;
    }

    /*
     * Agrupa por CUIT y suma los préstamos a partir del array ordenado
     * $array: de registros
     * retorna array de Deudores: Nro de identificación, Situación más desfavorable y Suma de Préstamos
    */
    private function armarDeudores($array)
    {
        $result = array();
        
        foreach($array as $t) 
        {
            $repeat=false;
            
            for($i=0;$i<count($result);$i++)
            {
                if($result[$i]['NroId']==$t[1])
                {
                    $result[$i]['SumPrest']+=$t[3];

                    $result[$i]['SituDes'] = $t[2];

                    $repeat=true;
                    break;
                }
            }
            
            if($repeat==false)
                $result[] = array('NroId' => $t[1], 'SituDes' => $t[2], 'SumPrest' => $t[3]);
        }

        return $result;
    }

    /*
     * Agrupa por entidad y suma los préstamos a partir del array ordenado
     * $array: de registros
     * retorna array de Entidad: Nro de entidad y Suma de Préstamos
    */
    private function armarEntidad($array)
    {
        $result = array();

        foreach($array as $t) 
        {
            $repeat=false;
            
            for($i=0;$i<count($result);$i++)
            {
                if($result[$i]['Ent']==$t[0])
                {
                    $result[$i]['TotalSumPrest']+=$t[3];
                    $repeat=true;
                    break;
                }
            }
            
            if($repeat==false)
                $result[] = array('Ent' => $t[0], 'TotalSumPrest' => $t[3]);
        }

        return $result;
    }

}