<?php

namespace AppBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use AppBundle\Document\Coordonnees;

/** 
 * @MongoDB\EmbeddedDocument
*/
class ContactCoordonnee {

    /**
     * @MongoDB\String
     */
    protected $telephoneFixe;

    /**
     * @MongoDB\String
     */
    protected $telephoneMobile;

    /**
     * @MongoDB\String
     */
    protected $fax;

    /**
     * @MongoDB\String
     */
    protected $email;

    /**
     * @MongoDB\String
     */
    protected $siteInternet;

     

    /**
     * Set telephoneFixe
     *
     * @param string $telephoneFixe
     * @return self
     */
    public function setTelephoneFixe($telephoneFixe)
    {
        $this->telephoneFixe = $telephoneFixe;
        return $this;
    }

    /**
     * Get telephoneFixe
     *
     * @return string $telephoneFixe
     */
    public function getTelephoneFixe()
    {
        return $this->telephoneFixe;
    }

    /**
     * Set telephoneMobile
     *
     * @param string $telephoneMobile
     * @return self
     */
    public function setTelephoneMobile($telephoneMobile)
    {
        $this->telephoneMobile = $telephoneMobile;
        return $this;
    }

    /**
     * Get telephoneMobile
     *
     * @return string $telephoneMobile
     */
    public function getTelephoneMobile()
    {
        return $this->telephoneMobile;
    }

    /**
     * Set fax
     *
     * @param string $fax
     * @return self
     */
    public function setFax($fax)
    {
        $this->fax = $fax;
        return $this;
    }

    /**
     * Get fax
     *
     * @return string $fax
     */
    public function getFax()
    {
        return $this->fax;
    }

    /**
     * Set email
     *
     * @param string $email
     * @return self
     */
    public function setEmail($email)
    {
        $this->email = $email;
        return $this;
    }

    /**
     * Get email
     *
     * @return string $email
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set siteInternet
     *
     * @param string $siteInternet
     * @return self
     */
    public function setSiteInternet($siteInternet)
    {
        $this->siteInternet = $siteInternet;
        return $this;
    }

    /**
     * Get siteInternet
     *
     * @return string $siteInternet
     */
    public function getSiteInternet()
    {
        return $this->siteInternet;
    }

}
