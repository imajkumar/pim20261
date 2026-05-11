<?php
declare(strict_types=1);

namespace Ayra\Bundle\AyraBundle\DataObject;

/**
 * Indian states / UTs and representative cities (district capitals & major towns).
 * Extend {@see self::CITIES_BY_STATE} if you need more places per region.
 *
 * @internal
 */
final class IndiaGeoData
{
    /**
     * @return list<array{key: string, value: string}>
     */
    public static function stateOptions(): array
    {
        return self::STATES;
    }

    /**
     * @return list<array{key: string, value: string}>
     */
    public static function cityOptionsForState(?string $stateValue): array
    {
        if ($stateValue === null || $stateValue === '') {
            return [];
        }

        return self::CITIES_BY_STATE[$stateValue] ?? [];
    }

    /**
     * Whether a stored city **value** belongs to the given state **value** (both option slugs).
     */
    public static function isCityValueAllowedForState(string $cityValue, string $stateValue): bool
    {
        foreach (self::cityOptionsForState($stateValue) as $row) {
            if (($row['value'] ?? '') === $cityValue) {
                return true;
            }
        }

        return false;
    }

    /**
     * Default stored city **value** when none is selected or the current value does not belong to the state
     * (first entry in {@see self::CITIES_BY_STATE} for that state).
     */
    public static function firstCityValueForState(string $stateValue): ?string
    {
        $opts = self::cityOptionsForState($stateValue);
        if ($opts === []) {
            return null;
        }

        $v = $opts[0]['value'] ?? '';

        return $v !== '' ? (string) $v : null;
    }

    /** @var list<array{key: string, value: string}> */
    private const STATES = [
        ['key' => 'Andhra Pradesh', 'value' => 'andhra_pradesh'],
        ['key' => 'Arunachal Pradesh', 'value' => 'arunachal_pradesh'],
        ['key' => 'Assam', 'value' => 'assam'],
        ['key' => 'Bihar', 'value' => 'bihar'],
        ['key' => 'Chhattisgarh', 'value' => 'chhattisgarh'],
        ['key' => 'Goa', 'value' => 'goa'],
        ['key' => 'Gujarat', 'value' => 'gujarat'],
        ['key' => 'Haryana', 'value' => 'haryana'],
        ['key' => 'Himachal Pradesh', 'value' => 'himachal_pradesh'],
        ['key' => 'Jharkhand', 'value' => 'jharkhand'],
        ['key' => 'Karnataka', 'value' => 'karnataka'],
        ['key' => 'Kerala', 'value' => 'kerala'],
        ['key' => 'Madhya Pradesh', 'value' => 'madhya_pradesh'],
        ['key' => 'Maharashtra', 'value' => 'maharashtra'],
        ['key' => 'Manipur', 'value' => 'manipur'],
        ['key' => 'Meghalaya', 'value' => 'meghalaya'],
        ['key' => 'Mizoram', 'value' => 'mizoram'],
        ['key' => 'Nagaland', 'value' => 'nagaland'],
        ['key' => 'Odisha', 'value' => 'odisha'],
        ['key' => 'Punjab', 'value' => 'punjab'],
        ['key' => 'Rajasthan', 'value' => 'rajasthan'],
        ['key' => 'Sikkim', 'value' => 'sikkim'],
        ['key' => 'Tamil Nadu', 'value' => 'tamil_nadu'],
        ['key' => 'Telangana', 'value' => 'telangana'],
        ['key' => 'Tripura', 'value' => 'tripura'],
        ['key' => 'Uttar Pradesh', 'value' => 'uttar_pradesh'],
        ['key' => 'Uttarakhand', 'value' => 'uttarakhand'],
        ['key' => 'West Bengal', 'value' => 'west_bengal'],
        ['key' => 'Andaman and Nicobar Islands', 'value' => 'andaman_nicobar'],
        ['key' => 'Chandigarh', 'value' => 'chandigarh'],
        ['key' => 'Dadra and Nagar Haveli and Daman and Diu', 'value' => 'dadra_nagar_haveli_daman_diu'],
        ['key' => 'Delhi', 'value' => 'delhi'],
        ['key' => 'Jammu and Kashmir', 'value' => 'jammu_kashmir'],
        ['key' => 'Ladakh', 'value' => 'ladakh'],
        ['key' => 'Lakshadweep', 'value' => 'lakshadweep'],
        ['key' => 'Puducherry', 'value' => 'puducherry'],
    ];

    /**
     * @var array<string, list<array{key: string, value: string}>>
     */
    private const CITIES_BY_STATE = [
        'andhra_pradesh' => [
            ['key' => 'Visakhapatnam', 'value' => 'visakhapatnam'],
            ['key' => 'Vijayawada', 'value' => 'vijayawada'],
            ['key' => 'Guntur', 'value' => 'guntur'],
            ['key' => 'Nellore', 'value' => 'nellore'],
            ['key' => 'Tirupati', 'value' => 'tirupati'],
            ['key' => 'Kurnool', 'value' => 'kurnool'],
            ['key' => 'Rajahmundry', 'value' => 'rajahmundry'],
            ['key' => 'Kakinada', 'value' => 'kakinada'],
        ],
        'arunachal_pradesh' => [
            ['key' => 'Itanagar', 'value' => 'itanagar'],
            ['key' => 'Naharlagun', 'value' => 'naharlagun'],
            ['key' => 'Pasighat', 'value' => 'pasighat'],
            ['key' => 'Tawang', 'value' => 'tawang'],
            ['key' => 'Ziro', 'value' => 'ziro'],
        ],
        'assam' => [
            ['key' => 'Guwahati', 'value' => 'guwahati'],
            ['key' => 'Silchar', 'value' => 'silchar'],
            ['key' => 'Dibrugarh', 'value' => 'dibrugarh'],
            ['key' => 'Jorhat', 'value' => 'jorhat'],
            ['key' => 'Nagaon', 'value' => 'nagaon'],
            ['key' => 'Tezpur', 'value' => 'tezpur'],
            ['key' => 'Tinsukia', 'value' => 'tinsukia'],
        ],
        'bihar' => [
            ['key' => 'Patna', 'value' => 'patna'],
            ['key' => 'Gaya', 'value' => 'gaya'],
            ['key' => 'Bhagalpur', 'value' => 'bhagalpur'],
            ['key' => 'Muzaffarpur', 'value' => 'muzaffarpur'],
            ['key' => 'Purnia', 'value' => 'purnia'],
            ['key' => 'Darbhanga', 'value' => 'darbhanga'],
            ['key' => 'Begusarai', 'value' => 'begusarai'],
        ],
        'chhattisgarh' => [
            ['key' => 'Raipur', 'value' => 'raipur'],
            ['key' => 'Bhilai', 'value' => 'bhilai'],
            ['key' => 'Bilaspur', 'value' => 'bilaspur'],
            ['key' => 'Korba', 'value' => 'korba'],
            ['key' => 'Durg', 'value' => 'durg'],
            ['key' => 'Rajnandgaon', 'value' => 'rajnandgaon'],
        ],
        'goa' => [
            ['key' => 'Panaji', 'value' => 'panaji'],
            ['key' => 'Margao', 'value' => 'margao'],
            ['key' => 'Vasco da Gama', 'value' => 'vasco_da_gama'],
            ['key' => 'Mapusa', 'value' => 'mapusa'],
            ['key' => 'Ponda', 'value' => 'ponda'],
        ],
        'gujarat' => [
            ['key' => 'Ahmedabad', 'value' => 'ahmedabad'],
            ['key' => 'Surat', 'value' => 'surat'],
            ['key' => 'Vadodara', 'value' => 'vadodara'],
            ['key' => 'Rajkot', 'value' => 'rajkot'],
            ['key' => 'Bhavnagar', 'value' => 'bhavnagar'],
            ['key' => 'Jamnagar', 'value' => 'jamnagar'],
            ['key' => 'Gandhinagar', 'value' => 'gandhinagar'],
            ['key' => 'Junagadh', 'value' => 'junagadh'],
        ],
        'haryana' => [
            ['key' => 'Faridabad', 'value' => 'faridabad'],
            ['key' => 'Gurugram', 'value' => 'gurugram'],
            ['key' => 'Panipat', 'value' => 'panipat'],
            ['key' => 'Ambala', 'value' => 'ambala'],
            ['key' => 'Karnal', 'value' => 'karnal'],
            ['key' => 'Hisar', 'value' => 'hisar'],
            ['key' => 'Rohtak', 'value' => 'rohtak'],
        ],
        'himachal_pradesh' => [
            ['key' => 'Shimla', 'value' => 'shimla'],
            ['key' => 'Mandi', 'value' => 'mandi'],
            ['key' => 'Dharamshala', 'value' => 'dharamshala'],
            ['key' => 'Solan', 'value' => 'solan'],
            ['key' => 'Kullu', 'value' => 'kullu'],
        ],
        'jharkhand' => [
            ['key' => 'Ranchi', 'value' => 'ranchi'],
            ['key' => 'Jamshedpur', 'value' => 'jamshedpur'],
            ['key' => 'Dhanbad', 'value' => 'dhanbad'],
            ['key' => 'Bokaro', 'value' => 'bokaro'],
            ['key' => 'Deoghar', 'value' => 'deoghar'],
        ],
        'karnataka' => [
            ['key' => 'Bengaluru', 'value' => 'bengaluru'],
            ['key' => 'Mysuru', 'value' => 'mysuru'],
            ['key' => 'Mangaluru', 'value' => 'mangaluru'],
            ['key' => 'Hubballi', 'value' => 'hubballi'],
            ['key' => 'Belagavi', 'value' => 'belagavi'],
            ['key' => 'Kalaburagi', 'value' => 'kalaburagi'],
            ['key' => 'Davanagere', 'value' => 'davanagere'],
        ],
        'kerala' => [
            ['key' => 'Thiruvananthapuram', 'value' => 'thiruvananthapuram'],
            ['key' => 'Kochi', 'value' => 'kochi'],
            ['key' => 'Kozhikode', 'value' => 'kozhikode'],
            ['key' => 'Thrissur', 'value' => 'thrissur'],
            ['key' => 'Kollam', 'value' => 'kollam'],
            ['key' => 'Kannur', 'value' => 'kannur'],
        ],
        'madhya_pradesh' => [
            ['key' => 'Bhopal', 'value' => 'bhopal'],
            ['key' => 'Indore', 'value' => 'indore'],
            ['key' => 'Gwalior', 'value' => 'gwalior'],
            ['key' => 'Jabalpur', 'value' => 'jabalpur'],
            ['key' => 'Ujjain', 'value' => 'ujjain'],
            ['key' => 'Rewa', 'value' => 'rewa'],
        ],
        'maharashtra' => [
            ['key' => 'Mumbai', 'value' => 'mumbai'],
            ['key' => 'Pune', 'value' => 'pune'],
            ['key' => 'Nagpur', 'value' => 'nagpur'],
            ['key' => 'Nashik', 'value' => 'nashik'],
            ['key' => 'Aurangabad', 'value' => 'aurangabad'],
            ['key' => 'Solapur', 'value' => 'solapur'],
            ['key' => 'Thane', 'value' => 'thane'],
            ['key' => 'Kolhapur', 'value' => 'kolhapur'],
        ],
        'manipur' => [
            ['key' => 'Imphal', 'value' => 'imphal'],
            ['key' => 'Thoubal', 'value' => 'thoubal'],
            ['key' => 'Bishnupur', 'value' => 'bishnupur'],
        ],
        'meghalaya' => [
            ['key' => 'Shillong', 'value' => 'shillong'],
            ['key' => 'Tura', 'value' => 'tura'],
            ['key' => 'Jowai', 'value' => 'jowai'],
        ],
        'mizoram' => [
            ['key' => 'Aizawl', 'value' => 'aizawl'],
            ['key' => 'Lunglei', 'value' => 'lunglei'],
        ],
        'nagaland' => [
            ['key' => 'Kohima', 'value' => 'kohima'],
            ['key' => 'Dimapur', 'value' => 'dimapur'],
            ['key' => 'Mokokchung', 'value' => 'mokokchung'],
        ],
        'odisha' => [
            ['key' => 'Bhubaneswar', 'value' => 'bhubaneswar'],
            ['key' => 'Cuttack', 'value' => 'cuttack'],
            ['key' => 'Rourkela', 'value' => 'rourkela'],
            ['key' => 'Berhampur', 'value' => 'berhampur'],
            ['key' => 'Sambalpur', 'value' => 'sambalpur'],
        ],
        'punjab' => [
            ['key' => 'Ludhiana', 'value' => 'ludhiana'],
            ['key' => 'Amritsar', 'value' => 'amritsar'],
            ['key' => 'Jalandhar', 'value' => 'jalandhar'],
            ['key' => 'Patiala', 'value' => 'patiala'],
            ['key' => 'Bathinda', 'value' => 'bathinda'],
        ],
        'rajasthan' => [
            ['key' => 'Jaipur', 'value' => 'jaipur'],
            ['key' => 'Jodhpur', 'value' => 'jodhpur'],
            ['key' => 'Udaipur', 'value' => 'udaipur'],
            ['key' => 'Kota', 'value' => 'kota'],
            ['key' => 'Bikaner', 'value' => 'bikaner'],
            ['key' => 'Ajmer', 'value' => 'ajmer'],
        ],
        'sikkim' => [
            ['key' => 'Gangtok', 'value' => 'gangtok'],
            ['key' => 'Namchi', 'value' => 'namchi'],
        ],
        'tamil_nadu' => [
            ['key' => 'Chennai', 'value' => 'chennai'],
            ['key' => 'Coimbatore', 'value' => 'coimbatore'],
            ['key' => 'Madurai', 'value' => 'madurai'],
            ['key' => 'Tiruchirappalli', 'value' => 'tiruchirappalli'],
            ['key' => 'Salem', 'value' => 'salem'],
            ['key' => 'Tirunelveli', 'value' => 'tirunelveli'],
        ],
        'telangana' => [
            ['key' => 'Hyderabad', 'value' => 'hyderabad'],
            ['key' => 'Warangal', 'value' => 'warangal'],
            ['key' => 'Nizamabad', 'value' => 'nizamabad'],
            ['key' => 'Karimnagar', 'value' => 'karimnagar'],
        ],
        'tripura' => [
            ['key' => 'Agartala', 'value' => 'agartala'],
            ['key' => 'Udaipur (Tripura)', 'value' => 'udaipur_tripura'],
        ],
        'uttar_pradesh' => [
            ['key' => 'Lucknow', 'value' => 'lucknow'],
            ['key' => 'Kanpur', 'value' => 'kanpur'],
            ['key' => 'Varanasi', 'value' => 'varanasi'],
            ['key' => 'Agra', 'value' => 'agra'],
            ['key' => 'Prayagraj', 'value' => 'prayagraj'],
            ['key' => 'Ghaziabad', 'value' => 'ghaziabad'],
            ['key' => 'Noida', 'value' => 'noida'],
            ['key' => 'Meerut', 'value' => 'meerut'],
        ],
        'uttarakhand' => [
            ['key' => 'Dehradun', 'value' => 'dehradun'],
            ['key' => 'Haridwar', 'value' => 'haridwar'],
            ['key' => 'Roorkee', 'value' => 'roorkee'],
            ['key' => 'Haldwani', 'value' => 'haldwani'],
        ],
        'west_bengal' => [
            ['key' => 'Kolkata', 'value' => 'kolkata'],
            ['key' => 'Howrah', 'value' => 'howrah'],
            ['key' => 'Durgapur', 'value' => 'durgapur'],
            ['key' => 'Asansol', 'value' => 'asansol'],
            ['key' => 'Siliguri', 'value' => 'siliguri'],
        ],
        'andaman_nicobar' => [
            ['key' => 'Port Blair', 'value' => 'port_blair'],
        ],
        'chandigarh' => [
            ['key' => 'Chandigarh', 'value' => 'chandigarh_city'],
        ],
        'dadra_nagar_haveli_daman_diu' => [
            ['key' => 'Silvassa', 'value' => 'silvassa'],
            ['key' => 'Daman', 'value' => 'daman'],
            ['key' => 'Diu', 'value' => 'diu'],
        ],
        'delhi' => [
            ['key' => 'New Delhi', 'value' => 'new_delhi'],
            ['key' => 'North Delhi', 'value' => 'north_delhi'],
            ['key' => 'South Delhi', 'value' => 'south_delhi'],
        ],
        'jammu_kashmir' => [
            ['key' => 'Srinagar', 'value' => 'srinagar'],
            ['key' => 'Jammu', 'value' => 'jammu'],
            ['key' => 'Anantnag', 'value' => 'anantnag'],
        ],
        'ladakh' => [
            ['key' => 'Leh', 'value' => 'leh'],
            ['key' => 'Kargil', 'value' => 'kargil'],
        ],
        'lakshadweep' => [
            ['key' => 'Kavaratti', 'value' => 'kavaratti'],
        ],
        'puducherry' => [
            ['key' => 'Puducherry', 'value' => 'puducherry_city'],
            ['key' => 'Karaikal', 'value' => 'karaikal'],
            ['key' => 'Mahe', 'value' => 'mahe'],
        ],
    ];
}
