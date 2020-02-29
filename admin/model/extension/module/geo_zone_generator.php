<?php
class ModelExtensionModuleGeoZoneGenerator extends Model {
    private $countries = array(
        'HU' => array(
            'name' => 'Hungary',
            'iso_code_2' => 'HU',
            'iso_code_3' => 'HUN',
            'zones' => array(
                1 => array(
                    'name' => 'Baranya',
                    'code' => 'HU-BA',
                    'status' => 1
                ),
                2 => array(
                    'name' => 'Bács-Kiskun',
                    'code' => 'HU-BK',
                    'status' => 1
                ),
                3 => array(
                    'name' => 'Békés',
                    'code' => 'HU-BE',
                    'status' => 1
                ),
                4 => array(
                    'name' => 'Borsod-Abaúj-Zemplén',
                    'code' => 'HU-BZ',
                    'status' => 1
                ),
                5 => array(
                    'name' => 'Budapest',
                    'code' => 'HU-BU',
                    'status' => 1
                ),
                6 => array(
                    'name' => 'Csongrád',
                    'code' => 'HU-CS',
                    'status' => 1
                ),
                7 => array(
                    'name' => 'Fejér',
                    'code' => 'HU-FE',
                    'status' => 1
                ), 
                8 => array(
                    'name' => 'Győr-Moson-Sopron',
                    'code' => 'HU-GS',
                    'status' => 1
                ),
                9 => array(
                    'name' => 'Hajdú-Bihar',
                    'code' => 'HU-HB',
                    'status' => 1
                ),
                10 => array(
                    'name' => 'Heves',
                    'code' => 'HU-HE',
                    'status' => 1
                ),
                11 => array(
                    'name' => 'Jász-Nagykun-Szolnok',
                    'code' => 'HU-JN',
                    'status' => 1
                ),
                12 => array(
                    'name' => 'Komárom-Esztergom',
                    'code' => 'HU-KE',
                    'status' => 1
                ),
                13 => array(
                    'name' => 'Nógrád',
                    'code' => 'HU-NO',
                    'status' => 1
                ),
                14 => array(
                    'name' => 'Pest',
                    'code' => 'HU-PE',
                    'status' => 1
                ),
                15 => array(
                    'name' => 'Somogy',
                    'code' => 'HU-SO',
                    'status' => 1
                ),
                16 => array(
                    'name' => 'Szabolcs-Szatmár-Bereg',
                    'code' => 'HU-SZ',
                    'status' => 1
                ),
                17 => array(
                    'name' => 'Tolna',
                    'code' => 'HU-TO',
                    'status' => 1
                ),
                18 => array(
                    'name' => 'Vas',
                    'code' => 'HU-VA',
                    'status' => 1
                ),
                19 => array(
                    'name' => 'Veszprém',
                    'code' => 'HU-VE',
                    'status' => 1
                ),
                20 => array(
                    'name' => 'Zala',
                    'code' => 'HU-ZA',
                    'status' => 1
                ),
            )
        ),
        'RD' => array(
            'name' => 'Redania',
            'iso_code_2' => 'RD',
            'iso_code_3' => 'RDN',
            'zones' => array(
                1 => array(
                    'name' => 'Novigrad',
                    'code' => 'NOVIGRAD',
                    'status' => 0
                ),
                2 => array(
                    'name' => 'Oxenfurt',
                    'code' => 'OXENFURT',
                    'status' => 0 
                ),
                3 => array(
                    'name' => 'Rinde',
                    'code' => 'RINDE',
                    'status' => 0 
                ),
                4 => array(
                    'name' => 'Tretogor',
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