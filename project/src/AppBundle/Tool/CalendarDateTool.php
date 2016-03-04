<?php
namespace AppBundle\Tool;

class CalendarDateTool
{
	protected $date;
	public static $libellesMois = array(
			'janv.',
			'févr.',
			'mars',
			'avril',
			'mai',
			'juin',
			'juil.',
			'août',
			'sept.',
			'oct.',
			'nov.',
			'déc.',
	);
	public static $libellesJours = array(
			'lun.',
			'mar.',
			'mer.',
			'jeu.',
			'ven.',
			'sam.',
			'dim.',
	);
	public function __construct($date = null)
	{
		$this->setDate($date);
	}
	
	public function getDate()
	{
		return $this->date;
	}
	
	public function setDate($date = null)
	{
		if (!$date) {
			$this->date = new \DateTime();
		} else {
			if (!($date instanceof \DateTime)) {
				$this->date = new \DateTime($date);
			} else {
				$this->date = clone $date;
			}
		}		
	}
	
	public function getDateDebutSemaine($format = null)
	{
		$ds = clone $this->date;
		$ds->modify('-'.($ds->format('N')-1).' day');
		return ($format)? $ds->format($format) : $ds;
	}
	
	public function getDateFinSemaine($format = null)
	{
		$ds = clone $this->date;
		$ds->modify('+'.(7 - $ds->format('N')).' day');
		return ($format)? $ds->format($format) : $ds;
	}
	
	public function getSemaineSuivante($format = null)
	{
		$ds = $this->getDateDebutSemaine();
		$ds->modify('+1 week');
		return ($format)? $ds->format($format) : $ds;
	}
	
	public function getSemainePrecedente($format = null)
	{
		$ds = $this->getDateDebutSemaine();
		$ds->modify('-1 week');
		return ($format)? $ds->format($format) : $ds;
	}
	
	public function getAujourdhui($format = null)
	{
		$ds = new \DateTime();
		return ($format)? $ds->format($format) : $ds;
	}
	
	public static function getShortLibelleMois($mois)
	{
		return self::$libellesMois[$mois - 1];
	}
	
	public static function getShortLibelleJour($jour)
	{
		return self::$libellesJours[$jour - 1];
	}
}