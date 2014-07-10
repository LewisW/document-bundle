<?php

namespace Vivait\DocumentBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Vivait\Common\Model\Task;

/**
 * Bundle
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="BundleRepository")
 */
class Bundle {
	/**
	 * @var integer
	 * @ORM\Column(name="id", type="integer")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	private $id;

	/**
	 * @var string
	 * @Assert\NotBlank()
	 * @ORM\Column(name="name", type="string", length=255)
	 */
	private $name;

	/**
	 * @var string
	 * @Assert\NotBlank()
	 * @ORM\Column(name="category", type="string", length=255)
	 */
	private $category;

    /**
     * @var boolean
     * @ORM\Column(name="enabled", type="boolean", nullable=true)
     */
    private $enabled;

    /**
     * @ORM\ManyToMany(targetEntity="Letter", inversedBy="bundles")
     * @ORM\JoinTable(name="bundle_letters")
     **/
    private $letters;

    public function __construct() {
        $this->letters = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * @return string
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @param string $category
     */
    public function setCategory($category)
    {
        $this->category = $category;
    }

    /**
     * @return boolean
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * @param boolean $enabled
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }




    /**
     * Get enabled
     *
     * @return boolean 
     */
    public function getEnabled()
    {
        return $this->enabled;
    }

    /**
     * Add letters
     *
     * @param \Vivait\DocumentBundle\Entity\Letter $letters
     * @return Bundle
     */
    public function addLetter(\Vivait\DocumentBundle\Entity\Letter $letters)
    {
        $this->letters[] = $letters;

        return $this;
    }

    /**
     * Remove letters
     *
     * @param \Vivait\DocumentBundle\Entity\Letter $letters
     */
    public function removeLetter(\Vivait\DocumentBundle\Entity\Letter $letters)
    {
        $this->letters->removeElement($letters);
    }

    /**
     * Get letters
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getLetters()
    {
        return $this->letters;
    }
}
