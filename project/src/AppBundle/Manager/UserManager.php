<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of EtablissementManager
 *
 * @author mathurin
 */

namespace AppBundle\Manager;

use Doctrine\ODM\MongoDB\DocumentManager as DocumentManager;

class UserManager {

    protected $dm;

    const RED = 'red';
    const ORANGE_DARK = 'orange-dark';
    const ORANGE = 'orange';
    const BLUE_DARK = 'blue-dark';
    const CYAN = 'cyan';
    const VIOLET = 'violet';
    const PURPLE = 'purple';
    const GREEN_DARK = 'green-dark';
    const GREEN = 'green';
    const WHITE = 'white';
    
    
    public static $couleur_for_label = array(
        self::RED => array('background-color' => 'darkred','color' => '#fff'),
        self::ORANGE_DARK => array('background-color' => '#d02c1d','color' => '#fff'),
        self::ORANGE => array('background-color' => 'orange','color' => '#fff'),
        self::BLUE_DARK => array('background-color' => '#2d6272','color' => '#fff'),
        self::CYAN => array('background-color' => 'cyan','color' => '#000'),
        self::VIOLET => array('background-color' => 'violet','color' => '#000'),
        self::PURPLE => array('background-color' => '#553466','color' => '#fff'),
        self::GREEN_DARK => array('background-color' => 'darkgreen','color' => '#fff'),
        self::GREEN => array('background-color' => '#048d3f','color' => '#fff'),
        self::WHITE => array('background-color' => '#eee','color' => '#000'));
    
    
    function __construct(DocumentManager $dm) {
        $this->dm = $dm;
    }

    

    public function getRepository() {

        return $this->dm->getRepository('AppBundle:User');
    }


}
