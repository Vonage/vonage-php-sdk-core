<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

namespace Vonage\Numbers\Filter;

use InvalidArgumentException;
use Vonage\Entity\Filter\FilterInterface;

use function array_key_exists;
use function filter_var;
use function is_null;
use function strlen;

class OwnedNumbers implements FilterInterface
{
    public const SEARCH_PATTERN_BEGIN = 0;
    public const SEARCH_PATTERN_CONTAINS = 1;
    public const SEARCH_PATTERN_ENDS = 2;

    /**
     * @var string
     */
    protected $applicationId;

    /**
     * @var string
     */
    protected $country;

    /**
     * @var bool
     */
    protected $hasApplication;

    /**
     * @var int
     */
    protected $pageIndex = 1;

    /**
     * @var string
     */
    protected $pattern;

    /**
     * @var int
     */
    protected $searchPattern = 0;

    /**
     * @var int
     */
    protected $pageSize = 10;

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
                $this->setSearchPattern($filter['search_pattern']);
            }
        }

        if (array_key_exists('application_id', $filter)) {
            $this->setApplicationId($filter['application_id']);
        }

        if (array_key_exists('has_application', $filter)) {
            $this->setHasApplication(filter_var($filter['has_application'], FILTER_VALIDATE_BOOLEAN));
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

        if ($this->getApplicationId()) {
            $data['application_id'] = $this->getApplicationId();
        }

        if (!is_null($this->getHasApplication())) {
            // The API requires a string
            $data['has_application'] = $this->getHasApplication() ? 'true' : 'false';
        }

        return $data;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    protected function setCountry(string $country): void
    {
        if (strlen($country) !== 2) {
            throw new InvalidArgumentException("Country must be in ISO 3166-1 Alpha-2 Format");
        }

        $this->country = $country;
    }

    public function getPageIndex(): int
    {
        return $this->pageIndex;
    }

    /**
     * @return $this
     */
    public function setPageIndex(int $pageIndex): self
    {
        $this->pageIndex = $pageIndex;

        return $this;
    }

    public function getPattern(): ?string
    {
        return $this->pattern;
    }

    /**
     * @return $this
     */
    public function setPattern(string $pattern): self
    {
        $this->pattern = $pattern;

        return $this;
    }

    public function getSearchPattern(): int
    {
        return $this->searchPattern;
    }

    /**
     * @return $this
     */
    public function setSearchPattern(int $searchPattern): self
    {
        $this->searchPattern = $searchPattern;

        return $this;
    }

    public function getPageSize(): int
    {
        return $this->pageSize;
    }

    /**
     * @return $this
     */
    public function setPageSize(int $pageSize): self
    {
        $this->pageSize = $pageSize;

        return $this;
    }

    public function getApplicationId(): ?string
    {
        return $this->applicationId;
    }

    /**
     * @return $this
     */
    public function setApplicationId(string $applicationId): self
    {
        $this->applicationId = $applicationId;

        return $this;
    }

    public function getHasApplication(): ?bool
    {
        return $this->hasApplication;
    }

    /**
     * @return $this
     */
    public function setHasApplication(bool $hasApplication): self
    {
        $this->hasApplication = $hasApplication;

        return $this;
    }
}
