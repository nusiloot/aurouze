<?php

namespace AppBundle\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class FacturesExtension extends AbstractExtension
{
    public function getFilters()
    {
        return [
            new TwigFilter('sommeRetards', [$this, 'sommeFacturesRetard'])
        ];
    }

    public function sommeFacturesRetard(array $facturesEnRetard)
    {
        $total = array_reduce($facturesEnRetard, function ($sum, $facture) {
            return $sum + $facture->getMontantTTC();
        }, 0);

        $paye = array_reduce($facturesEnRetard, function ($sum, $facture) {
            return $sum + $facture->getMontantPaye();
        }, 0);

        return number_format($total - $paye, 2, ',', ' ');
    }
}
