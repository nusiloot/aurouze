<?php
namespace AppBundle\Transformer;

use AppBundle\Document\Prestation;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class PrestationTransformer implements DataTransformerInterface
{
	protected $dm;

	public function __construct(DocumentManager $dm)
	{
		$this->dm = $dm;
	}
	
	public function transform($values)
	{
		$result = array();
		$prestations = $this->dm->getRepository('AppBundle:Configuration')->findConfiguration()->getPrestations()->toArray();
		$prestas = array();
		foreach ($values as $value) {
			$prestas[] = $value->getIdentifiant();
		}
		foreach ($prestations as $index => $prestation) {
			if (in_array($prestation->getIdentifiant(), $prestas)) {
				$result[] = $index;
			}
		}
		return $result;
	}
	public function reverseTransform($values)
	{
		$result = array();
		$prestations = $this->dm->getRepository('AppBundle:Configuration')->findConfiguration()->getPrestations()->toArray();
		foreach ($values as $value) {
			$result[] = $prestations[$value];
		}
		return $result;
	}
}