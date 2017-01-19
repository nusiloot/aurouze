<?php

namespace AppBundle\Tool;

use Symfony\Component\Config\Definition\Exception\Exception;

class OSMAdresses {

    private $document;
    private $lat;
    private $lon;
    private $url;
    private $format;
    
    CONST RAYON_TERRE = 6378137;
    CONST DISTANCE_CHECK = 20000;

    public function __construct($url, $format = 'json') {
        $this->url = $url;
        $this->format = $format;
    }

    public function calculCoordonnees($document) {
        if(!$document){
            return "Aucun document n'a été trouvé";
        }
        $this->document = $document;
        $adresseTrim = trim(preg_replace("/B[\.]*P[\.]* [0-9]+/", "", $document->getAdresse()));
        $adresseTrim = str_replace(",", " ", $adresseTrim);
        $adresseTrim = str_replace("/", " ", $adresseTrim);
        $adresseTrim = preg_replace("/commune/i", "", $adresseTrim);
        if (!preg_match('/^http.*\./', $this->url)) {
            return false;
        }
        $fullAdresse = $adresseTrim.", ".$document->getCodePostal(). " ". $document->getCommune().', FRANCE';
		$minAdresse = $document->getCodePostal(). " ". $document->getCommune().', FRANCE';
       

        $result = $this->getCoordonnees($fullAdresse);
        $resultMin = $this->getCoordonnees($minAdresse);
        if (!$result) {
            return "Adresse non trouvée : $fullAdresse";
        }
        $lat = $result->response->docs[0]->lat;
        $lon = $result->response->docs[0]->lng;
        if ($resultMin) {
        	$distance = round($this->getDistances($lat, $lon, $resultMin->response->docs[0]->lat, $resultMin->response->docs[0]->lng));
        	if ($distance > self::DISTANCE_CHECK) {
        		$lat = $resultMin->response->docs[0]->lat;
        		$lon = $resultMin->response->docs[0]->lng;
        	}
        }
        $this->document->getCoordonnees()->setLat($lat);
        $this->document->getCoordonnees()->setLon($lon);
        return "Nouvelle coordonnées : $lat,$lon pour $fullAdresse";
    }
    
    public function getDistances($lat1, $lon1, $lat2, $lon2) 
    {
		$rlo1 = deg2rad($lon1);
	  	$rla1 = deg2rad($lat1);
	  	$rlo2 = deg2rad($lon2);
	  	$rla2 = deg2rad($lat2);
	 	$dlo = ($rlo2 - $rlo1) / 2;
	 	$dla = ($rla2 - $rla1) / 2;
    	$a = (sin($dla) * sin($dla)) + cos($rla1) * cos($rla2) * (sin($dlo) * sin($dlo));
    	$d = 2 * atan2(sqrt($a), sqrt(1 - $a));
    	return (self::RAYON_TERRE * $d);
    }
    
    public function getCoordonnees($adresse)
    {
    	$url = $this->url . '?q=' . urlencode($adresse);
    	$file = file_get_contents($url);
    	$result = json_decode($file);
    	if (!count($result)) {
    		return null;
    	}
    	if(!count($result->response->docs) || !isset($result->response->docs[0])){
    		return null;
    	}
    	return $result;
    }


}
