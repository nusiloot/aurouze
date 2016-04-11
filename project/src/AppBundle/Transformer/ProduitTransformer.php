<?php
namespace AppBundle\Transformer;

use AppBundle\Document\Produit;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class ProduitTransformer implements DataTransformerInterface
{
	protected $dm;

	public function __construct(DocumentManager $dm)
	{
		$this->dm = $dm;
	}
	
	public function transform($values)
	{
		$result = array();
		$produits = $this->dm->getRepository('AppBundle:Configuration')->findConfiguration()->getProduits()->toArray();
		$prods = array();
		foreach ($values as $value) {
			$prods[] = $value->getIdentifiant();
		}
		foreach ($produits as $index => $produit) {
			if (in_array($produit->getIdentifiant(), $prods)) {
				$result[] = $index;
			}
		}
		return $result;
	}
	public function reverseTransform($values)
	{
		$result = array();
		$produits = $this->dm->getRepository('AppBundle:Configuration')->findConfiguration()->getProduits()->toArray();
		foreach ($values as $value) {
			$result[] = $produits[$value];
		}
		return $result;
	}
}