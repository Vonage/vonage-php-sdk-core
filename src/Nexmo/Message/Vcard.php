<?php
namespace Nexmo\Message;
use Nexmo\MessageAbstract;
use Nexmo\MessageInterface;

/**
 * SMS Text Message
 * @author Tim Lytle <tim.lytle@nexmo.com>
 */
class Vcard extends MessageAbstract implements MessageInterface
{
    const TYPE = 'vcard';

    /**
     * Message Body
     * @var string
     */
    protected $vcard;

    /**
     * Create a new SMS text message.
     *
     * @param string $to
     * @param string $from
     * @param string $vcard
     */
    public function __construct($to, $from, $vcard)
    {
        parent::__construct($to, $from);
        $this->vcard = (string) $vcard;
    }

    /**
     * Get an array of params to use in an API request.
     */
    public function getParams()
    {
        return array_merge(parent::getParams(), array(
            'vcard' => $this->vcard
        ));
    }
}