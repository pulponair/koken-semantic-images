<?php
class PulponairSemanticImages extends KokenPlugin {
	const PROPERTY_SETTING_PREFIX = 'property_';

	/**
	 * Item property map
	 *
	 * @var array
	 */
	protected $itemPropertyMap = array(
		'dateCreated' => 'captured_on/datetime',
		'datePublished' => 'published_on/datetime',
		'contentURL' => 'url'
	);

	/**
	 * Constructor registers filter
	 *
	 */
	public function __construct() {
		$this->register_filter('site.output', 'render');
	}

	/**
	 * The actual render method. Searches for image tags and appends semantinc tags.
	 *
	 * @param string $content
	 * @return string mixed
	 */
	public function render($content) {
		$pattern = '/<img.*data-base=".*\/(.*)\/.*,.*?".+?>/';
		$imageCount = preg_match_all($pattern, $content, $matches);
		if ($imageCount) {
			$search = array();
			$replace = array();
			for ($i = 0; $i < $imageCount; $i++) {
				$search[] = $matches[0][$i];
				$image = Koken::api('/content/index/' . (int)$matches[1][$i]);
				$replace[] = $this->wrapByItemScopeTag(
					$matches[0][$i] .
					$this->mapImagePropertiesToItemPropertiesTags($image) ,
					'ImageObject');
			}
			$content = str_replace($search, $replace, $content);
		}
		return $content;
	}

	/**
	 * Maps the image properties to item property tags. Taking the configuration into account
	 *
	 * @param array $image
	 * @return string
	 */
	protected function mapImagePropertiesToItemPropertiesTags($image) {
		$result = '';

		foreach($this->data as $key => $value) {
			if (strpos($key, self::PROPERTY_SETTING_PREFIX) !== 0) {
				continue;
			}

			if ($value) {
				$itemProperty = substr($key, strlen(self::PROPERTY_SETTING_PREFIX));
				//echo $this->itemPropertyMap[$itemProperty] . '<br>';
				if ($path = $this->itemPropertyMap[$itemProperty]) {
					$content = $this->getArrayElementByPath($image, $path);
				} else {
					$content = $this->getArrayElementByPath($image, $value);
				}

				if (!empty($content)) {
					$result .= $this->createItemPropertyTag($itemProperty, $content);
				}
			}
		}
		//var_dump($result);
		return $result;
	}

	/**
	 * Gets an array element by path
	 *
	 * @param $source
	 * @param $path
	 * @param string $pathDelimiter
	 * @return mixed
	 */
	protected function getArrayElementByPath($source, $path, $pathDelimiter = '/') {
		$explodedPath = explode($pathDelimiter, $path);
		$pointer = &$source;
		foreach ($explodedPath as $segment) {
			$pointer = &$pointer[$segment];
		}

		return $pointer;
	}

	/**
	 * Creates an item property tag.
	 *
	 * @param string $property
	 * @param string $content
	 * @param string $tag
	 * @return string
	 */
	protected function createItemPropertyTag($property, $content, $tag = 'meta') {
		return '<' . $tag . ' itemprop="' . $property . '" content="' . $content . '" />';
	}

	/**
	 * Wraps given content in an schema.org item scope
	 *
	 * @param string $content
	 * @param sting $itemType
	 * @param string $tag
	 * @return string
	 */
	protected function wrapByItemScopeTag($content, $itemType, $tag = 'span') {
		return '<' . $tag . ' itemscope itemtype="http://schema.org/' . $itemType . '">' .
			$content .
			'</' . $tag . '>';
	}

}