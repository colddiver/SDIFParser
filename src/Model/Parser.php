<?php

namespace SdifParser\Model;

/**
 * 
 * @author ebeaule
 *
 */
class Parser
{
    
    use Common;
    
    public function parse_file(string $filePath, string $defaultCountry) {
        
        $parsedFile = new File();
        $parsedFile->setPath($filePath);
        
        $sd3 = file_get_contents($filePath);
        $rows = explode("\n", $sd3);
        
        //$timeEntry (TimeEntry::class);

        foreach ($rows as $data) {
            $code = substr($data, 0, 2);
            
            switch ($code) {
                case 'A0':  //File
                    $parsedFile->setOrgCode($this->extract($data, 3, 1));
                    $parsedFile->setSdifVersion($this->extract($data, 4, 8));
                    $parsedFile->setCode($this->extract($data, 12, 2));
                    $parsedFile->setSoftware($this->extract($data, 44, 20));
                    $parsedFile->setSoftwareVersion($this->extract($data, 64, 10));
                    $parsedFile->setContact($this->extract($data, 74, 20));
                    $parsedFile->setContactPhone($this->extract($data, 94, 12));
                    $parsedFile->setDate($this->extract($data, 106, 8));
                    break;
                
                case 'B1':  //Meet
                    $meet = new Meet();
                    
                    $meet->setName($this->extract($data, 12, 30));
                    $meet->setAddress1($this->extract($data, 42, 22));
                    $meet->setAddress2($this->extract($data, 64, 22));
                    $meet->setCity($this->extract($data, 86, 20));
                    $meet->setState($this->extract($data, 106, 2));
                    $meet->setPostalCode($this->extract($data, 108, 10));
                    $meet->setCountry($this->extract($data, 118, 3), $defaultCountry);
                    $meet->setTypeCode($this->extract($data, 121, 1));
                    $meet->setStartDate($this->extract($data, 122, 8));
                    $meet->setEndDate($this->extract($data, 130, 8));
                    $meet->setAltitude($this->extract($data, 138, 4));
                    $meet->setCourse($this->extract($data, 150, 1));
                    break;
                    
                case 'B2': //Meet Host
                    $meet->setHost($this->extract($data, 12, 30));
                    $meet->setHostAddress1($this->extract($data, 42, 22));
                    $meet->setHostAddress2($this->extract($data, 64, 22));
                    $meet->setHostPhone($this->extract($data, 121, 12));
                    
                    //$parsedFile->setMeet($meet);
                    break;
                    
                case 'C1': //Team ID
                    /*
                     * Adding last $team before creating a new one since it is only at that point,
                     * that $team would be fully populated with all its swimmers.
                     */
                    if (isset($team)) {
                         $meet->addTeam($team, $defaultCountry);
                     }
                    $team = new Team();
                    
                    $team->setName($this->extract($data, 18, 30));
                    $team->setShortName($this->extract($data, 48, 16));
                    $team->setAddress1($this->extract($data, 64, 22));
                    $team->setAddress2($this->extract($data, 86, 22));
                    $team->setCity($this->extract($data, 108, 20));
                    $team->setState($this->extract($data, 128, 2));
                    $team->setPostalCode($this->extract($data, 130, 10));
                    $team->setCountry($this->extract($data, 140, 3), $defaultCountry);
                    $team->setRegion($this->extract($data, 143, 1));
                    
                    //Doing this one last so we can create a code from team name above info when it is missing
                    $team->setCode($this->extract($data, 12, 6));
                    
                    break;
                    
                case 'C2':  //Team Entry
                    /*
                     * IGNORED
                     * 
                     * One per swimmer. A swimmer with multiple DO records will have one D3 record following
                     * his/her first D0 record. Contains additional information that is not included in
                     * pre version 3 SDI formats
                     */
                    
                    /*
                    $ussNum = $this->extract($data, 3, 14);
                    $preferredFirstName = $this->extract($data, 17, 15);
                    $ethnicityCode = $this->extract($data, 32, 2);
                    $juniorHighSchool = $this->extract($data, 34, 1);
                    $seniorHighSchool = $this->extract($data, 35, 1);
                    $ymca = $this->extract($data, 36, 1);
                    $college = $this->extract($data, 37, 1);
                    $summerSwimLeague = $this->extract($data, 38, 1);
                    $masters = $this->extract($data, 39, 1);
                    $disabled = $this->extract($data, 40, 1);
                    $waterPolo = $this->extract($data, 41, 1);
                    $none = $this->extract($data, 42, 1);
                    */
                    
                    break;
                    
                case 'D0': //Individual Event
                    /*
                     * Adding last $timeEntry before creating a new one since it is only at that point,
                     * that $timeEntry would be fully populated with all the splits.
                     */ 
                    if (isset($timeEntry)) {
                        $meet->appendTimeEntry($timeEntry);
                    }
                    
                    $swimmer = new Swimmer();
                    $timeEntry = new TimeEntry();
                    
                    //print 'D0 - Name: ' . $this->extract($data, 12, 28) . "\tDOB: " . $this->extract($data, 56, 8) . "\n";
                    $swimmer->setName($this->extract($data, 12, 28));
                    $swimmer->setUssNo($this->extract($data, 40, 12));
                    $swimmer->setAttachCode($this->extract($data, 52, 1));
                    $swimmer->setCitizenCode($this->extract($data, 53, 3));
                    $swimmer->setDob($this->extract($data, 56, 8));
                    $swimmer->setAge($this->extract($data, 64, 2));
                    $swimmer->setGender($this->extract($data, 66, 1));
                    
                    $swimmer->setTeamCode($team->getCode());
                    
                    //Adding swimmer to team, meet & timeEntry
                    $team->setSwimmers($swimmer);
                    $meet->setSwimmers($swimmer);
                    $timeEntry->setSwimmers($swimmer);
                    
                    //print 'D0 - ' . json_encode($swimmer) . "\n";
                    
                    //Grabbing existing event so we can append to it OR getting a new event
                    $distance = $this->extract($data, 68, 4);
                    $stroke = $this->extract($data, 72, 1);
                    $course = $this->extract($data, 124, 1);
                    $event = $meet->getOrCreateEventBy($distance, $stroke, $course);
                    
                    $event->setNumber($this->extract($data, 73, 4));
                    $event->setDate($this->extract($data, 81, 8));
                    
                    $timeEntry->setEventName($event->getName());
                    $timeEntry->setGender($this->extract($data, 67, 1));
                    $timeEntry->setAgeGroup($this->extract($data, 77, 4));
                    $timeEntry->setDate($this->extract($data, 81, 8));
                    $timeEntry->setSeedTime($this->extract($data, 89, 8));
                    $timeEntry->setSeedCourse($this->extract($data, 97, 1));
                    $timeEntry->setPrelimTime($this->extract($data, 98, 8));
                    $timeEntry->setPrelimCourse($this->extract($data, 106, 1));
                    $timeEntry->setSwimOffTime($this->extract($data, 107, 8));
                    $timeEntry->setSwimOffCourse($this->extract($data, 115, 1));
                    $timeEntry->setFinalsTime($this->extract($data, 116, 8));
                    $timeEntry->setFinalsCourse($this->extract($data, 124, 1));
                    $timeEntry->setPrelimHeat($this->extract($data, 125, 2));
                    $timeEntry->setPrelimLane($this->extract($data, 127, 2));
                    $timeEntry->setFinalsHeat($this->extract($data, 129, 2));
                    $timeEntry->setFinalsLane($this->extract($data, 131, 2));
                    $timeEntry->setPrelimPlace($this->extract($data, 133, 3));
                    $timeEntry->setFinalsPlace($this->extract($data, 136, 3));
                    $timeEntry->setPoints($this->extract($data, 139, 4));
                    $timeEntry->setTimeClassCode($this->extract($data, 143, 2));
                    $timeEntry->setFlightStatus($this->extract($data, 145, 1));

                    //$meet->setTeams($team);
                    
                    //print "\n" . $event->getName() . ' number: ' . $event->getNumber() . "\n";
                    
                    break;
                
                case 'E0':  //Relay Event
                    /*
                     * Adding last $timeEntry before creating a new one since it is only at that point,
                     * that $timeEntry would be fully populated with all the splits.
                     */
                     if (isset($timeEntry)) {
                         $meet->appendTimeEntry($timeEntry);
                     }
                     
                     $timeEntry = new TimeEntry();
                     
                     //Grabbing existing event so we can append to it OR getting a new event
                     $distance = $this->extract($data, 22, 4);
                     $stroke = $this->extract($data, 26, 1);
                     $course = $this->extract($data, 81, 1);
                     $event = $meet->getOrCreateEventBy($distance, $stroke, $course);
                     
                     $event->setNumber($this->extract($data, 27, 4));
                     $event->setDate($this->extract($data, 38, 8));
                     
                     $timeEntry->setEventName($event->getName());
                     $timeEntry->setGender($this->extract($data, 21, 1));
                     $timeEntry->setAgeGroup($this->extract($data, 31, 4));
                     
                     $timeEntry->setRelayTeamAge($this->extract($data, 35, 3));
                     
                     $timeEntry->setDate($this->extract($data, 38, 8));
                     $timeEntry->setSeedTime($this->extract($data, 46, 8));
                     
                     $timeEntry->setSeedCourse($this->extract($data, 54, 1));
                     $timeEntry->setPrelimTime($this->extract($data, 55, 8));
                     $timeEntry->setPrelimCourse($this->extract($data, 63, 1));
                     $timeEntry->setSwimOffTime($this->extract($data, 64, 8));
                     $timeEntry->setSwimOffCourse($this->extract($data, 72, 1));
                     $timeEntry->setFinalsTime($this->extract($data, 73, 8));
                     $timeEntry->setFinalsCourse($this->extract($data, 81, 1));
                     $timeEntry->setPrelimHeat($this->extract($data, 82, 2));
                     $timeEntry->setPrelimLane($this->extract($data, 84, 2));
                     $timeEntry->setFinalsHeat($this->extract($data, 86, 2));
                     $timeEntry->setFinalsLane($this->extract($data, 88, 2));
                     $timeEntry->setPrelimPlace($this->extract($data, 90, 3));
                     $timeEntry->setFinalsPlace($this->extract($data, 93, 3));
                     $timeEntry->setPoints($this->extract($data, 96, 4));
                     $timeEntry->setTimeClassCode($this->extract($data, 100, 2));
                     //$timeEntry->setFlightStatus($this->extract($data, 145, 1));      //Not used for relays
                     
                     //Temporary
                     //$event->setTimeEntries($timeEntry);
                     
                     //print json_encode($timeEntry) . "\n";
                     
                     break;

                case 'F0':  //Relay Swimmers
                    
                    //print 'F0 - Name: ' . $this->extract($data, 23, 28) . "\tDOB: " . $this->extract($data, 66, 8) . "\n";
                    
                    $swimmer = new Swimmer();
                    $swimmer->setName($this->extract($data, 23, 28));
                    $swimmer->setUssNo($this->extract($data, 51, 12));
                    //$swimmer->setAttachCode($this->extract($data, 52, 1));
                    $swimmer->setCitizenCode($this->extract($data, 63, 3));
                    $swimmer->setDob($this->extract($data, 66, 8));
                    $swimmer->setAge($this->extract($data, 74, 2));
                    $swimmer->setGender($this->extract($data, 76, 1));
                    
                    $swimmer->setTeamCode($team->getCode());
                    
                    //Adding swimmer to team, meet & timeEntry
                    $team->setSwimmers($swimmer);
                    $meet->setSwimmers($swimmer);
                    $timeEntry->setSwimmers($swimmer);

                    //print 'F0 - ' . json_encode($swimmer) . "\n";
                    
                    break;
                
                case 'G0': //Splits
                    
                    $sequence = $this->extract($data, 56, 1);   //sequence number to order multiple splits records for one athlete and one event
                    $distance = $this->extract($data, 59, 4);
                    $code = $this->extract($data, 63, 1);       //Splits can either be cumulative (C) or interval (I)
                    
                    $timeIndices = [
                        1 => 64,
                        2 => 72,
                        3 => 80,
                        4 => 88,
                        5 => 96,
                        6 => 104,
                        7 => 112,
                        8 => 120,
                        9 => 128,
                        10 => 136
                    ];
                    
                    //Index adjustment to count properly the split we are currently in
                    $adj = ($sequence * 10) - 10;
                    
                    foreach ($timeIndices as $i => $timeIndex) {
                        
                        $previousCumTime = $timeEntry->getLastSplitTime();
                        
                        $time = $this->extract($data, $timeIndex, 8);
                        
                        if (!empty($time)) {
                            $split = new Split();
                            $split->setDistance($distance * ($i + $adj));
                            $split->setSplitType($this->extract($data, 144, 1));
                            
                            $split->setSwimmer($swimmer);
                            
                            $timeRaw = $this->extract($data, $timeIndex, 8);
                            $split->setTimeCode($time);
                            
                            //FIXME - Need to ensure that this works for relays!
                            $split->setSwimmer($swimmer);
                            
                            $time = $this->parseTime($timeRaw);
                            
                            switch ($code) {
                                case 'C':   //Cumulative Splits
                                    $split->setCumulativeTime($time);
                                    $split->setIntervalTime($time - $previousCumTime);
                                    break;
                                case 'I':   //Interval Splits
                                    $split->setIntervalTime($time);
                                    $split->setCumulativeTime($previousCumTime + $time);
                                    break;
                            }
                            
                            //print json_encode($split) . "\n";
                            
                            $timeEntry->setSplits($split);
                        }
                        
                    }
                    //print json_encode($timeEntry, JSON_PRETTY_PRINT) . "\n\n";
                    break;
                case 'Z0':  //File terminator
                    $parsedFile->setMeet($meet);
                    break;
                  
            }
        }
        
        return $parsedFile;
    }
    
    /**
     * 
     * Function to enable extracting data using the start indices straight from the sd3 spec
     * 
     * @param string $data
     * @param int $start
     * @param int $length
     * @return string
     */
    private function extract(string $data, int $start, int $length) {
        return trim(substr($data, $start - 1, $length));
    }
}