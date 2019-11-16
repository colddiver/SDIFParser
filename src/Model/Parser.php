<?php

namespace SdifParser\Model;

use DateTime;

/**
 * 
 * @author ebeaule
 *
 */
class Parser
{
    
    use Common;
    
    public function parseMeetFile(string $filePath, string $defaultCountry) {
        
        $parsedFile = new File();
        $parsedFile->setPath($filePath);
        $parsedFile->setType('txt');
        
        $dataFile = file_get_contents($filePath);
        $rows = explode("\n", $dataFile);
        
        
        //FIXME - detect file type - hy3 or sd3
        $code = substr($rows[0], 0, 2);
        $date = DateTime::createFromFormat('mdY', $this->extract($rows[0], 106, 8));
        
        print var_dump($date);
        
        if ($code === 'A0' && $date != false) {
            $parsedFile->setType('sd3');
        } else {
            $date = DateTime::createFromFormat('mdY', $this->extract($rows[0], 59, 8));
            if ($code === 'A1' && $date != false) $parsedFile->setType('hy3');
        }
        
        //Some files are missing the file terminator Z0 code so we are adding it if it is missing
        $lastRow = end($rows);
        if (substr($lastRow, 0, 2) !== 'Z0') {
            $rows[] = 'Z0';
        }

        $r = 1;
        
        //SD3 Parsing
        if ($parsedFile->getType() == 'sd3') {
            foreach ($rows as $data) {
                $code = substr($data, 0, 2);
                
                switch ($code) {
                    case 'A0':  //File
                        
                        try {
                            
                            $parsedFile->setOrgCode($this->extract($data, 3, 1));
                            $parsedFile->setSdifVersion($this->extract($data, 4, 8));
                            $parsedFile->setCode($this->extract($data, 12, 2));
                            $parsedFile->setSoftware($this->extract($data, 44, 20));
                            $parsedFile->setSoftwareVersion($this->extract($data, 64, 10));
                            $parsedFile->setContact($this->extract($data, 74, 20));
                            $parsedFile->setContactPhone($this->extract($data, 94, 12));
                            $parsedFile->setDate($this->extract($data, 106, 8));
                            
                        } catch (\Exception $e) {
                            print sprintf("ERROR\t File %s - Line: %d\n", $filePath, $r);
                            print 'Caught exception: '.  $e->getMessage() . "\n";
                            print var_dump($parsedFile);
                        }
                        
                        if ($parsedFile->getDescription() === 'Unknown' || $parsedFile->getOrgDescription() === 'Unknown') {
                            throw new \Exception('Unknown file type: this may not be an SDIF file!');
                        }
                        break;
                        
                    case 'B1':  //Meet
                        $meet = new Meet();
                        
                        try {
                            
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
                            
                        } catch (\Exception $e) {
                            print sprintf("ERROR\t File %s - Line: %d\n", $filePath, $r);
                            print 'Caught exception: '.  $e->getMessage() . "\n";
                            print var_dump($meet);
                        }
                        
                        break;
                        
                    case 'B2': //Meet Host
                        try {
                            
                            $meet->setHost($this->extract($data, 12, 30));
                            $meet->setHostAddress1($this->extract($data, 42, 22));
                            $meet->setHostAddress2($this->extract($data, 64, 22));
                            $meet->setHostPhone($this->extract($data, 121, 12));
                            
                        } catch (\Exception $e) {
                            print sprintf("ERROR\t File %s - Line: %d\n", $filePath, $r);
                            print 'Caught exception: '.  $e->getMessage() . "\n";
                            print var_dump($meet);
                        }
                        
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
                        
                        try {
                            
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
                            
                        } catch (\Exception $e) {
                            print sprintf("ERROR\t File %s - Line: %d\n", $filePath, $r);
                            print 'Caught exception: '.  $e->getMessage() . "\n";
                            print var_dump($team);
                        }
                        
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
                        
                        try {
                            
                            $swimmer->setName($this->extract($data, 12, 28));
                            $swimmer->setUssNo($this->extract($data, 40, 12));
                            $swimmer->setAttachCode($this->extract($data, 52, 1));
                            $swimmer->setCitizenCode($this->extract($data, 53, 3));
                            $swimmer->setDob($this->extract($data, 56, 8));
                            $swimmer->setAge($this->extract($data, 64, 2), $meet->getStartDate('Y'));
                            $swimmer->setGender($this->extract($data, 66, 1));
                            $swimmer->setTeamCode($team->getCode());

                        } catch (\Exception $e) {
                            print sprintf("ERROR\t File %s - Line: %d\n", $filePath, $r);
                            print 'Caught exception: '.  $e->getMessage() . "\n";
                            print var_dump($swimmer);
                        }
                        
                        
                        
                        $timeEntry = new TimeEntry();
                        
                        //Adding swimmer to team, meet & timeEntry
                        $team->setSwimmers($swimmer);
                        $meet->setSwimmers($swimmer);
                        $timeEntry->setSwimmers($swimmer);
                        
                        //Grabbing existing event so we can append to it OR getting a new event
                        $distance = $this->extract($data, 68, 4);
                        $stroke = $this->extract($data, 72, 1);
                        $course = $this->extract($data, 124, 1);
                        $event = $meet->getOrCreateEventBy($distance, $stroke, $course);
                        
                        $event->setNumber($this->extract($data, 73, 4));
                        $event->setDate($this->extract($data, 81, 8));
                        
                        try {
                            
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
                            
                        } catch (\Exception $e) {
                            print sprintf("ERROR\t File %s - Line: %d\n", $filePath, $r);
                            print 'Caught exception: '.  $e->getMessage() . "\n";
                            print var_dump($timeEntry);
                        }
                        
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
                        
                        try {
                            
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
                            
                        } catch (\Exception $e) {
                            print sprintf("ERROR\t File %s - Line: %d\n", $filePath, $r);
                            print 'Caught exception: '.  $e->getMessage() . "\n";
                            print var_dump($timeEntry);
                        }
                        
                        break;
                        
                    case 'F0':  //Relay Swimmers
                        $swimmer = new Swimmer();
                        
                        try {
                            
                            $swimmer->setName($this->extract($data, 23, 28));
                            $swimmer->setUssNo($this->extract($data, 51, 12));
                            $swimmer->setCitizenCode($this->extract($data, 63, 3));
                            $swimmer->setDob($this->extract($data, 66, 8));
                            $swimmer->setAge($this->extract($data, 74, 2), $meet->getStartDate('Y'));
                            $swimmer->setGender($this->extract($data, 76, 1));
                            $swimmer->setTeamCode($team->getCode());
                            
                        } catch (\Exception $e) {
                            print sprintf("ERROR\t File %s - Line: %d\n", $filePath, $r);
                            print 'Caught exception: '.  $e->getMessage() . "\n";
                            print var_dump($timeEntry);
                        }
                        
                        //Adding swimmer to team, meet & timeEntry
                        $team->setSwimmers($swimmer);
                        $meet->setSwimmers($swimmer);
                        $timeEntry->setSwimmers($swimmer);
                        break;
                        
                    case 'G0': //Splits
                        
                        try {
                            
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
                                    $timeEntry->setSplits($split);
                                }
                            }
                            
                        } catch (\Exception $e) {
                            print sprintf("ERROR\t File %s - Line: %d\n", $filePath, $r);
                            print 'Caught exception: '.  $e->getMessage() . "\n";
                            print var_dump($timeEntry);
                        }
                        
                        break;
                        
                    case 'Z0':  //File terminator
                        //File terminator is sometimes absent so we can't rely on that
                        $parsedFile->setMeet($meet);
                        break;
                }
                $r++;
            }
        }
        
        //HY-TEK Parsing
        if ($parsedFile->getType() == 'hy3') {
            foreach ($rows as $data) {
                $code = substr($data, 0, 2);
                
                switch ($code) {
                    case 'A1':  //File
                        
                        try {
                            //$parsedFile = $this->parseA0($data, $parsedFile);
                            
                            $parsedFile->setDescription($this->extract($data, 5, 25));
                            $parsedFile->setSoftware($this->extract($data, 30, 15));
                            $parsedFile->setSoftwareVersion($this->extract($data, 45, 10));
                            $parsedFile->setDate($this->extract($data, 59, 8));
                            
                            $parsedFile->setOrgDescription($this->extract($data, 84, 8));
                        } catch (\Exception $e) {
                            print sprintf("ERROR\t File %s - Line: %d\n", $filePath, $r);
                            print 'Caught exception: '.  $e->getMessage() . "\n";
                            print var_dump($parsedFile);
                        }
                        
                        if ($parsedFile->getDescription() === 'Unknown' || $parsedFile->getOrgDescription() === 'Unknown') {
                            throw new \Exception('Unknown file type: this may not be a valid Hy-Tek file!');
                        }
                        
                        if (strpos($parsedFile->getDescription(), 'Result') === false) {
                            throw new \Exception('This file does not contain meet results. Parsing stopped.');
                        }
                        break;
                        
                    case 'B1':  //Meet
                        $meet = new Meet();
                        
                        try {
                            //$meet = $this->parseB1($data, $meet, $defaultCountry);
                            
                            $meet->setName($this->extract($data, 3, 45));
                            $meet->setCity($this->extract($data, 48, 45));
                            $meet->setStartDate($this->extract($data, 93, 8));
                            $meet->setEndDate($this->extract($data, 101, 8));
                            $meet->setAltitude($this->extract($data, 117, 5));
                            
                            //FIXME - No idea where to extract country so relying on defaultCountrya
                            $meet->setCountry('', $defaultCountry);

                        } catch (\Exception $e) {
                            print sprintf("ERROR\t File %s - Line: %d\n", $filePath, $r);
                            print 'Caught exception: '.  $e->getMessage() . "\n";
                            print var_dump($meet);
                        }
                        
                        break;
                        
                    case 'B2': //Meet Additinoal Info
                        //$meet = $this->parseB2($data, $meet);
                        
                        $meet->setTypeCode($this->extract($data, 97, 2));
                        $meet->setCourse($this->extract($data, 99, 1));
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
                        
                        try {
                            //$team = $this->parseC1($data, $team, $defaultCountry);
                            
                            $team->setName($this->extract($data, 8, 30));
                            $team->setShortName($this->extract($data, 38, 16));
                            
                            //Doing this one last so we can create a code from team name above info when it is missing
                            $team->setCode($this->extract($data, 3, 5));
                            
                        } catch (\Exception $e) {
                            print sprintf("ERROR\t File %s - Line: %d\n", $filePath, $r);
                            print 'Caught exception: '.  $e->getMessage() . "\n";
                            print var_dump($team);
                        }
                        
                        break;
                        
                    case 'C2':  //Team Entry

                        try {
                            $team->setAddress1($this->extract($data, 33, 30));
                            $team->setCity($this->extract($data, 63, 30));
                            $team->setState($this->extract($data, 93, 2));
                            $team->setPostalCode($this->extract($data, 95, 10));
                            $team->setCountry($this->extract($data, 105, 3), $defaultCountry);
                            $team->setRegion($this->extract($data, 143, 1));
                            
                        } catch (\Exception $e) {
                            print sprintf("ERROR\t File %s - Line: %d\n", $filePath, $r);
                            print 'Caught exception: '.  $e->getMessage() . "\n";
                            print var_dump($team);
                        }
                        
                        break;
                        
                    case 'D1':  //Swimmer Entry
                        
                        try {
                            $swimmer = new Swimmer();
                            
                            $swimmer->setGender($this->extract($data, 3, 1));
                            $swimmer->setLastName($this->extract($data, 9, 20));
                            $swimmer->setFirstName($this->extract($data, 29, 20));
                            $swimmer->setDob($this->extract($data, 56, 8));
                            $swimmer->setAge($this->extract($data, 98, 2), $meet->getStartDate('Y'));

                            //$swimmer->setUssNo($this->extract($data, 40, 12));
                            //$swimmer->setAttachCode($this->extract($data, 52, 1));
                            //$swimmer->setCitizenCode($this->extract($data, 53, 3));
                            //
                            
                            
                            
                            //$swimmer->setTeamCode($teamCode);
                            $swimmer->setTeamCode($team->getCode());
                            
                            //Adding swimmer to team, meet & timeEntry
                            $team->setSwimmers($swimmer);
                            $meet->setSwimmers($swimmer);
                            
                        } catch (\Exception $e) {
                            print sprintf("ERROR\t File %s - Line: %d\n", $filePath, $r);
                            print 'Caught exception: '.  $e->getMessage() . "\n";
                            print var_dump($team);
                        }
                        
                        break;
                        
                    case 'E1': //Individual Event
                        /*
                         * Adding last $timeEntry before creating a new one since it is only at that point,
                         * that $timeEntry would be fully populated with all the splits.
                         */
                        if (isset($timeEntry)) {
                            $meet->appendTimeEntry($timeEntry);
                        }
                        
                        $timeEntry = new TimeEntry();
                        
                        //Adding swimmer to timeEntry
                        $timeEntry->setSwimmers($swimmer);
                        
                        //Grabbing existing event so we can append to it OR getting a new event
                        $distance = $this->extract($data, 18, 4);
                        $stroke = $this->extract($data, 22, 1);
                        $course = $this->extract($data, 51, 1);     //FIXME - not sure if this is the right one
                        $event = $meet->getOrCreateEventBy($distance, $stroke, $course);
                        
                        $event->setNumber($this->extract($data, 39, 3));
                        
                        try {
                            
                            $timeEntry->setEventName($event->getName());
                            $timeEntry->setGender($this->extract($data, 3, 1));
                            $timeEntry->setAgeGroupFromSwimmerAge($swimmer->getAge());   //Need to infer from age since for most hy3 files, is it set at [  0][109] = Open/Senior
                            $timeEntry->setSeedTime($this->extract($data, 89, 8));
                            $timeEntry->setSeedCourse($this->extract($data, 97, 1));
                            
                        } catch (\Exception $e) {
                            print sprintf("ERROR\t File %s - Line: %d\n", $filePath, $r);
                            print 'Caught exception: '.  $e->getMessage() . "\n";
                            print var_dump($timeEntry);
                        }
                        
                        break;
                        
                    case 'E2':
                        $event->setDate($this->extract($data, 88, 8));
                        $timeEntry->setDate($this->extract($data, 88, 8));
                        
                        /*$timeEntry->setPrelimTime($this->extract($data, 98, 8));
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
                         */
                        
                        
                        break;
                        
/*                    case 'E0':  //Relay Event
                        /*
                         * Adding last $timeEntry before creating a new one since it is only at that point,
                         * that $timeEntry would be fully populated with all the splits.
                         
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
                        
                        try {
                            //$timeEntry = $this->parseE0TimeEntry($data, $timeEntry, $event->getName());
                        } catch (\Exception $e) {
                            print sprintf("ERROR\t File %s - Line: %d\n", $filePath, $r);
                            print 'Caught exception: '.  $e->getMessage() . "\n";
                            print var_dump($timeEntry);
                        }
                        
                        break;
                        
                    case 'F0':  //Relay Swimmers
                        $swimmer = new Swimmer();
                        
                        try {
                            $swimmer = $this->parseF0Swimmer($data, $swimmer, $team->getCode(), $meet->getStartDate('Y'));
                        } catch (\Exception $e) {
                            print sprintf("ERROR\t File %s - Line: %d\n", $filePath, $r);
                            print 'Caught exception: '.  $e->getMessage() . "\n";
                            print var_dump($timeEntry);
                        }
                        
                        //Adding swimmer to team, meet & timeEntry
                        $team->setSwimmers($swimmer);
                        $meet->setSwimmers($swimmer);
                        $timeEntry->setSwimmers($swimmer);
                        break;
                        
                    case 'G0': //Splits
                        
                        try {
                            $timeEntry = $this->parseG0Splits($data, $timeEntry, $swimmer);
                        } catch (\Exception $e) {
                            print sprintf("ERROR\t File %s - Line: %d\n", $filePath, $r);
                            print 'Caught exception: '.  $e->getMessage() . "\n";
                            print var_dump($timeEntry);
                        }
                        
                        break;
                        */
                        
                    case 'Z0':  //File terminator
                        
                        //Adding last data
                        $meet->addTeam($team, $defaultCountry);
                        
                        //$meet->appendTimeEntry($timeEntry);
                        
                        $parsedFile->setMeet($meet);
                        break;
                }
                $r++;
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