<?php

namespace SdifParser\Model;

/**
 * 
 * @author ebeaule
 *
 */
class Parser
{
    public function parse_file(string $filePath) {
        
        $parsedFile = new File();
        $parsedFile->setPath($filePath);
        
        $sd3 = file_get_contents($filePath);
        $rows = explode("\n", $sd3);

        foreach ($rows as $data) {
            $code = substr($data, 0, 2);
            
            switch ($code) {
                case 'A0':  //File
                    $parsedFile->setSdifVersion($this->extract($data, 4, 8));
                    $parsedFile->setCode($this->extract($data, 12, 2));
                    $parsedFile->setSoftware($this->extract($data, 44, 20));
                    $parsedFile->setSoftwareVersion($this->extract($data, 64, 10));
                    $parsedFile->setContact($this->extract($data, 74, 20));
                    $parsedFile->setContactPhone($this->extract($data, 94, 12));
                    $parsedFile->setDate($this->extract($data, 106, 8));
                    break;
                /*
                case 'B1':  //Meet
                    $meet['name'] = utf8_encode(trim(substr($data, 11, 30)));
                    $meet['city'] = utf8_encode(trim(substr($data, 85, 20)));
                    $meet['state'] = trim(substr($data, 106, 2));
                    $meet['country'] = trim(substr($data, 117, 3));
                    $meet['start'] = $this->extract_date(trim(substr($data, 121, 8)));
                    $meet['end'] = $this->extract_date(trim(substr($data, 129, 8)));
                    $meet['course'] = $this->decode_course(trim(substr($data, 149, 1)));
                    
                    //print $row + 1 . ' - ' . $meet['course'] . "\n";
                    break;
                case 'C1': //Club
                    $club['code'] = utf8_encode(trim(substr($data, 11, 6)));
                    $club['name'] = utf8_encode(trim(substr($data, 17, 30)));
                    $club['city'] = utf8_encode(trim(substr($data, 107, 20)));
                    $club['state'] = trim(substr($data, 127,2));
                    $club['country'] = trim(substr($data, 139,3));
                    
                    //If club code is absent, adding the country code instead
                    if (empty($club['code'])) $club['code'] = $club['country'];
                    
                    $clubs[] = $club;
                    //print $row + 1 . ' - ' . $club['state'] . "\n";
                    break;
                case 'D0': //Event
                    $event['swimmers'][0] = $this->parse_name(utf8_encode(trim(substr($data, 11, 28))));
                    $event['swimmers'][0]['dob'] = $this->extract_date(trim(substr($data, 55, 8)));
                    $event['swimmers'][0]['gender'] = trim(substr($data, 65, 1));
                    $event['swimmers'][0]['club'] = end($clubs);
                    $event['gender'] = trim(substr($data, 66, 1));
                    $event['distance'] = trim(substr($data, 67, 4));
                    $event['stroke'] = $this->decode_stroke(trim(substr($data, 71, 1)));
                    $event['age_group'] = $this->parse_age_group(trim(substr($data, 76, 4)));
                    $event['date'] = $this->extract_date(trim(substr($data, 80,8)));
                    
                    $event['time'] = trim(substr($data, 115, 8));   //Using finals time
                    $event['time_in_seconds'] = $this->parse_time($event['time']);
                    
                    $event['course'] = $this->decode_course(trim(substr($data, 123, 1)));   //Using course for finals time
                    
                    $event['place'] = trim(substr($data, 135, 3));  //Using finals place ranking
                    
                    $event['splits'] = [];  //Will be filled later
                    
                    $events[] = $event;
                    
                    //print $this->pretty_print_entry($event);
                    break;
                    
                case 'E0':  //Relay Event
                    //FIXME - create an array of clubs with code as key
                    $club_code = utf8_encode(trim(substr($data, 12, 6)));
                    
                    $event['swimmers'] = [];
                    
                    $event['gender'] = trim(substr($data, 20, 1));
                    $event['distance'] = trim(substr($data, 21, 4));
                    $event['stroke'] = $this->decode_stroke(trim(substr($data, 25, 1)));
                    $event['age_group'] = $this->parse_age_group(trim(substr($data, 30, 4)));
                    $event['date'] = $this->extract_date(trim(substr($data, 37,8)));
                    
                    $event['time'] = trim(substr($data, 72, 8));   //Using finals time
                    $event['time_in_seconds'] = $this->parse_time($event['time']);
                    
                    $event['course'] = $this->decode_course(trim(substr($data, 80, 1)));   //Using course for finals time
                    
                    $event['place'] = trim(substr($data, 92, 3));  //Using finals place ranking
                    
                    $event['splits'] = [];  //Will be filled later
                    
                    //print $this->pretty_print_entry($event);
                    print var_dump($event);
                    break;
                    
                case 'F0':  //Relay Swimmers
                    //FIXME - to be developed once an sd3 example is provided
                    //FIXME fetch the club
                    
                    
                    
                    break;
                case 'G0': //Splits
                    
                    $lastEventKey = array_key_last($events);
                    
                    $swimmer = $this->parse_name(utf8_encode(trim(substr($data, 15, 28))));
                    $sequence = trim(substr($data, 55, 1));    //sequence number to order multiple splits records for one athlete and one event
                    $split_count = trim(substr($data, 56, 2)); //total number of splits for this event
                    $distance = trim(substr($data, 58, 4));
                    $code = trim(substr($data, 62, 1)); //Splits can either be cumulative or interval
                    
                    $time_indices = [63, 71, 79, 87, 95, 103, 111, 119, 127, 135];
                    
                    $adj = ($sequence * 10) - 10;   //Index adjustment to count properly the split we are currently in
                    
                    foreach ($time_indices as $k => $i) {
                        
                        if ($sequence == 1 & $k == 0) {
                            $previous_cum_time = 0.0; //Need to initialize on first split we encounter
                        } else {
                            //For subsequent splits in the series, we grab the value of the last split on the event array
                            $lastSplitKey = array_key_last($events[$lastEventKey]['splits']);
                            $previous_cum_time = $events[$lastEventKey]['splits'][$lastSplitKey]['cumulative_time_in_seconds'];
                        }
                        
                        $split['swimmer'] = $swimmer;
                        $split['sequence'] = $sequence;
                        $split['split_count'] = $split_count;
                        
                        $split['distance'] = $distance * ($k + 1 + $adj);
                        $split['code'] = $code;
                        $split['time'] = trim(substr($data, $i, 8));
                        
                        switch ($code) {
                            case 'C':   //Cumulative Splits
                                $split['cumulative_time_in_seconds'] = $this->parse_time($split['time']);
                                $split['interval_time_in_seconds'] = $split['cumulative_time_in_seconds'] - $previous_cum_time;
                                break;
                            case 'I':   //Interval Splits
                                $split['interval_time_in_seconds'] = $this->parse_time($split['time']);
                                $split['cumulative_time_in_seconds'] = $previous_cum_time + $split['interval_time_in_seconds'];
                                break;
                        }
                        
                        //Adding to last event
                        if (!empty($split['time'])) {
                            $events[$lastEventKey]['splits'][] = $split;
                        }
                        
                    }
                    break;
                    
                    //print var_dump($split);
                  */
            }
        }
        /*
         foreach ($events as $e => $d) {
         print $this->pretty_print_entry($d);
         }
         */
        
        return $parsedFile;
    }
    
    private function extract(string $data, int $start, int $length) {
        return trim(substr($data, $start - 1, $length));
    }
}