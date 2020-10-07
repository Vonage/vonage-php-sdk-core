<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license   MIT <https://github.com/vonage/vonage-php/blob/master/LICENSE>
 */
declare(strict_types=1);

namespace Vonage\Numbers\Filter;

use InvalidArgumentException;
use Vonage\Entity\Filter\FilterInterface;
use Vonage\Numbers\Number;

class AvailableNumbers implements FilterInterface
{
    public const SEARCH_PATTERN_BEGIN = 0;
    public const SEARCH_PATTERN_CONTAINS = 1;
    public const SEARCH_PATTERN_ENDS = 2;

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

    /**
     * AvailableNumbers constructor.
     *
     * @param array $filter
     */
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
                $this->setSearchPattern((int)$filter['search_pattern']);
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

    /**
     * @return int[]
     */
    public function getQuery(): array
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

    /**
     * @return string|null
     */
    public function getCountry(): ?string
    {
        return $this->country;
    }

    /**
     * @param string $country
     */
    protected function setCountry(string $country): void
    {
        if (strlen($country) !== 2) {
            throw new InvalidArgumentException("Country must be in ISO 3166-1 Alpha-2 Format");
        }

        $this->country = $country;
    }

    /**
     * @return string|null
     */
    public function getFeatures(): ?string
    {
        return $this->features;
    }

    /**
     * @param string $features
     * @return $this
     */
    public function setFeatures(string $features): self
    {
        $this->features = $features;

        return $this;
    }

    /**
     * @return int
     */
    public function getPageIndex(): int
    {
        return $this->pageIndex;
    }

    /**
     * @param int $pageIndex
     * @return $this
     */
    public function setPageIndex(int $pageIndex): self
    {
        $this->pageIndex = $pageIndex;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getPattern(): ?string
    {
        return $this->pattern;
    }

    /**
     * @param string $pattern
     * @return $this
     */
    public function setPattern(string $pattern): self
    {
        $this->pattern = $pattern;

        return $this;
    }

    /**
     * @return int
     */
    public function getSearchPattern(): int
    {
        return $this->searchPattern;
    }

    /**
     * @param int $searchPattern
     * @return $this
     */
    public function setSearchPattern(int $searchPattern): self
    {
        $this->searchPattern = $searchPattern;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return $this
     */
    public function setType(string $type): self
    {
        // Workaround for code snippets
        if (empty($type)) {
            return $this;
        }

        if ($type !== Number::TYPE_FIXED && $type !== Number::TYPE_MOBILE) {
            throw new InvalidArgumentException('Invalid type of number');
        }

        $this->type = $type;

        return $this;
    }

    /**
     * @return int
     */
    public function getPageSize(): int
    {
        return $this->pageSize;
    }

    /**
     * @param int $pageSize
     * @return $this
     */
    public function setPageSize(int $pageSize): self
    {
        $this->pageSize = $pageSize;

        return $this;
    }
}
