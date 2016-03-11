<?php

namespace AppBundle\Tool;

use Symfony\Component\Config\Definition\Exception\Exception;

class OSMAdresses {

    private $document;
    private $lat;
    private $lon;
    private $url;
    private $format;

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
        $adresseTrim = preg_replace("/\//", ",", $adresseTrim);
        if (!preg_match('/^http.*\./', $this->url)) {
            return false;
        }
        $fullAdresse = $adresseTrim . " " . $document->getCommune() . " " . $document->getCodePostal().', FRANCE';
        $url = $this->url . '?q=' . urlencode($fullAdresse);

        $file = file_get_contents($url);

        $result = json_decode($file);

        if (!count($result)) {
            return "Adresse non trouvée : $fullAdresse";
        }
        if(!count($result->response->docs) || !isset($result->response->docs[0])){
            return "Adresse non trouvée : $fullAdresse" ;
        }
        $lat = $result->response->docs[0]->lat;
        $lon = $result->response->docs[0]->lng;
        $this->document->getCoordonnees()->setLat($lat);
        $this->document->getCoordonnees()->setLon($lon);
        return "Nouvelle coordonnées : $lat,$lon pour $fullAdresse";
    }


}
