<?php 

namespace SdifParser\Model;

use DateTime;
use JsonSerializable;


class Meet implements JsonSerializable
{
    use Common;
    
    private $name;
    private $address1;
    private $address2;
    private $city;
    private $state;
    private $postalCode;
    private $country;
    private $typeCode;
    private $typeDescription;
    private $startDate;
    private $endDate;
    private $altitude;
    private $course;
    private $host;
    private $hostAddress1;
    private $hostAddress2;
    private $hostPhone;
    private $teams = [];
    private $swimmers = [];
    private $events = [];
    
    public function getName(): string {
        
        if (empty($this->name)) return 'N/A';
        return $this->name;
    }
    
    public function setName(string $value) {
        $this->name = utf8_encode($value);
    }
    
    public function getAddress1(): string {
        return $this->address1;
    }
    
    public function setAddress1(string $value) {
        $this->address1 = utf8_encode($value);
    }
    
    public function getAddress2(): string {
        return $this->address2;
    }
    
    public function setAddress2(string $value) {
        $this->address2 = utf8_encode($value);
    }
    
    public function getCity(): string {
        if (empty($this->city)) return 'N/A';
        return $this->city;
    }
    
    public function setCity(string $value) {
        $this->city = utf8_encode($value);
    }
    
    public function getState(): string {
        if (empty($this->state)) return 'N/A';
        return $this->state;
    }
    
    public function setState(string $value) {
        $this->state = $value;
    }
    
    public function getPostalCode(): string {
        return $this->postalCode;
    }
    
    public function setPostalCode(string $value) {
        $this->postalCode = $value;
    }
    
    public function getCountry(): string {
        if (empty($this->country)) return 'N/A';
        return $this->country;
    }
    
    public function setCountry(string $value, string $defaultCountry) {
        
        if (empty($value)) {
            $this->country = strtoupper($defaultCountry);
        } else {
            $this->country = strtoupper($value);
        }
    }
    
    public function getTypeCode(): string {
        return $this->typeCode;
    }
    
    public function setTypeCode(string $value) {
        $this->typeCode = $value;
        $this->setTypeDescription($value);
    }
    
    public function getTypeDescription(): string {
        return $this->typeDescription;
    }
    
    public function setTypeDescription(string $value) {
        switch ($value) {
            case '1':
                $this->typeDescription = 'Invitational';
                break;
            case '2':
                $this->typeDescription = 'Regional';
                break;
            case '3':
                $this->typeDescription = 'LSC Championship';
                break;
            case '4':
                $this->typeDescription = 'Zone';
                break;
            case '5':
                $this->typeDescription = 'Zone Championship ';
                break;
            case '6':
                $this->typeDescription = 'National Championship';
                break;
            case '7':
                $this->typeDescription = 'Juniors';
                break;
            case '8':
                $this->typeDescription = 'Seniors';
                break;
            case '9':
                $this->typeDescription = 'Dual';
                break;
            case '0':
                $this->typeDescription = 'Time Trials';
                break;
            case 'A':
                $this->typeDescription = 'International';
                break;
            case 'B':
                $this->typeDescription = 'Open';
                break;
            case 'C':
                $this->typeDescription = 'League';
                break;
            default:
                $this->typeDescription = 'Unknown';
                break;
                
        }
    }
    
    public function getStartDate(string $format = 'Y-m-d'): string {
        
        // Some meet files have no start date
        if (empty($this->startDate)) {
            return 'N/A';
        } else {
            return date_format($this->startDate, $format);
        }
        
    }
    
    public function setStartDate(string $value) {
        $this->startDate = DateTime::createFromFormat('mdY', $value);
    }
    
    public function getEndDate(string $format = 'Y-m-d'): string {
        // Some meet files have no end date
        if (empty($this->startDate)) {
            return 'N/A';
        } else {
            return date_format($this->endDate, $format);
        }
    }
    
    public function setEndDate(string $value) {
        $this->endDate = DateTime::createFromFormat('mdY', $value);
        
        //When a start date does not exist, using end date as start date
        if ($this->startDate === false) {
            $this->startDate = $this->endDate;
        }
    }
    
    /**
     * 
     * @param string $unit (m = meters or ft)
     * @return float
     */
    public function getAltitude(string $unit = 'm'): float {
        if ($unit == 'ft') return $this->altitude * 3.2808;
        return $this->altitude;
    }
    
    /**
     * 
     * @param int $value (in feet above sea level)
     */
    public function setAltitude(string $value) {
        $this->altitude = intval($value) / 3.2808;
    }
    
    public function getCourse(): string {
        return $this->course;
    }
    
    public function setCourse(string $value) {
        
        switch ($value) {
            case '1':
                $this->course = 'SCM';
                break;
            case 'S':
                $this->course = 'SCM';
                break;
            case '2':
                $this->course = 'SCY';
                break;
            case 'Y':
                $this->course = 'SCY';
                break;
            case '3':
                $this->course = 'LCM';
                break;
            case 'L':
                $this->course = 'LCM';
                break;
            case 'X':
                $this->course = 'Disqualified';
                break;
            default:
                $this->course = 'Unknown';
                break;
        }
    }
    
    public function getHost(): string {
        return $this->host;
    }
    
    public function setHost(string $value) {
        $this->host = utf8_encode($value);
    }
    
    public function getHostAddress1(): string {
        return $this->hostAddress1;
    }
    
    public function setHostAddress1(string $value) {
        $this->hostAddress1 = utf8_encode($value);
    }
    
    public function getHostAddress2(): string {
        return $this->hostAddress2;
    }
    
    public function setHostAddress2(string $value) {
        $this->hostAddress2 = utf8_encode($value);
    }
    
    public function getHostPhone(): string {
        return $this->hostPhone;
    }
    
    public function setHostPhone(string $value) {
        $this->hostPhone = $value;
    }
    
    public function getTeams(): array {
        return $this->teams;
    }
    
    public function getTeamCount(): int {
        
        
        return count(array_keys($this->teams));
    }

    //Should no longer be used
    /*public function setTeams(Team $value) {
        $country = $value->getCountry();
        $code = $value->getCode();
        $this->teams[$country][$code] = $value;
    }*/
    
    public function getSwimmers(): array {
        return count(array_keys($this->swimmers));
    }
    
    public function setSwimmers(Swimmer $value) {
        $key = $value->getId();
        $this->swimmers[$key] = $value;
    }
    
    public function getSwimmerCount(): int {
        return count($this->swimmers);
    }
    /*
    public function getEventByNumber(string $number): Event {
        if (isset($this->events[$number])) {
            return $this->events[$number];
        } else {
            $newEvent = new Event();
            $newEvent->setNumber($number);
            return $newEvent;
        }
    }*/
    
    public function getOrCreateEventBy(string $distance, string $stroke, string $course): Event {

        $key = sprintf('%s %s (%s)', $distance, $this->decodeStroke($stroke), $this->decodeCourse($course));

        if (!empty($this->events[$key])) {
            return $this->events[$key];
        } else {
            $newEvent = new Event();
            $newEvent->setDistance($distance);
            $newEvent->setStroke($stroke);
            $newEvent->setCourse($course);
            
            //Saving new event in meet
            $this->setEvents($newEvent);
            return $newEvent;
        }
    }
    
    
    public function getEventByName(string $name): Event {
        
        if (!empty($this->events[$name])) {
            return $this->events[$name];
        } else {
            throw new \Exception('There is no event for key: '. $name);
        }
    }
    
    
    public function getEvents(): array {
        return $this->events;
    }
    
    public function getEventCount(): int {
        return count($this->events);
    }
    
    public function setEvents(Event $value) {
        
        //array_splice($this->events, $value->getNumber(), 0, [$value]);
        $key = $value->getName();
        
        $this->events[$key] = $value;
    }
    
    public function appendTimeEntry(TimeEntry $entry) {
        $event = $this->getEventByName($entry->getEventName());
        $event->setTimeEntries($entry);
        
        //Is PHP pointing to original object or creating a new variable?
    }
    
    public function addTeam(Team $team, string $defaultCountry) {
        
        if (empty($team->getCountry())) {
            $country = $defaultCountry;
        } else {
            $country = $team->getCountry();
        }
        
        $code = $team->getCode();
        $this->teams[$country][$code] = $team;
    }
    
    
    public function jsonSerialize() {
        return [
            'name' => $this->name,
            'address1' => $this->address1,
            'address2' => $this->address2,
            'city' => $this->city,
            'state' => $this->state,
            'postalCode' => $this->postalCode,
            'country' => $this->country,
            'typeCode' => $this->typeCode,
            'typeDescription' => $this->typeDescription,
            'startDate' => $this->getStartDate(),
            'endDate' => $this->getEndDate(),
            'altitude' => $this->altitude,
            'course' => $this->course,
            'host' => $this->host,
            'hostAddress1' => $this->hostAddress1,
            'hostAddress2' => $this->hostAddress2,
            'hostPhone' => $this->hostPhone,
            'teams' => $this->teams,
            'events' => $this->events
        ];
    }
}