<?php

namespace SdifParser\Model;

use DateTime;
use JsonSerializable;

class Swimmer implements JsonSerializable
{
    
    private $name;
    private $firstName;
    private $lastName;
    private $ussNo;
    private $attachCode;
    private $citizenCode;
    private $dob;
    private $age;
    private $gender;
    private $teamCode;
    
    public function getName(): string {
        return $this->name;
    }
    
    public function setName(string $value) {
        $this->name = utf8_encode($value);
        
        $parts = explode(',', $this->name);
        $this->firstName = trim($parts[1]);
        $this->lastName = trim($parts[0]);
        
    }
    
    public function getFirstName(): string {
        return $this->firstName;
    }
    
    public function getLastName(): string {
        return $this->lastName;
    }

    public function getUssNo(): string {
        return $this->ussNo;
    }
    
    public function setUssNo(string $value) {
        $this->ussNo = $value;
    }
    
    public function getAttachCode(): string {
        return $this->attachCode;
    }
    
    public function setAttachCode(string $value) {
        $this->attachCode = $value;
    }
    
    public function getCitizenCode(): string {
        return $this->citizenCode;
    }
    
    public function setCitizenCode(string $value) {
        $this->citizenCode = $value;
    }
    
    public function getDob(string $format = 'Y-m-d'): string {
        
        if (!empty($this->dob)) {
            return date_format($this->dob, $format);
        } else {
            return 'N/A';
        }
    }
    
    public function setDob(string $value) {
        
        //Some records have no DOB - only an age
        if (isset($value)) $this->dob = DateTime::createFromFormat('mdY', $value);
    }
    
    public function getAge(): string {
        return $this->age;
    }
    
    public function setAge(string $value) {
        $this->age = $value;
    }
    
    public function getGender(): string {
        return $this->gender;
    }
    
    public function setGender(string $value) {
        $this->gender = $value;
    }
    
    public function getTeamCode(): string {
        return $this->teamCode;
    }
    
    public function setTeamCode(string $value) {
        $this->teamCode = $value;
    }
    
    public function getId(): string {
        return md5($this->name . $this->gender . $this->getDob() . $this->getTeamCode());
    }
    
    public function jsonSerialize() {
        return [
            'id' => $this->getId(),
            'name' => $this->name,
            'firstName' => $this->firstName,
            'lastName' => $this->lastName,
            'ussNo' => $this->ussNo,
            'attachCode' => $this->attachCode,
            'citizenCode' => $this->citizenCode,
            'dob' => $this->getDob(),
            'age' => $this->age,
            'gender' => $this->gender,
            'teamCode' => $this->teamCode
        ];
    }
}