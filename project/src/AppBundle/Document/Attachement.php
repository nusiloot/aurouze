<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Attachement
 *
 * @author mathurin
 */

namespace AppBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Component\HttpFoundation\File\File;


/**
 * @MongoDB\Document
 * @Vich\Uploadable
 * @MongoDB\Document(repositoryClass="AppBundle\Repository\AttachementRepository")
 */
class Attachement
{

  /**
    * @MongoDB\Id(strategy="AUTO")
    */
   protected $id;

   /**
    * @MongoDB\File
    * @Vich\UploadableField(mapping="attachement_image", fileNameProperty="imageName", size="imageSize")
    *
    */
   protected $imageFile;

   /**
    * @MongoDB\string
    *
    */
   protected $imageName;

   /**
    * @MongoDB\Int
    *
    */
   protected $imageSize;

   /**
    * @MongoDB\Date
    *
    */
   protected $updatedAt;

   /**
    * @MongoDB\string
    *
    */
   protected $titre;

   /**
    * @MongoDB\string
    *
    */
   protected $originalName;

   /**
    * @MongoDB\ReferenceOne(targetDocument="Societe", inversedBy="attachements", simple=true)
    */
   protected $societe;

   /**
    * @MongoDB\ReferenceOne(targetDocument="Etablissement", inversedBy="attachements", simple=true)
    */
   protected $etablissement;

   /**
    * @MongoDB\Boolean
    */
   protected $visibleTechnicien;


    /**
     * Set updatedAt
     *
     * @param date $updatedAt
     * @return $this
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    /**
     * Get updatedAt
     *
     * @return date $updatedAt
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Set titre
     *
     * @param string $titre
     * @return $this
     */
    public function setTitre($titre)
    {
        $this->titre = $titre;
        return $this;
    }

    /**
     * Get titre
     *
     * @return string $titre
     */
    public function getTitre()
    {
        return $this->titre;
    }

    /**
     * Get id
     *
     * @return id $id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set societe
     *
     * @param AppBundle\Document\Societe $societe
     * @return $this
     */
    public function setSociete(\AppBundle\Document\Societe $societe)
    {
        $this->societe = $societe;
        return $this;
    }

    /**
     * Get societe
     *
     * @return AppBundle\Document\Societe $societe
     */
    public function getSociete()
    {
        return $this->societe;
    }

    /**
     * Set imageName
     *
     * @param string $imageName
     * @return $this
     */
    public function setImageName($imageName)
    {
        $this->imageName = $imageName;
        return $this;
    }

    /**
     * Get imageName
     *
     * @return string $imageName
     */
    public function getImageName()
    {
        return $this->imageName;
    }

    /**
     * Set imageSize
     *
     * @param int $imageSize
     * @return $this
     */
    public function setImageSize($imageSize)
    {
        $this->imageSize = $imageSize;
        return $this;
    }

    /**
     * Get imageSize
     *
     * @return int $imageSize
     */
    public function getImageSize()
    {
        return $this->imageSize;
    }

    /**
     * Set imageFile
     *
     * @param file $imageFile
     * @return $this
     */
    public function setImageFile($imageFile = null)
    {
      $this->imageFile = $imageFile;

      if ($imageFile) {
          $this->updatedAt = new \DateTime('now');
      }
      if($imageFile instanceof \Symfony\Component\HttpFoundation\File\UploadedFile && $imageFile->getClientOriginalName()){
        $this->setOriginalName($imageFile->getClientOriginalName());
      }
    }

    /**
     * Get imageFile
     *
     * @return file $imageFile
     */
    public function getImageFile()
    {
        return $this->imageFile;
    }

    /**
     * Set etablissement
     *
     * @param AppBundle\Document\Etablissement $etablissement
     * @return $this
     */
    public function setEtablissement(\AppBundle\Document\Etablissement $etablissement)
    {
        $this->etablissement = $etablissement;
        return $this;
    }

    /**
     * Get etablissement
     *
     * @return AppBundle\Document\Etablissement $etablissement
     */
    public function getEtablissement()
    {
        return $this->etablissement;
    }

    /**
     * Set visibleTechnicien
     *
     * @param boolean $visibleTechnicien
     * @return $this
     */
    public function setVisibleTechnicien($visibleTechnicien)
    {
        $this->visibleTechnicien = $visibleTechnicien;
        return $this;
    }

    /**
     * Get visibleTechnicien
     *
     * @return boolean $visibleTechnicien
     */
    public function getVisibleTechnicien()
    {
        return $this->visibleTechnicien;
    }

    public function isPdf(){
      return preg_match('/\.pdf$/',$this->getImageName());
    }

    /**
     * Set originalName
     *
     * @param string $originalName
     * @return $this
     */
    public function setOriginalName($originalName)
    {
        $this->originalName = $originalName;
        return $this;
    }

    /**
     * Get originalName
     *
     * @return string $originalName
     */
    public function getOriginalName()
    {
        return $this->originalName;
    }

    public function removeFile(){
        unlink($this->getImageFile()->getPathName());
    }
}
