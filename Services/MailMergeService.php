<?php

namespace Vivait\DocumentBundle\Services;

use Doctrine\ORM\EntityManager;
use MBence\OpenTBSBundle\Services\OpenTBS;
use Vivait\Common\Model\Task\LetterInterface;
use JMS\DiExtraBundle\Annotation as DI;
use Vivait\TaskBundle\Entity\TaskMessageAttachment;

/**
 * @DI\Service("vivait.document.mailmerge");
 */
class MailMergeService {
	/**
	 * @var OpenTBS
	 */
	protected $tbs;

	protected $fields = array();

	/**
	 * @DI\InjectParams({
	 *     "tbs" = @DI\Inject("opentbs"),
	 *     "em" = @DI\Inject("doctrine.orm.entity_manager")
	 * })
	 */
	function __construct(OpenTBS $tbs) {
		$this->tbs = $tbs;
	}

	/**
	 * Flattens a nested array into a one-dimensional array while concat keys
	 * @param        $array
	 * @param string $prefix
	 * @return array
	 */
	protected static function flatten($array, $prefix = '') {
		$result = array();
		foreach ($array as $key => $value) {
			if (is_array($value)) {
				$result = $result + self::flatten($value, $prefix . $key . '.');
			} else {
				$result[$prefix . $key] = $value;
			}
		}
		return $result;
	}

	public function mergeFile($source, $destination = null) {
		if ($destination === null) {
			$destination = $source;
		}

		$this->tbs->LoadTemplate($source);

		// Flatten and merge the fields
		foreach ($this->fields as $base => $group) {
			$this->tbs->MergeField($base, self::flatten($group));
		}

		$this->tbs->Show(OPENTBS_FILE, $destination);

		return $destination;
	}

	public function mergeString($content) {
		$fields = self::flatten($this->fields);

		foreach ($fields as $search => $replace) {
			$content = str_replace(sprintf("{{ %s }}", $search), $replace, $content);
		}

		return $content;
	}
} 