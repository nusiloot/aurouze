<?php

namespace AppBundle\Model;

interface FacturableInterface
{
    public function getDocumentType();
    public function getLignes();
    public function getNumero();
}
