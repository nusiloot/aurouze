<?php
namespace AppBundle\Transformer;

use AppBundle\Document\Provenance;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class ProvenanceTransformer implements DataTransformerInterface
{
	protected $dm;

	public function __construct(DocumentManager $dm)
	{
		$this->dm = $dm;
	}
	
	public function transform($value)
	{
		$result = null;
		if ($value) {
			$provenances = $this->dm->getRepository('AppBundle:Configuration')->findConfiguration()->getProvenances()->toArray();
			foreach ($provenances as $index => $provenance) {
				if ($provenance->getIdentifiant() == $value->getIdentifiant()) {
					$result = $index;
				}
			}
		}
		return $result;
	}
	public function reverseTransform($value)
	{
		$provenances = $this->dm->getRepository('AppBundle:Configuration')->findConfiguration()->getProvenances()->toArray();
		
		return (isset($provenances[$value]))? $provenances[$value] : null;
	}
}