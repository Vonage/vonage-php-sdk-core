<?php
declare(strict_types=1);

namespace Nexmo\Account;

use Nexmo\Entity\Hydrator\HydratorInterface;

/**
 * Provides a common interface for creating Pricing objects
 *
 * @internal
 */
class PriceFactory
{
    const TYPE_PREFIX = 0;
    const TYPE_SMS = 1;
    const TYPE_VOICE = 2;

    /**
     * @var HydratorInterface
     */
    protected $hydrator;
    
    public function __construct(HydratorInterface $hydrator)
    {
        $this->hydrator = $hydrator;
    }

    /**
     * @todo Figure out a way to do this without specifying a type
     *
     * @param array<string, scalar> $data Data for the price request
     */
    public function build(array $data, int $type) : Price
    {
        switch ($type) {
            case 0:
                $class = PrefixPrice::class;
                break;
            case 1:
                $class = SmsPrice::class;
                break;
            case 2:
                $class = VoicePrice::class;
                break;
            default:
                throw new \InvalidArgumentException('Invalid pricing type requested', $type);
        }

        $price = new $class();
        return $this->hydrator->hydrateObject($data, $price);
    }
}
