<?php

namespace SdifParser\Model;

use DateTime;
use JsonSerializable;

/**
 * 
 * @author ebeaule
 *
 */
class File implements JsonSerializable
{

    private $path;
    private $type;
    private $orgCode;
    private $orgDescription;
    private $sdifVersion;
    private $code;
    private $description;
    private $software;
    private $softwareVersion;
    private $contact;
    private $contactPhone;
    private $date;
    private $meet;
    
    public function getPath(): string {
        return $this->path;
    }
    
    public function setPath(string $value) {
        $this->path = $value;
    }
    
    public function getType(): string {
        return $this->type;
    }
    
    public function setType(string $value) {
        $this->type = $value;
    }
    
    public function getOrgCode(): string {
        return $this->orgCode;
    }
    
    public function setOrgCode(string $value) {
        $this->orgCode = $value;
        $this->setOrgDescriptionFromCode($value);
    }
    
    public function getOrgDescription(): string {
        return $this->orgDescription;
    }
    
    private function setOrgDescriptionFromCode(string $value) {
        
        switch ($value) {
            case '1':
                $this->orgDescription = 'USS';
                break;
            case '2':
                $this->orgDescription = 'Masters';
                break;
            case '3':
                $this->orgDescription = 'NCAA';
                break;
            case '4':
                $this->orgDescription = 'NCAA Div I';
                break;
            case '5':
                $this->orgDescription = 'NCAA Div II';
                break;
            case '6':
                $this->orgDescription = 'NCAA Div III';
                break;
            case '7':
                $this->orgDescription = 'YMCA';
                break;
            case '8':
                $this->orgDescription = 'FINA';
                break;
            case '9':
                $this->orgDescription = 'High School';
                break;
            default:
                $this->orgDescription = 'Unknown';
                break;
        }
    }
    
    public function setOrgDescription(string $value) {
        $this->orgDescription = $value;
    }
    
    public function getSdifVersion(): string {
        return $this->sdifVersion;
    }
    
    public function setSdifVersion(string $value) {
        $this->sdifVersion = $value;
    }
    
    public function getCode(): int {
        return $this->code;
    }
    
    public function setCode(int $value) {
        $this->code = $value;
        $this->setDescriptionFromCode($value);
    }
    
    public function getDescription(): string {
        return $this->description;
    }
    
    private function setDescriptionFromCode(int $code) {
        
        switch ($code) {
            case 1:
                $this->description = 'Meet Registrations';
                break;
            case 2:
                $this->description = 'Meet Results';
                break;
            case 3:
                $this->description = 'OVC';
                break;
            case 4:
                $this->description = 'National Age Group Record';
                break;
            case 5:
                $this->description = 'LSC Age Group Record';
                break;
            case 6:
                $this->description = 'LSC Motivational List';
                break;
            case 7:
                $this->description = 'National Records and Rankings';
                break;
            case 8:
                $this->description = 'Team Selection';
                break;
            case 9:
                $this->description = 'LSC Best Times';
                break;
            case 10:
                $this->description = 'USS Registration';
                break;
            case 16:
                $this->description = 'Top 16';
                break;
            case 20:
                $this->description = 'Vendor-defined code';
                break;
            default:
                $this->description = 'Unknown';
                break;
        }
    }
    
    public function setDescription(string $value) {
        $this->description = $value;
    }
    
    public function getSoftware(): string {
        return $this->software;
    }
    
    public function setSoftware(string $value) {
        $this->software = utf8_encode($value);
    }
    
    public function getSoftwareVersion(): string {
        return $this->softwareVersion;
    }
    
    public function setSoftwareVersion(string $value) {
        $this->softwareVersion = $value;
    }
    
    public function getContact(): string {
        return $this->contact;
    }
    
    public function setContact(string $value) {
        $this->contact = utf8_encode($value);
    }
    
    public function getContactPhone(): string {
        return $this->contactPhone;
    }
    
    public function setContactPhone(string $value) {
        $this->contactPhone = $value;
    }
    
    public function getDate(string $format = 'Y-m-d'): string {
        return date_format($this->date, $format);
    }
    
    public function setDate(string $value) {
        $this->date = DateTime::createFromFormat('mdY', $value);
    }
    
    public function getMeet(): Meet {
        if (empty($this->meet)) {
            return new Meet();
        }
        return $this->meet;
    }
    
    public function setMeet(Meet $value) {
        $this->meet = $value;
    }
    
    public function getRecommendedFileName(): string {
        
        // [meet date] [meet name] [course] ([file date]-[E#]-[T#]-[S#].sd3)
        
        $newName = sprintf('%s %s %s (%s-E%d-T%d-S%d).%s', 
            $this->meet->getStartDate(), 
            $this->meet->getName(), 
            $this->meet->getCourse(), 
            $this->getDate(),
            $this->meet->getEventCount(), 
            $this->meet->getTeamCount(), 
            $this->meet->getSwimmerCount(),
            $this->type);
        //Removing non-friendly path characters
        return mb_ereg_replace("([^\w\s\d\-_~,;\[\]\(\).])", '', $newName);
        
    }
    
    /*
    public function get(): string {
        return $this->;
    }
    
    public function set(string $value) {
        $this-> = $value;
    }
     */
    
    public function jsonSerialize() {
        return [
            'path' => $this->path,
            'orgCode' => $this->orgCode,
            'orgDesription' => $this->orgDescription,
            'sdifVersion' => $this->sdifVersion,
            'code' => $this->code,
            'description' => $this->description,
            'software' => $this->software,
            'softwareVersion' => $this->softwareVersion,
            'contact' => $this->contact,
            'contactPhone' => $this->contactPhone,
            'date' => $this->getDate(),
            'meet' => $this->meet
        ];
    }
}