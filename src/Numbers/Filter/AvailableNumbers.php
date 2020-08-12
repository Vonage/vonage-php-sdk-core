<?php
declare(strict_types=1);

namespace Vonage\Numbers\Filter;

use Vonage\Numbers\Number;
use InvalidArgumentException;
use Vonage\Entity\Filter\FilterInterface;

class AvailableNumbers implements FilterInterface
{
    const SEARCH_PATTERN_BEGIN = 0;
    const SEARCH_PATTERN_CONTAINS = 1;
    const SEARCH_PATTERN_ENDS = 2;

    /**
     * @var string
     */
    protected $country;

    /**
     * @var string
     */
    protected $features;

    /**
     * @var int
     */
    protected $pageIndex = 1;

    /**
     * @var int
     */
    protected $pageSize = 10;

    /**
     * @var string
     */
    protected $pattern;

    /**
     * @var int
     */
    protected $searchPattern = 0;

    /**
     * @var string
     */
    protected $type;

    public function __construct(array $filter = [])
    {
        if (array_key_exists('country', $filter)) {
            $this->setCountry($filter['country']);
        }
        if (array_key_exists('size', $filter)) {
            $this->setPageSize($filter['size']);
        }
        if (array_key_exists('index', $filter)) {
            $this->setPageIndex($filter['index']);
        }
        
        if (array_key_exists('pattern', $filter)) {
            $this->setPattern($filter['pattern']);
            if (array_key_exists('search_pattern', $filter)) {
                $this->setSearchPattern((int) $filter['search_pattern']);
            }
        }
        if (array_key_exists('type', $filter)) {
            $this->setType($filter['type']);
        }
        if (array_key_exists('features', $filter)) {
            // Handle the old format where we asked for an array
            if (is_array($filter['features'])) {
                $filter['features'] = implode(',', $filter['features']);
            }
            $this->setFeatures($filter['features']);
        }
    }

    public function getQuery()
    {
        $data = [
            'size' => $this->getPageSize(),
            'index' => $this->getPageIndex(),
        ];

        if ($this->getCountry()) {
            $data['country'] = $this->getCountry();
        }

        if ($this->getPattern()) {
            $data['search_pattern'] = $this->getSearchPattern();
            $data['pattern'] = $this->getPattern();
        }

        if ($this->getType()) {
            $data['type'] = $this->getType();
        }

        if ($this->getFeatures()) {
            $data['features'] = $this->getFeatures();
        }

        return $data;
    }

    public function getCountry() : ?string
    {
        return $this->country;
    }

    protected function setCountry(string $country) : void
    {
        if (strlen($country) !== 2) {
            throw new \InvalidArgumentException("Country must be in ISO 3166-1 Alpha-2 Format");
        }

        $this->country = $country;
    }

    public function getFeatures() : ?string
    {
        return $this->features;
    }

    public function setFeatures(string $features) : self
    {
        $this->features = $features;
        return $this;
    }

    public function getPageIndex() : int
    {
        return $this->pageIndex;
    }

    public function setPageIndex(int $pageIndex) : self
    {
        $this->pageIndex = $pageIndex;
        return $this;
    }

    public function getPattern() : ?string
    {
        return $this->pattern;
    }

    public function setPattern(string $pattern) : self
    {
        $this->pattern = $pattern;
        return $this;
    }

    public function getSearchPattern() : int
    {
        return $this->searchPattern;
    }

    public function setSearchPattern(int $searchPattern) : self
    {
        $this->searchPattern = $searchPattern;
        return $this;
    }

    public function getType() : ?string
    {
        return $this->type;
    }

    public function setType(string $type) : self
    {
        // Workaround for code snippets
        if (empty($type)) {
            return $this;
        }

        if ($type !== Number::TYPE_FIXED && $type !== Number::TYPE_MOBILE) {
            var_dump($type);
            throw new InvalidArgumentException('Invalid type of number');
        }

        $this->type = $type;
        return $this;
    }

    public function getPageSize() : int
    {
        return $this->pageSize;
    }

    public function setPageSize(int $pageSize) : self
    {
        $this->pageSize = $pageSize;
        return $this;
    }
}
