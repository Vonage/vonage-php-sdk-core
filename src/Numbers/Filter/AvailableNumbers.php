<?php

declare(strict_types=1);

namespace Vonage\Numbers\Filter;

use InvalidArgumentException;
use Vonage\Client\Exception\Request;
use Vonage\Entity\Filter\FilterInterface;
use Vonage\Numbers\Number;

use function array_key_exists;
use function implode;
use function is_array;
use function strlen;

class AvailableNumbers implements FilterInterface
{
    public const SEARCH_PATTERN_BEGIN = 0;
    public const SEARCH_PATTERN_CONTAINS = 1;
    public const SEARCH_PATTERN_ENDS = 2;

    public static array $possibleParameters = [
        'country' => 'string',
        'pattern' => 'string',
        'search_pattern' => 'integer',
        'size' => 'integer',
        'index' => 'integer',
        'has_application' => 'boolean',
        'application_id' => 'string',
        'features' => 'string',
        'type' => 'string',
    ];

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

    protected ?bool $hasApplication = null;

    protected ?string $applicationId = null;

    public function __construct(array $filter = [])
    {
        foreach ($filter as $key => $value) {
            if (!array_key_exists($key, self::$possibleParameters)) {
                throw new Request("Unknown option: '" . $key . "'");
            }

            switch (self::$possibleParameters[$key]) {
                case 'boolean':
                    $value = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                    if (is_null($value)) {
                        throw new Request("Invalid value: '" . $key . "' must be a boolean value");
                    }
                    $value = $value ? "true" : "false";
                    break;
                case 'integer':
                    $value = filter_var($value, FILTER_VALIDATE_INT);
                    if ($value === false) {
                        throw new Request("Invalid value: '" . $key . "' must be an integer");
                    }
                    break;
                default:
                    // No-op, take the value whatever it is
                    break;
            }
        }

        if (array_key_exists('country', $filter)) {
            $this->setCountry($filter['country']);
        }

        if (array_key_exists('size', $filter)) {
            $this->setPageSize((int)$filter['size']);
        }

        if (array_key_exists('index', $filter)) {
            $this->setPageIndex((int)$filter['index']);
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

        if (array_key_exists('has_application', $filter)) {
            $this->setHasApplication((bool)$filter['has_application']);
        }

        if (array_key_exists('application_id', $filter)) {
            $this->setApplicationId($filter['application_id']);
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

        if ($this->getHasApplication() !== null) {
            $data['has_application'] = $this->getHasApplication();
        }

        if ($this->getApplicationId() !== null) {
            $data['application_id'] = $this->getApplicationId();
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

    public function getFeatures(): ?string
    {
        return $this->features;
    }

    /**
     * @return $this
     */
    public function setFeatures(string $features): self
    {
        $this->features = $features;

        return $this;
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

    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * @return $this
     */
    public function setType(string $type): self
    {
        // Workaround for code snippets
        if (empty($type)) {
            return $this;
        }

        $valid = [
            Number::TYPE_FIXED,
            Number::TYPE_MOBILE,
            Number::TYPE_TOLLFREE,
        ];

        if (!in_array($type, $valid)) {
            throw new InvalidArgumentException('Invalid type of number');
        }

        $this->type = $type;

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
}
