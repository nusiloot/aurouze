<?php
namespace AppBundle\Tool;

class RechercheTool
{
	public static $correspondancesLettre = array(
		'a' => '[aàâä]',
		'o' => '[oô]',
		'e' => '[eéèëê]',
		'i' => '[iïî]',
		'c' => '[cç]',
		'u' => '[uùûü]',
		'y' => '[yÿ]',
		'A' => '[AÀÂÄ]',
		'O' => '[OÔ]',
		'E' => '[EÉÈËÊ]',
		'I' => '[IÏÎ]',
		'Y' => '[YŸ]',
		'C' => '[CÇ]',
		'U' => '[UÙÛÜ]'
	);
	
	public static function getCorrespondances($term)
	{
		$lettres = array_keys(self::$correspondancesLettre);
		$correspondances = array_values(self::$correspondancesLettre);
		return str_replace($lettres, $correspondances, $term);
	}
}