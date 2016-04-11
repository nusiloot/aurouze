<?php

namespace AppBundle\Model;

use AppBundle\Document\Societe;

interface MouvementManagerInterface {
    public function getMouvementsBySociete(Societe $societe, $isFaturable, $isFacture);
    public function getMouvements($isFaturable, $isFacture);
}
