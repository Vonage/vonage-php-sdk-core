<?php
declare(strict_types=1);

namespace Nexmo\Voice\NCCO\Action;

class Stream implements ActionInterface
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
    protected $streamUrl;

    public function __construct(string $streamUrl = null)
    {
        $this->streamUrl = $streamUrl;
    }

    /**
     * @param array{streamUrl: string, bargeIn?: bool, level?: float, loop?: int, voiceName?: string} $data
     */
    public static function factory(string $streamUrl, array $data): Stream
    {
        $stream = new Stream($streamUrl);

        if (array_key_exists('bargeIn', $data)) {
            $stream->setBargeIn($data['bargeIn']);
        }

        if (array_key_exists('level', $data)) {
            $stream->setLevel($data['level']);
        }

        if (array_key_exists('loop', $data)) {
            $stream->setLoop($data['loop']);
        }

        return $stream;
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

    public function getStreamUrl() : string
    {
        return $this->streamUrl;
    }

    /**
     * @return array{action: string, bargeIn: bool, level: float, loop: int, streamUrl: string}
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

    public function setText(string $text) : self
    {
        $this->text = $text;
        return $this;
    }

    public function setVoiceName(string $name) : self
    {
        $this->voiceName = $name;
        return $this;
    }

    /**
     * @return array{action: string, bargeIn: bool, level: float, loop: int, streamUrl: string}
     */
    public function toNCCOArray(): array
    {
        return [
            'action' => 'stream',
            'bargeIn' => $this->getBargeIn(),
            'level' => $this->getLevel(),
            'loop' => $this->getLoop(),
            'streamUrl' => [$this->getStreamUrl()],
        ];
    }
}
