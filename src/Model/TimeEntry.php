<?php

namespace SdifParser\Model;

use DateTime;
use JsonSerializable;


class TimeEntry implements JsonSerializable
{
    use Common;
    
    private $eventName;
    private $gender;
    private $ageGroup;
    private $date;
    private $seedTime;
    private $seedTimeCode;
    private $seedCourse;
    private $prelimsTime;
    private $prelimsTimeCode;
    private $prelimsCourse;
    private $swimOffTime;
    private $swimOffTimeCode;
    private $swimOffCourse;
    private $finalsTime;
    private $finalsTimeCode;
    private $finalsCourse;
    private $prelimsHeat;
    private $prelimsLane;
    private $finalsHeat;
    private $finalsLane;
    private $prelimsPlace;
    private $finalsPlace;
    private $points;
    private $timeClassCode;
    private $flightStatus;
    
    private $isRelay;
    private $relayTeamAge;
    
    private $swimmers = [];
    private $splits = [];
    
    public function getEventName(): string {
        return $this->eventName;
    }
    
    public function setEventName(string $value) {
        $this->eventName = $value;
    }
    
    public function getGender(): string {
        return $this->gender;
    }
    
    public function setGender(string $value) {
        $this->gender = $value;
    }
    
    public function getAgeGroup(): string {
        return $this->ageGroup;
    }
    
    public function setAgeGroup(string $value) {
        $this->ageGroup = $this->parseAgeGroup($value);
    }
    
    public function getDate(string $format = 'Y-m-d'): string {
        return date_format($this->date, $format);
    }
    
    public function setDate(string $value) {
        $this->date = DateTime::createFromFormat('mdY', $value);
    }
    
    public function getSeedTime(): string {
        return $this->formatTime($this->seedTime);
    }
    
    public function setSeedTime(string $value) {
        $this->seedTime = $this->parseTime($value);
        $this->seedTimeCode = $this->extractTimeCode($value);
    }
    
    public function getSeedCourse(): string {
        return $this->seedCourse;
    }
    
    public function setSeedCourse(string $value) {
        $this->seedCourse = $this->decodeCourse($value);
    }
    
    public function getPrelimTime(): string {
        return $this->formatTime($this->prelimsTime);
    }
    
    public function setPrelimTime(string $value) {
        $this->prelimsTime = $this->parseTime($value);
        $this->prelimsTimeCode = $this->extractTimeCode($value);
    }
    
    public function getPrelimCourse(): string {
        return $this->prelimsCourse;
    }
    
    public function setPrelimCourse(string $value) {
        $this->prelimsCourse = $this->decodeCourse($value);
    }
    
    public function getSwimOffTime(): string {
        return $this->formatTime($this->swimOffTime);
    }
    
    public function setSwimOffTime(string $value) {
        $this->swimOffTime = $this->parseTime($value);
        $this->swimOffTimeCode = $this->extractTimeCode($value);
    }
    
    public function getSwimOffCourse(): string {
        return $this->swimOffCourse;
    }
    
    public function setSwimOffCourse(string $value) {
        $this->swimOffCourse = $this->decodeCourse($value);
    }
    
    public function getFinalsTime(): string {
        return $this->formatTime($this->finalsTime);
    }
    
    public function setFinalsTime(string $value) {
        $this->finalsTime = $this->parseTime($value);
        $this->finalsTimeCode = $this->extractTimeCode($value);
    }
    
    public function getFinalsCourse(): string {
        return $this->finalsCourse;
    }
    
    public function setFinalsCourse(string $value) {
        $this->finalsCourse = $this->decodeCourse($value);
    }
    
    public function getPrelimHeat(): int {
        return $this->prelimsHeat;
    }
    
    public function setPrelimHeat(string $value) {
        $this->prelimsHeat = intval($value);
    }
    
    public function getPrelimLane(): int {
        return $this->prelimsLane;
    }
    
    public function setPrelimLane(string $value) {
        $this->prelimsLane = intval($value);
    }
    
    public function getFinalsHeat(): int {
        return $this->finalsHeat;
    }
    
    public function setFinalsHeat(string $value) {
        $this->finalsHeat = intval($value);
    }
    
    public function getFinalsLane(): int {
        return $this->finalsLane;
    }
    
    public function setFinalsLane(string $value) {
        $this->finalsLane = intval($value);
    }
    
    public function getPrelimPlace(): int {
        return $this->prelimsPlace;
    }
    
    public function setPrelimPlace(string $value) {
        $this->prelimsPlace = intval($value);
    }
    
    public function getFinalsPlace(): int {
        return $this->finalsPlace;
    }
    
    public function setFinalsPlace(string $value) {
        $this->finalsPlace = intval($value);
    }
    
    public function getPoints(): float {
        return $this->points;
    }
    
    public function setPoints(string $value) {
        $this->points = floatval($value);
    }
    
    public function getTimeClassCode(): string {
        return $this->timeClassCode;
    }
    
    public function setTimeClassCode(string $value) {
        $this->timeClassCode = $value;
    }
    
    public function getFlightStatus(): string {
        return $this->flightStatus;
    }
    
    public function setFlightStatus(string $value) {
        $this->flightStatus = $value;
    }
    
    public function getIsRelay(): bool {
        return $this->isRelay;
    }
    
    
    public function getRelayTeamAge(): int {
        return $this->relayTeamAge;
    }
    
    public function setRelayTeamAge(string $value) {
        $this->relayTeamAge = intval($value);
    }
    
    
    public function getSwimmers(): array {
        return $this->swimmers;
    }
    
    public function setSwimmers(Swimmer $value) {
        $key = $value->getId();
        $this->swimmers[$key] = $value;
    }
    
    public function getSplits(): array {
        return $this->splits;
    }
    
    public function setSplits(Split $value) {
        $this->splits[] = $value;
    }
    
    public function getSplitAtIndex($i): Split {
        print $i . "\n";
        return $this->splits[$i];
    }
    
    public function getLastSplit(): Split {
        return end($this->splits);
    }
    
    public function getLastSplitTime(): float {
        if (count($this->splits) > 0) {
            $lastSplit = $this->getLastSplit();
            return $lastSplit->getCumulativeTime();
        }
        return 0.0;
    }
    
    public function jsonSerialize() {
        return [
            'eventName' => $this->eventName,
            'gender' => $this->gender,
            'ageGroup' => $this->ageGroup,
            'date' => $this->getDate(),
            'seedTime' => $this->seedTime,
            'seedCourse' => $this->seedCourse,
            'prelimsTime' => $this->prelimsTime,
            'prelimsCourse' => $this->prelimsCourse,
            'swimOffTime' => $this->swimOffTime,
            'swimOffCourse' => $this->swimOffCourse,
            'finalsTime' => $this->finalsTime,
            'finalsCourse' => $this->finalsCourse,
            'prelimsHeat' => $this->prelimsHeat,
            'prelimsLane' => $this->prelimsLane,
            'finalsHeat' => $this->finalsHeat,
            'finalsLane' => $this->finalsLane,
            'prelimsPlace' => $this->prelimsPlace,
            'finalsPlace' => $this->finalsPlace,
            'points' => $this->points,
            'timeClassCode' => $this->timeClassCode,
            'flightStatus' => $this->flightStatus,
            'isRelay' => $this->isRelay,
            'relayTeamAge' => $this->relayTeamAge,
            'swimmers' => $this->swimmers,
            'splits' => $this->splits
        ];
    }
}