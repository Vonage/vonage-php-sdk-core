<?php
declare(strict_types=1);

namespace Nexmo\Voice\NCCO\Action;

use InvalidArgumentException;
use Nexmo\Voice\NCCO\NCCO;

class Transfer implements ActionInterface
{
    /**
     * Array data for the destination
     * For an NCCO, it's the array copy of the NCCO
     * For a URL, it's the single-element array for the URL
     *
     * @var array
     */
    protected $destination;

    /**
     * Holds what kind of destination we are working with
     * @var string
     */
    protected $destinationType;

    public function __construct($destination)
    {
        if ($destination instanceof NCCO) {
            $this->destinationType = 'ncco';
            $this->destination = $destination->toArray();
            return;
        }

        if (is_string($destination)) {
            $this->destinationType = 'url';
            $this->destination = [$destination];
            return;
        }

        throw new \InvalidArgumentException('Destination for transfer must be an NCCO object or a URL');
    }

    public function toNCCOArray(): array
    {
        return [
            'action' => 'transfer',
            'destination' => [
                'type' => 'ncco',
                $this->destinationType => $this->destination,
            ]
        ];
    }

    public function jsonSerialize()
    {
        return $this->toNCCOArray();
    }
}
