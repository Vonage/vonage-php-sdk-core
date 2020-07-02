<?php
declare(strict_types=1);

namespace Nexmo\Voice\NCCO\Action;

class Talk implements ActionInterface
{
    /**
     * @var bool
     */
    protected $bargeIn = false;

    /**
     * @var float
     */
    protected $level = 0;

    /**
     * @var int
     */
    protected $loop = 1;

    /**
     * @var string
     */
    protected $text;

    /**
     * @var string
     */
    protected $voiceName = 'kimberly';

    public function __construct(string $text = null)
    {
        $this->text = $text;
    }

    /**
     * @param array{text: string, bargeIn?: bool, level?: float, loop?: int, voiceName?: string} $data
     */
    public static function factory(string $text, array $data): Talk
    {
        $talk = new Talk($text);

        if (array_key_exists('bargeIn', $data)) {
            $talk->setBargeIn($data['bargeIn']);
        }

        if (array_key_exists('level', $data)) {
            $talk->setLevel($data['level']);
        }

        if (array_key_exists('loop', $data)) {
            $talk->setLoop($data['loop']);
        }

        if (array_key_exists('voiceName', $data)) {
            $talk->setVoiceName($data['voiceName']);
        }
        
        return $talk;
    }

    public function getBargeIn() : bool
    {
        return $this->bargeIn;
    }

    public function getLevel() : float
    {
        return $this->level;
    }

    public function getLoop() : int
    {
        return $this->loop;
    }

    public function getText() : string
    {
        return $this->text;
    }

    public function getVoiceName() : string
    {
        return $this->voiceName;
    }

    /**
     * @return array{action: string, bargeIn: bool, level: float, loop: int, text: string, voiceName: string}
     */
    public function jsonSerialize()
    {
        return $this->toNCCOArray();
    }

    public function setBargeIn(bool $value) : self
    {
        $this->bargeIn = $value;
        return $this;
    }

    public function setLevel(float $level) : self
    {
        $this->level = $level;
        return $this;
    }

    public function setLoop(int $times) : self
    {
        $this->loop = $times;
        return $this;
    }

    public function setVoiceName(string $name) : self
    {
        $this->voiceName = $name;
        return $this;
    }

    /**
     * @return array{action: string, bargeIn: bool, level: float, loop: int, text: string, voiceName: string}
     */
    public function toNCCOArray(): array
    {
        return [
            'action' => 'talk',
            'bargeIn' => $this->getBargeIn(),
            'level' => $this->getLevel(),
            'loop' => $this->getLoop(),
            'text' => $this->getText(),
            'voiceName' => $this->getVoiceName(),
        ];
    }
}
