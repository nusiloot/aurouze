<?php

namespace AppBundle\Model;

use AppBundle\Document\Etablissement;

interface MouvementManagerInterface {
    public function getMouvementsByEtablissement(Etablissement $etablissement, $isFaturable, $isFacture);
    public function getMouvements($isFaturable, $isFacture);
}
