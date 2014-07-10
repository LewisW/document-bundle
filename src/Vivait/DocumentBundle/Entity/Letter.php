<?php

namespace Vivait\DocumentBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Vivait\Common\Model\Task;

/**
 * Letter
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="LetterRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Letter implements Task\LetterInterface {
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
	 * @var string
	 * @ORM\Column(type="string", length=255, nullable=true)
	 */
	private $path;

    /**
     * @var integer
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $copies;

    /**
     * @var integer
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $priority;

	/**
	 * @var string
	 * @Assert\NotBlank()
	 * @ORM\Column(type="string", length=255, nullable=true)
	 */
	private $filename;

    /**
     * @var boolean
     * @ORM\Column(name="enabled", type="boolean", nullable=true)
     */
    private $enabled;

    /**
     * @ORM\ManyToMany(targetEntity="Bundle", mappedBy="letters")
     **/
    private $bundles;

    public function __construct() {
        $this->bundles = new \Doctrine\Common\Collections\ArrayCollection();
    }


//	/**
//	 * @Assert\File(
//	 *     maxSize = "2M",
//	 *     mimeTypes = {"application/vnd.openxmlformats-officedocument.wordprocessingml.document", "application/vnd.oasis.opendocument.text"},
//	 *     mimeTypesMessage = "Only 'Microsoft Word 2007-onward (docx)' and 'OpenDocument Text (odt)' documents are supported"
//	 * )
//	 */
	private $file;

	private $temp;


	/**
	 * Get file.
	 * @return UploadedFile
	 */
	public function getFile() {
		return $this->file;
	}

	public function getAbsolutePath() {
		return null === $this->path
			? null
			: $this->getUploadRootDir() . '/' . $this->path;
	}

	public function getWebPath() {
		return null === $this->path
			? null
			: $this->getUploadDir() . '/' . $this->path;
	}

	protected function getUploadRootDir() {
		// the absolute directory path where uploaded
		// documents should be saved

        $path = __DIR__ . '/';
        $count = 0;
        while(!in_array('web',scandir($path)) || !in_array('app',scandir($path))) {
            $path .= '../';
            $count++;
            if($count > 10) { throw new \Exception('Exceeded directory recursion looking for root directory');}
        }
		return $path . 'web/' . $this->getUploadDir();
	}

	protected function getUploadDir() {
		return 'uploads/letters';
	}

	/**
	 * Get id
	 * @return integer
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * @return mixed
	 */
	public function getExtension() {
		$extension = pathinfo($this->getPath(), PATHINFO_EXTENSION);
		return $extension;
	}

	/**
	 * Set name
	 * @param string $name
	 * @return Letter
	 */
	public function setName($name) {
		$this->name = $name;

		return $this;
	}

	/**
	 * Get name
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * Set category
	 * @param string $category
	 * @return Letter
	 */
	public function setCategory($category) {
		$this->category = $category;

		return $this;
	}

	/**
	 * Get category
	 * @return string
	 */
	public function getCategory() {
		return $this->category;
	}

	/**
	 * Sets file.
	 * @param UploadedFile $file
	 */
	public function setFile(UploadedFile $file = null) {
		$this->file = $file;
		// check if we have an old image path
		if (isset($this->path)) {
			// store the old name to delete after the update
			$this->temp = $this->path;
			$this->path = null;
		} else {
			$this->path = 'initial';
		}
	}

	/**
	 * @ORM\PrePersist()
	 * @ORM\PreUpdate()
	 */
	public function preUpload() {
		if (null !== $this->getFile()) {
			// do whatever you want to generate a unique name
			$filename   = sha1(uniqid(mt_rand(), true));
			$this->path = $filename . '.' . $this->getFile()->getClientOriginalExtension();
		}
	}

	/**
	 * @ORM\PostPersist()
	 * @ORM\PostUpdate()
	 */
	public function upload() {
		if (null === $this->getFile()) {
			return;
		}

		// if there is an error when moving the file, an exception will
		// be automatically thrown by move(). This will properly prevent
		// the entity from being persisted to the database on error
		$this->getFile()->move($this->getUploadRootDir(), $this->path);

		// check if we have an old image
		if (isset($this->temp)) {
			// delete the old image
			unlink($this->getUploadRootDir() . '/' . $this->temp);
			// clear the temp image path
			$this->temp = null;
		}
		$this->file = null;
	}

	/**
	 * @ORM\PostRemove()
	 */
	public function removeUpload() {
		if ($file = $this->getAbsolutePath()) {
			unlink($file);
		}
	}

	/**
	 * Set Path
	 * @param string $path
	 * @return $this
	 */
	public function setPath($path) {
		$this->path = $path;
		return $this;
	}

	/**
	 * Get Path
	 * @return string
	 */
	public function getPath() {
		return $this->path;
	}

	/**
	 * Set Filename
	 * @param string $filename
	 * @return $this
	 */
	public function setFilename($filename) {
		$this->filename = $filename;
		return $this;
	}

	/**
	 * Get Filename
	 * @return string
	 */
	public function getFilename() {
		return $this->filename;
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
    public function getCopies()
    {
        return $this->copies;
    }

    /**
     * @param int $copies
     */
    public function setCopies($copies)
    {
        $this->copies = $copies;
    }

    /**
     * @return int
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * @param int $priority
     */
    public function setPriority($priority)
    {
        $this->priority = $priority;
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
     * Add bundles
     *
     * @param \Vivait\DocumentBundle\Entity\Bundle $bundles
     * @return Letter
     */
    public function addBundle(\Vivait\DocumentBundle\Entity\Bundle $bundles)
    {
        $this->bundles[] = $bundles;

        return $this;
    }

    /**
     * Remove bundles
     *
     * @param \Vivait\DocumentBundle\Entity\Bundle $bundles
     */
    public function removeBundle(\Vivait\DocumentBundle\Entity\Bundle $bundles)
    {
        $this->bundles->removeElement($bundles);
    }

    /**
     * Get bundles
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getBundles()
    {
        return $this->bundles;
    }
}
