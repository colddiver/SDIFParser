<?php

namespace SdifParser\Model;

use JsonSerializable;


class Split implements JsonSerializable
{
    
    use Common;
    
    private $distance;
    private $intervalTime;
    private $cumulativeTime;
    private $timeCode;
    private $splitType;
    private $swimmer;
    
    public function getDistance(): int {
        return $this->distance;
    }
    
    public function setDistance(string $value) {
        $this->distance = intval($value);
    }
    
    //FIXME perhaps add a bool $formatted paramater to pass a formatted string or a double?
    
    public function getIntervalTime(): float {
        //return $this->formatTime($this->intervalTime);
        return $this->intervalTime;
    }
    
    public function setIntervalTime(float $value) {
        $this->intervalTime = $value;
    }
    
    public function getCumulativeTime(): float {
        //return $this->formatTime($this->cumulativeTime);
        return $this->cumulativeTime;
    }
    
    public function setCumulativeTime(float $value) {
        $this->cumulativeTime = $value;
    }
    
    public function getTimeCode(): string {
        return $this->timeCode;
    }
    
    public function setTimeCode(string $value) {
        $this->timeCode = $this->extractTimeCode($value);
    }
    
    public function getSplitType(): string {
        return $this->splitType;
    }
    
    public function setSplitType(string $value) {
        $this->splitType = $this->decodeSplitType($value);
    }
    
    public function getSwimmer(): Swimmer {
        return $this->swimmer;
    }
    
    public function setSwimmer(Swimmer $value) {
        $this->swimmer = $value;
    }
    
    public function jsonSerialize() {
        return [
            'distance' => $this->distance,
            'intervalTime' => $this->intervalTime,
            'cumulativeTime' => $this->cumulativeTime,
            'timeCode' => $this->timeCode,
            'splitType' => $this->splitType,
            'swimmer' => $this->swimmer
        ];
    }
}