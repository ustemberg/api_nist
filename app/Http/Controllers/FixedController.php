<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use App\Models\Fixed;
class FixedController extends Controller
{
    //Autenticación de la API
    public function __construct(){
        $this->middleware('jwt.auth');
    }
    public function bring_vulnerabilities(){
        //Traemos de la API del NIST las primeras 1000 vulnerabilidades y las retornamos
        $client = new Client();
        $response = $client->get('https://services.nvd.nist.gov/rest/json/cves/2.0?resultsPerPage=1000');
        $vulnerabilities = json_decode($response->getBody(), true);
        $vulnerabilities = $vulnerabilities['vulnerabilities'];
        return $vulnerabilities;
    }

    public function all(){
        $vulnerabilities = FixedController::bring_vulnerabilities();

        // Arreglo para almacenar algunos de los campos de cada vulnerabilidad.
        $filteredVulnerabilities = [];

        // Recorremos el arreglo de vulnerabilidades
        foreach ($vulnerabilities as $vulnerability) {
            // Accedemos a la propiedad 'cve' de cada vulnerabilidad
            $cve = $vulnerability['cve'];

            // Accedemos a la propiedad 'id', 'descriptions', 'baseSeverity', 'exploitabilityScore' y 'impactScore'
            // dentro de 'cve'->'metrics'
            $id = $cve['id'];
            $description = $cve['descriptions'];
            $baseSeverity = ''; $exploitabilityScore = ''; $impactScore = '';
            if(isset($cve['metrics']['cvssMetricV2'])){
                $baseSeverity = $cve['metrics']['cvssMetricV2'][0]['baseSeverity'];
                $exploitabilityScore = $cve['metrics']['cvssMetricV2'][0]['exploitabilityScore'];
                $impactScore = $cve['metrics']['cvssMetricV2'][0]['impactScore'];
            }
            // Construimos un nuevo objeto JSON con las propiedades deseadas
            $filteredVulnerability = [
                'id' => $id,
                'description' => $description,
                'severity' => $baseSeverity,
                'exploitability_score' => $exploitabilityScore,
                'impact_score' => $impactScore
            ];

            // Agregamos el objeto al arreglo de objetos filtrados
            $filteredVulnerabilities[] = $filteredVulnerability;
        }

        // Convertimos el arreglo de objetos filtrados a JSON y lo retornamos
        return response()->json($filteredVulnerabilities);

    }

    public function all_by_severity(){
        //Hacemos lo mismo que en la función all(), pero generando un objeto más global que posea un objeto
        //con las vulnerabilidades que sean de cada severidad posible
        $vulnerabilities = FixedController::bring_vulnerabilities();

        $high=[]; $medium =[]; $low = []; $summarized_vuln = [];
        foreach ($vulnerabilities as $vulnerability) {
            $cve = $vulnerability['cve'];
            $id = $cve['id'];
            $description = $cve['descriptions'];
            //Las vulnerabilidades que no tienen seteadas la parte de la severidad, ni las imprimimos acá
            //ya que no podemos clasificarla (decisión de implementación) en la función all() si las incluimos
            if(isset($cve['metrics']['cvssMetricV2'])) {
                $baseSeverity = $cve['metrics']['cvssMetricV2'][0]['baseSeverity'];
                $exploitabilityScore = $cve['metrics']['cvssMetricV2'][0]['exploitabilityScore'];
                $impactScore = $cve['metrics']['cvssMetricV2'][0]['impactScore'];

                $vuln = [
                    'id' => $id,
                    'description' => $description,
                    'exploitability_score' => $exploitabilityScore,
                    'impact_score' => $impactScore
                ];

                if ($baseSeverity == 'HIGH') $high[] = $vuln;
                else if ($baseSeverity == 'MEDIUM') $medium[] = $vuln;
                else $low[] = $vuln;
            }
            $summarized_vuln = [
                'HIGH' => $high,
                'MEDIUM' => $medium,
                'LOW' => $low
            ];
        }
        return response()->json($summarized_vuln);
    }

    public function fixed(){
        //Simplemente listamos de la tabla 'fixeds' las vulnerabilidades corregidas (por reglas de Laravel debe llamarse así,
        //aunque no exista la palabra)
        $fixed = Fixed::orderBy('id','ASC')->get();
        if(count($fixed) == 0) $fixed = ["msg" => "No hay vulnerabilidades corregidas"];
        return response()->json($fixed);
    }

    public function all_by_severity_notfixed(){
        //traemos todas las vulnerabilidades corregidas
        $fixed = Fixed::orderBy('id','ASC')->get();
        if(count($fixed) == 0) return FixedController::all_by_severity();
        //traemos todas las vulnerabilidades sumarizadas por severidad del punto 2
        $all_by_severity = FixedController::all_by_severity();
        $all_by_severity = json_decode($all_by_severity->getContent(), true);
        $summarized_vuln = [
            'HIGH' => $all_by_severity['HIGH'],
            'MEDIUM' => $all_by_severity['MEDIUM'],
            'LOW' => $all_by_severity['LOW']
        ];
        $filter_high = []; $filter_med = []; $filter_low = [];

        //recorremos vulnerabilidades x severidad y no la almacenamos si alguna de ellas está entre las corregidas
        foreach($summarized_vuln['HIGH'] as $vulnHigh){
            $isFixed = false;
            foreach($fixed as $fix){
                $id = $fix->cveId;
                if($vulnHigh['id'] == $id){
                    $isFixed = true;
                    break;
                }
            }
            if(!$isFixed) $filter_high[] = $vulnHigh;
        }
        foreach($summarized_vuln['MEDIUM'] as $vulnMed){
            $isFixed = false;
            foreach($fixed as $fix){
                $id = $fix->cveId;
                if($vulnMed['id'] == $id){
                    $isFixed = true;
                    break;
                }
            }
            if(!$isFixed) $filter_med[] = $vulnMed;
        }
        foreach($summarized_vuln['LOW'] as $vulnLow){
            $isFixed = false;
            foreach($fixed as $fix){
                $id = $fix->cveId;
                if($vulnLow['id'] == $id){
                    $isFixed = true;
                    break;
                }
            }
            if(!$isFixed) $filter_low[] = $vulnLow;
        }

        $filter_vuln = [
            'HIGH' => $filter_high,
            'MEDIUM' => $filter_med,
            'LOW' => $filter_low
        ];
        return response()->json($filter_vuln);
    }

    public function fix(){
        $ids = trim(request('cveIDs'));
        if($ids == '') return response()->json(['message' => 'No ha ingresado ninguna vulnerabilidad para marcar como corregida'], 200);
        $ids = explode(',',$ids);
        foreach($ids as $id){
            //insertamos cada id de vulnerabilidad corregida, separada por comas en la entrada
            $fixed = new Fixed();
            $fixed->cveId = $id;
            $fixed->save();
        }
        return response()->json(['message' => 'Vulnerabilidades guardadas como corregidas'], 200);
    }
}
