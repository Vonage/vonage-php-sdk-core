<?php
declare(strict_types=1);

namespace Vonage\Voice\NCCO\Action;

class Stream implements ActionInterface
{
    /**
     * @var bool
     */
    protected $bargeIn;

    /**
     * @var float
     */
    protected $level;

    /**
     * @var int
     */
    protected $loop;

    /**
     * @var string
     */
    protected $streamUrl;

    public function __construct(string $streamUrl)
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
            $stream->setBargeIn(
                filter_var($data['bargeIn'], FILTER_VALIDATE_BOOLEAN, ['flags' => FILTER_NULL_ON_FAILURE])
            );
        }

        if (array_key_exists('level', $data)) {
            $stream->setLevel(
                filter_var($data['level'], FILTER_VALIDATE_FLOAT, ['flags' => FILTER_NULL_ON_FAILURE])
            );
        }

        if (array_key_exists('loop', $data)) {
            $stream->setLoop(
                filter_var($data['loop'], FILTER_VALIDATE_INT, ['flags' => FILTER_NULL_ON_FAILURE])
            );
        }

        return $stream;
    }

    public function getBargeIn() : ?bool
    {
        return $this->bargeIn;
    }

    public function getLevel() : ?float
    {
        return $this->level;
    }

    public function getLoop() : ?int
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

    /**
     * @return array{action: string, bargeIn: bool, level: float, loop: int, streamUrl: string}
     */
    public function toNCCOArray(): array
    {
        $data = [
            'action' => 'stream',
            'streamUrl' => [$this->getStreamUrl()],
        ];

        if (!is_null($this->getBargeIn())) {
            $data['bargeIn'] = $this->getBargeIn() ? 'true' : 'false';
        }

        if (!is_null($this->getLevel())) {
            $data['level'] = (string) $this->getLevel();
        }

        if (!is_null($this->getLoop())) {
            $data['loop'] = (string) $this->getLoop();
        }

        return $data;
    }
}
