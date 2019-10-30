<?php
namespace SdifParser\Model;

use DateTime;
use JsonSerializable;

class Event implements JsonSerializable
{
    use Common;
    
    private $number;
    private $distance;
    private $stroke;
    private $course;
    private $date;
    private $isRelay;
    
    private $timeEntries = [];
    
    public function getNumber(): int {
        return $this->number;
    }
    
    public function setNumber(string $value) {
        $this->number = intval($value);
    }
    
    public function getDistance(): int {
        return $this->distance;
    }
    
    public function setDistance(string $value) {
        $this->distance = intval($value);
    }
    
    public function getStroke(): string {
        return $this->stroke;
    }
    
    public function setStroke(string $value) {
        
        $this->stroke = $this->decodeStroke($value);
        
        switch ($value) {
            case 1:
                $this->isRelay = false;
                break;
            case 2:
                $this->isRelay = false;
                break;
                
            case 3:
                $this->isRelay = false;
                break;
            case 4:
                $this->isRelay = false;
                break;
            case 5:
                $this->isRelay = false;
                break;
            case 6:
                $this->isRelay = true;
                break;
            case 7:
                $this->isRelay = true;
                break;
            default:
                $this->isRelay = false;
                break;
        }
    }
    
    public function getCourse(): string {
        return $this->course;
    }
    
    public function setCourse(string $value) {
        $this->course = $this->decodeCourse($value);
    }
    
    public function getDate(string $format = 'Y-m-d'): string {
        return date_format($this->date, $format);
    }
    
    public function setDate(string $value) {
        $this->date = DateTime::createFromFormat('mdY', $value);
    }
    
    public function getIsRelay(): bool {
        return $this->isRelay;
    }
    
    public function getName(): string {
        return sprintf('%s %s (%s)', $this->distance, $this->stroke, $this->course);
    }
    
    public function getTimeEntries(): array {
        return $this->timeEntries;
    }
    
    public function setTimeEntries(TimeEntry $value) {
        //$key = $value->getAgeGroup();
        //print json_encode($value) . "\n";
        //$key = count($this->timeEntries);
        $this->timeEntries[] = $value;
    }
    
    public function jsonSerialize() {
        return [
            'number' => $this->number,
            'distance' => $this->distance,
            'stroke' => $this->stroke,
            'course' => $this->course,
            'date' => $this->date,
            'isRelay' => $this->isRelay,
            'timeEntries' => $this->timeEntries
        ];
    }
}