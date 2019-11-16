<?php

namespace SdifParser\Model;

use JsonSerializable;

class Team implements JsonSerializable
{
    
    private $code;
    private $name;
    private $shortName;
    private $address1;
    private $address2;
    private $city;
    private $state;
    private $postalCode;
    private $country;
    private $region;
    private $swimmers = [];
    
    public function getCode(): string {
        return $this->code;
    }
    
    public function setCode(string $value) {
        
        if (!empty($value)) {
            $this->code = $value;
        } else {
            //Creating a code when it is absent - using 2 characters from all parts of a team name
            if(preg_match_all('/\b(\w\w)/', strtoupper($this->name), $m)) {
                $this->code = substr(implode('', $m[1]), 0, 6);
            }
        }
        
    }
    
    public function getName(): string {
        return $this->name;
    }
    
    public function setName(string $value) {
        $this->name = utf8_encode($value);
    }
    
    public function getShortName(): string {
        return $this->shortName;
    }
    
    public function setShortName(string $value) {
        $this->shortName = utf8_encode($value);
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
        return $this->city;
    }
    
    public function setCity(string $value) {
        $this->city = utf8_encode($value);
    }
    
    public function getState(): string {
        return $this->state;
    }
    
    public function setState(string $value) {
        $this->state = utf8_encode($value);
    }
    
    public function getPostalCode(): string {
        return $this->postalCode;
    }
    
    public function setPostalCode(string $value) {
        $this->postalCode = $value;
    }
    
    public function getCountry(): string {
        return "$this->country";
    }
    
    public function setCountry(string $value, string $defaultCountry) {
        
        if (empty($value)) {
            $this->country = $defaultCountry;
        } else {
            $this->country = $value;
        }
    }
    
    public function getRegion(): string {
        return $this->region;
    }
    
    public function setRegion(string $value) {
        $this->region = utf8_encode($value);
    }
    
    public function getSwimmers(): array {
        return $this->swimmers;
    }
    
    public function setSwimmers(Swimmer $value) {
        $key = $value->getId();
        $this->swimmers[$key] = $value;
    }
    
    public function jsonSerialize() {
        return [
            'code' => $this->code,
            'name' => $this->name,
            'shortName' => $this->shortName,
            'address1' => $this->address1,
            'address2' => $this->address2,
            'city' => $this->city,
            'state' => $this->state,
            'postalCode' => $this->postalCode,
            'country' => $this->country,
            'region' => $this->region,
            'swimmers' => $this->swimmers
        ];
    }
}