<?php
class ModelExtensionModuleGeoZoneGenerator extends Model {
    private $countries = array(
        'HU' => array(
            'name' => 'Hungary',
            'iso_code_2' => 'HU',
            'iso_code_3' => 'HUN',
            'zones' => array(
                1 => array(
                    'name' => 'Test 1',
                    'iso_2' => 'TEST1',
                    'code' => 'TEST1',
                    'status' => 1
                ),
                2 => array(
                    'name' => 'Test 2',
                    'iso_2' => 'TEST2',
                    'code' => 'TEST2',
                    'status' => 1
                )
            )
        ),
        'RD' => array(
            'name' => 'Redania',
            'iso_code_2' => 'RD',
            'iso_code_3' => 'RDN',
            'zones' => array(
                1 => array(
                    'name' => 'Novigrad',
                    'iso_2' => '',
                    'code' => 'NOVIGRAD',
                    'status' => 0
                ),
                2 => array(
                    'name' => 'Oxenfurt',
                    'iso_2' => '',
                    'code' => 'OXENFURT',
                    'status' => 0 
                ),
                3 => array(
                    'name' => 'Rinde',
                    'iso_2' => '',
                    'code' => 'RINDE',
                    'status' => 0 
                ),
                4 => array(
                    'name' => 'Tretogor',
                    'iso_2' => '',
                    'code' => 'TRETOGOR',
                    'status' => 0 
                ),
            )
        )
    );

    /**
     * Add zones for a country by ISO2 code
     *
     * @param string $iso_code_2
     * @return array logged results
     */
    public function addZones($iso_code_2) {

        $log = array();

        $query = $this->db->query("SELECT country_id, name FROM " . DB_PREFIX . "country WHERE iso_code_2 ='" . $this->db->escape($iso_code_2) . "'");

        $country_id = 0;
        if(!empty($query->row['country_id'])) {
            $country_id = $query->row['country_id'];    
        }
        
        if($country_id) {
            $log[] = "Country with ISO2 code has beend found: " . $query->row['name'] . " (ID:" . $country_id . ").";

            $zones = $this->countries[$iso_code_2]['zones'];

            foreach($zones as $zone) {
                $zone_id = $this->db->query("INSERT INTO " . DB_PREFIX . "zone SET country_id ='" . (int)$country_id . "', name = '" . $this->db->escape($zone['name']) . "', code = '" . $this->db->escape($zone['code']) . "', status = '" . (int)$zone['status'] . "'");
                
                if($zone_id) {
                    $log[] = "Adding zone ID:" . $zone_id . " named " . $zone['name'] . " with code " . $zone['code'] . " for country ID " . $country_id . ".";
                } else {
                    $log[] = "Could not add zone named " . $zone['name'] . " with code " . $zone['code'] . " for country ID " . $country_id . ".";
                }

            }
        } else {
            $log[] = "Country with ISO2 code " . $iso_code_2 . " could not be found on your system.";
        }

        return $log;

    }


    /**
     * Get list of all countries with additional data available to install
     *
     * @return array
     */
    public function getCountries() {

        $countries = $this->countries;

        // Check for all pre-existing zones
        foreach($countries as &$country) {
            $country['zones_exist_in_oc'] = $this->checkForExistingZones($country['iso_code_2']);
        }

        return $countries;
    }


    /**
     * Check if there are any pre-existing zones in the system for the country code
     *
     * @param string $iso_2
     * @return bool
     */
    public function checkForExistingZones($iso_code_2) {

        $query = $this->db->query("SELECT z.zone_id FROM " .  DB_PREFIX . "country AS c INNER JOIN . " . DB_PREFIX . "zone AS z ON c.country_id = z.country_id WHERE c.iso_code_2 = '" . $this->db->escape($iso_code_2) . "' LIMIT 1");

        if(!empty($query->row)) {
            return true;
        }

        return false;
    }
    
}