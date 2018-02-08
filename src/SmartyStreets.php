<?php
/**
 * SmartyStreets Api
 *
 * @author Vincent Zhu <vincentm@ldproducts.com>
 */

namespace huynguye\smartystreets;

class SmartyStreets
{
    /**
     * Constant urls
     */
    const US_STREET_API_URL  = 'https://api.smartystreets.com/street-address?';
    const US_ZIPCODE_API_URL = 'https://us-zipcode.api.smartystreets.com/lookup?';
    const US_AUTOCOM_API_URL = 'https://autocomplete-api.smartystreets.com/suggest?';
    const US_EXTRACT_API_URL = 'https://extract-beta.api.smartystreets.com/?';
    const INTL_ADDR_API_URL  = 'https://international-street.api.smartystreets.com/verify?';

    /**
     * Default street type
     */
    const DEFAULT_API_TYPE   = 'street';

    /**
     * Default candidates as 10
     */
    const CANDIDATES = 10;

    /**
     * @var string search utl type
     */
    private $_searchType = 'street';

    /**
     * Api Urls
     *
     * @var array
     */
    private $_apiUrls = array(
        'street'        => self::US_STREET_API_URL,
        'zipcode'       => self::US_ZIPCODE_API_URL,
        'autocomplete'  => self::US_AUTOCOM_API_URL,
        'extract'       => self::US_EXTRACT_API_URL,
        'intl'          => self::INTL_ADDR_API_URL,
    );

    /**
     * Response status codes
     *
     * @var array
     */
    private $_statusCodes = array(
        200 => 'OK (success!): The response body will be a JSON array containing zero or more matches for the input provided with the request. The structure of the response is the same for both GET and POST requests',
        400 => 'Bad Request (Malformed Payload): The request was malformed in some way and could not be parsed.',
        403 => 'Forbidden: Because the international service is currently in a limited release phase, only approved accounts may access the service. Please contact us for your account to be granted access.',
        401 => 'Unauthorized: The credentials were provided incorrectly or did not match any existing, active credentials',
        402 => 'Payment Required: There is no active subscription for the account associated with the credentials submitted with the request.',
        413 => 'Request Entity Too Large: The maximum size for a request body to this API is 16K (16,384 bytes).',
        422 => 'Unprocessable Entity (Unsuitable Payload): The value of the prefix input parameter was too long and could not be processed..',
        429 => 'Too Many Requests: When using public "website key" authentication, we restrict the number of requests coming from a given source over too short of a time. If you use "website key" authentication, you can avoid this error by adding your IP address as an authorized host for the website key in question.',
        503 => 'Gateway Timeout: Our own upstream data provider did not respond in a timely fashion and the request failed. A serious, yet rare occurrence indeed.',
    );

    /**
     * Dvp match codes
     *
     * @var array
     */
    private $_dvpMatchCodes = array(
        'Y' => 'Confirmed; entire address was Delivery Point Validation confirmed deliverable.',
        'N' => 'Not Confirmed; address could not be Delivery Point Validation confirmed as deliverable (only returned as part of the XML response).',
        'S' => 'Confirmed By Dropping Secondary; address was Delivery Point Validation confirmed by dropping secondary info (apartment, suite, etc.). (e.g., 62 Ea Darden Dr Apt 298 Anniston, AL)',
        'D' => 'Confirmed - Missing Secondary Info; the address was Delivery Point Validation confirmed, but it is missing secondary information (apartment, suite, etc.). (e.g., 122 Mast Rd Lee, NH)',
        ''  => 'The address was not submitted for DPV. This is usually because the address does not have a ZIP Code and a +4 add-on code, or the address has already been determined to be Not Deliverable (only returned as part of the XML response).'
    );

    /**
     * Dvp footnotes
     *
     * @var array
     */
    private $_dvpFootnotes = array(
        'AA' => 'City/state/ZIP + street are all valid.(e.g., 2335 S State St Ste 300 Provo UT)',
        'A1' => 'ZIP+4 not matched; address is invalid. (City/state/ZIP + street don\'t match.)',
        'BB' => 'ZIP+4 matched; confirmed entire address; address is valid. (e.g., 2335 S State St Ste 300 Provo UT)',
        'CC' => 'Confirmed address by dropping secondary (apartment, suite, etc.) information.',
        'F1' => 'Matched to military address. (e.g., Unit 2050 Box 4190 APO AP 96278)',
        'G1' => 'Matched to general delivery address. (e.g., General Delivery Provo UT 84604)',
        'M1' => 'Primary number (e.g., house number) is missing. (e.g., N University Ave Provo UT)',
        'M3' => 'Primary number (e.g., house number) is invalid. (e.g., 16 N University Ave Provo UT)',
        'N1' => 'Confirmed with missing secondary information; address is valid but it also needs a secondary number (apartment, suite, etc.). (e.g., 2335 S State St Provo UT)',
        'P1' => 'PO, RR, or HC box number is missing. (e.g., RR 5 Cadiz KY)',
        'P3' => 'PO, RR, or HC box number is invalid. (e.g., RR 5 Box 1000 Cadiz KY)',
        'RR' => 'Confirmed address with private mailbox (PMB) info. (e.g., 3214 N university ave #409 Provo UT)',
        'R1' => 'Confirmed address without private mailbox (PMB) info. (e.g., 3214 N university ave Provo UT)',
        'U1' => 'Matched a unique ZIP Code. (e.g., 100 North Happy Street 12345)',
    );

    // private $_footnotes = array(
    //     'A#' => 'Corrected ZIP Code',
    //     'B#' => 'Fixed city/state spelling',
    //     'C#' => 'Invalid city/state/ZIP',
    //     'D#' => 'No ZIP+4 assigned',
    //     'E#' => 'Same ZIP for multiple',
    //     'F#' => 'Address not found',
    //     'G#' => 'Used firm data',
    //     'H#' => 'Missing secondary number',
    //     'I#' => 'Insufficient/ incorrect address data',
    //     'J#' => 'Dual address',
    //     'K#' => 'Cardinal rule match',
    //     'L#' => 'Changed address component',
    //     'LL#' => 'Flagged address for LACSLink',
    //     'LI#' => 'Flagged address for LACSLink',
    //     'M#' => 'Fixed street spelling',
    //     'N#' => 'Fixed abbreviations',
    //     'O#' => 'Multiple ZIP+4; lowest used',
    //     'P#' => 'Better address exists',
    //     'Q#' => 'Unique ZIP match',
    //     'R#' => 'No match; EWS: Match soon',
    //     'S#' => 'Bad secondary address',
    //     'T#' => 'Multiple response due to magnet street syndrome',
    //     'U#' => 'Unofficial post office name',
    //     'V#' => 'Unverifiable city / state',
    //     'W#' => 'Invalid delivery address',
    //     'X#' => 'Unique ZIP Code',
    //     'Y#' => 'Military match',
    //     'Z#' => 'Matched with ZIPMOVE',
    // );

    /**
     * Footnotes
     *
     * @var array
     */
    private $_footnotes = array(
        'A#' => 'The address was found to have a different 5-digit ZIP Code than given in the submitted list. The correct ZIP Code is shown in the ZIP Code field. (e.g., 4800 Fairmount Ave Kansas City MO 64111)',
        'B#' => 'The spelling of the city name and/or state abbreviation in the submitted address was found to be different than the standard spelling. The standard spelling of the city name and state abbreviation is shown in the City and State fields. (e.g., 39 Main Street Roeblong NJ 08554)',
        'C#' => 'The ZIP Code in the submitted address could not be found because neither a valid city and state, nor valid 5-digit ZIP Code was present. SmartyStreets recommends that the customer check the accuracy of the submitted address. (e.g., 200 Camp Drive, 25816)',
        'D#' => 'This is a record listed by the United States Postal Service as a non-deliverable location. We recommends that the customer check the accuracy of the submitted address. (e.g., 39 Main Street Roeblong NJ 08554)',
        'E#' => 'Multiple records were returned, but each shares the same 5-digit ZIP Code. (e.g., 1 Rosedale Baltimore MD)',
        'F#' => 'The address, exactly as submitted, could not be found in the city, state, or ZIP Code provided. Many factors contribute to this; either the primary number is missing, the street is missing, or the street is too horribly misspelled to understand. (e.g., 2600 Rafe Lane Jackson MS 39201)',
        'G#' => 'Information in the firm line was determined to be a part of the address. It was moved out of the firm line and incorporated into the address line. (e.g., addressee:"14315 50th Pl N", street:"First Union", city:"Plymouth", state:"MN", zipcode:"55446")',
        'H#' => 'ZIP+4 information indicates that this address is a building. The address as submitted does not contain a secondary (apartment, suite, etc.) number. SmartyStreets recommends that the customer check the accuracy of the submitted address and add the missing secondary number to ensure the correct Delivery Point Barcode (DPBC). (e.g., 109 Wimbledon Sq Chesapeake, VA 23320)',
        'I#' => 'More than one ZIP+4 Code was found to satisfy the address as submitted. The submitted address did not contain sufficiently complete or correct data to determine a single ZIP+4 Code. SmartyStreets recommends that the customer check the accuracy and completeness of the submitted address. For example, a street may have a similar address at both the north and south ends of the street. (e.g., 1 Rosedale Baltimore MD 21229)',
        'J#' => 'The input contained two addresses. For example: 123 MAIN ST PO BOX 99. (e.g., PO Box 38606 30th Street Train Station Philadelphia PA 19104)',
        'K#' => 'The cardinal direction (North, South, East, West) was changed in order to obtain a match. While the output address is valid, it may not be the intended address, so be aware. (e.g., 315 W Cesar Chavez St Austin TX)',
        'L#' => 'An address component (i.e., directional or suffix only) was added, changed, or deleted in order to achieve a match. (e.g., 173 Broadway Salt Lake UT 84101)',
        'LL#' => 'The input address matched a record that was LACS-indicated, that was submitted to LACSLink for processing. This does not mean that the address was converted; it only means that the address was submitted to LACSLink because the input address had the LACS indicator set.',
        'LI#' => 'The input address matched a record that was LACS-indicated, that was submitted to LACSLink for processing. This does not mean that the address was converted; it only means that the address was submitted to LACSLink because the input address had the LACS indicator set.',
        'M#' => 'The spelling of the street name was changed in order to achieve a match. (e.g., 3308 Fountainviuw Monsey NY)',
        'N#' => 'The delivery address was standardized. For example, if STREET was in the delivery address, SmartyStreets will return ST as its standard spelling. (e.g., 2438 Brown Avenue Knoxville TN 37917)',
        'O#' => 'More than one ZIP+4 Code was found to satisfy the address as submitted. The lowest ZIP+4 add-on may be used to break the tie between the records. (e.g., RR 2 Box 132 Wolf Summit WV 26426)',
        'P#' => 'The delivery address is matchable, but it is known by another (preferred) name. For example, in New York, NY, AVENUE OF THE AMERICAS is also known as 6TH AVE. An inquiry using a delivery address of 39 6th Avenue would be flagged with Footnote P. (e.g., 131 Stone Farm Lebanon NH 03766)',
        'Q#' => 'Match to an address with a unique ZIP Code (e.g., 645 Swick Hill Street Charlotte NC 28263)',
        'R#' => 'The delivery address is matchable, but the Early Warning System file indicates that an exact match will be available soon. (e.g., street: 1644 CR 1800E PMB 17420, city: Arthur, state: IL, zipcode: 61911)',
        'S#' => 'The secondary information (apartment, suite, etc.) does not match that on the national ZIP+4 file. The secondary information, although present on the input address, was not valid in the range found on the national ZIP+4 file. (e.g., 1409 Hueytown Rd Apt 1781 Bessemer AL 35023)',
        'T#' => 'The search resulted in a single response; however, the record matched was flagged as having magnet street syndrome, and the input street name components (pre-directional, primary street name, post-directional, and suffix) did not exactly match those of the record. A "magnet street" is one having a primary street name that is also a suffix or directional word, having either a post-directional or a suffix (i.e., 2220 PARK MEMPHIS TN logically matches to a ZIP+4 record 2200-2258 PARK AVE MEMPHIS TN 38114-6610), but the input address lacks the suffix "AVE" which is present on the ZIP+4 record. The primary street name "PARK" is a suffix word. The record has either a suffix or a post-directional present. Therefore, in accordance with CASS requirements, a ZIP+4 Code must not be returned. The multiple response return code is given since a "no match" would prevent the best candidate. (e.g., 84 Green St Northampton MA)',
        'U#' => 'The city or post office name in the submitted address is not recognized by the United States Postal Service as an official last line name (preferred city name), and is not acceptable as an alternate name. The preferred city name is included in the City field. (e.g., 9894 Bissonnet St #723 Sharpstown TX 77036)',
        'V#' => 'The city and state in the submitted address could not be verified as corresponding to the given 5-digit ZIP Code. This comment does not necessarily denote an error; however, SmartyStreets recommends that the customer check the accuracy of the city and state in the submitted address. (e.g., 107 Kerwood St Kildeer IL 60067)',
        'W#' => 'The input address record contains a delivery address other than a PO Box, General Delivery, or Postmaster 5-digit ZIP Code that is identified as a "small town default". The USPS does not provide street delivery service for this ZIP Code. The USPS requires the use of a PO Box, General Delivery, or Postmaster for delivery within this ZIP Code.',
        'X#' => 'Default match inside a unique ZIP Code (e.g., 609 Pheasant Ridge Road Wayne PA 19088)',
        'Y#' => 'Match made to a record with a military ZIP Code. (e.g., PSC 10 Box 1324 APO AE 09142)',
        'Z#' => 'The ZIPMOVE product shows which ZIP+4 records have moved from one ZIP Code to another. If an input address matches a ZIP+4 record which the ZIPMOVE product indicates has moved, the search is performed again in the new ZIP Code. (e.g., 4928 HERITAGE XING DR SW Hiram GA 30141)',
    );

    /**
     * Allowed input fields
     *
     * @var array
     */
    private $_allowedInputFileds = array(
        'input_id', 'city', 'state','street','street2', 'candidates',
        'secondary', 'lastline', 'addressee', 'urbanization',
        'address1','address2','locality','administrative_area','postal_code',
        'country','zipcode','prefix','suggestions','city_filter','state_filter',
        'prefer','geolocate','geolocate_precision', 'country'
    );

    /**
     * Original address data
     *
     * @var array
     */
    private $_originAddress = array();

    /**
     * Request data
     *
     * @var array
     */
    private $_request = array();

    /**
     * Response data
     *
     * @var array
     */
    private $_response = array();

    /**
     * Errors
     *
     * @var array
     */
    private $_errors = array();

    /**
     * Filter data
     *
     * @var array
     */
    private $_filters = array();

    /**
     * Auth-id of Api
     *
     * @var string
     */
    private $_authId = null;

    /**
     * Auth-token of Api
     *
     * @var string
     */
    private $_authToken = null;

    /**
     * Return data count
     *
     * @var integer
     */
    private $_candidates = null;

    /**
     * Constructor
     *
     * @param string $authId
     * @param string $authToken
     * @return void
     */
    public function __construct($authId, $authToken)
    {
        $this->_authId = $authId;
        $this->_authToken = $authToken;
        $this->_candidates = self::CANDIDATES;
    }

    /**
     * Set auth-id
     *
     * @param string $authId
     * @return void
     */
    public function setAuthId($authId)
    {
        if ($authId) {
            $this->_authId = $authId;
        }
    }

    public function getAuthId()
    {
        return $this->_authId;
    }

    /**
     * Set auth-token
     *
     * @param string $authToken
     * @return void
     */
    public function setAuthToken($authToken)
    {
        if ($authToken) {
            $this->_authToken = $authToken;
        }
    }

    public function getAuthToken()
    {
        return $this->_authToken;
    }

    /**
     * Set candidates
     *
     * @param integer $candidates
     * @return void
     */
    public function setCandidates($candidates)
    {
        if (is_numeric($candidates) && $candidates > 0) {
            $this->_candidates = $candidates;
        }
    }

    public function getCandidates()
    {
        return $this->_candidates;
    }

    public function getAllowedInputFields()
    {
        return $this->_allowedInputFileds;
    }

    /**
     * Is field valid?
     *
     * @param string $input
     * @return boolean
     */
    public function isValidField($input)
    {
        if (in_array($input, $this->getAllowedInputFields())) {
            return TRUE;
        }
        return FALSE;
    }

    /**
     * Add error messages
     *
     * @param string $error
     * @return void
     */
    public function addError($error)
    {
        $this->_errors[] = $error;
    }

    /**
     * Get errors
     *
     * @return array
     */
    public function getErrors()
    {
        return $this->_errors;
    }

    /**
     * Add filter
     *
     * @param string $input filter field name
     * @param string $value filter value
     * @return void
     */
    public function addFilter($input, $value)
    {
        if ($input && $value && $this->isValidField($input)) {
            $this->_filters[$input] = $value;
        }
    }

    /**
     * Get filter data
     *
     * @return array
     */
    public function getFilters()
    {
        return $this->_filters;
    }

    /**
     * Verify auth-id and auth-token
     *
     */
    public function validate()
    {
        if (!$this->getAuthId() || !$this->getAuthToken()) {
            $this->addError('auth-id or auth-token not found!');
        }
        return count($this->getErrors()) > 0 ? FALSE: TRUE;
    }

    public function getValidInputData($input = array(), $includeAuth = TRUE)
    {
        if ($this->validate()) {
            $inputFields = $this->getAllowedInputFields();
            $request = array();
            $this->_request = array();
            $this->_originAddress = $input;
            if ($intlAddress = $this->getIntlAddressFormat($input)) {
                $request = $intlAddress;
                $this->_searchType = 'intl';
            } else {
                foreach ($input as $k => $v) {
                    $k = strtolower($k);
                    if ($k && $v) {
                        if ($k == 'postcode') {
                            $k = 'zipcode';
                        }
                        if (in_array($k, $inputFields)) {
                            if ($k == 'street' && is_array($v)) {
                                $request[$k] = join(' ', $v);
                            } else {
                                $request[$k] = $v;
                            }
                        }
                    }
                }
            }
            if ($request) {
                if ($filters = $this->getFilters()) {
                    foreach ($filters as $k => $v) {
                        $request[$k] = $v;
                    }
                }
                $this->_request = $request;
                if ($includeAuth) {
                    $request['auth-id'] = $this->getAuthId();
                    $request['auth-token'] = $this->getAuthToken();
                }
                if (empty($request['candidates'])) {
                    $request['candidates'] = $this->getCandidates();
                }
                return http_build_query($request);
            }
        }
        return FALSE;
    }

    public function getApiUrl($type)
    {
        $type = trim(strtolower($type));
        if (!empty($this->_apiUrls[$type])) {
            return $this->_apiUrls[$type];
        }
        $this->addError(sprintf('api type is not correct, allowed api type %s', join(', ', array_keys($this->_apiUrls))));
        return FALSE;
    }

    public function getStatusCodes()
    {
        return $this->_statusCodes;
    }

    public function getStatusCode($code = '')
    {
        if ($code && !empty($this->_statusCodes[$code])) {
            return $this->_statusCodes[$code];
        }
    }

    public function getDvpMatchCodes()
    {
        return $this->_dvpMatchCodes;
    }

    public function getDvpMatchCode($code = '')
    {
        if ($code && !empty($this->_dvpMatchCodes[$code])) {
            return $this->_dvpMatchCodes[$code];
        }
    }

    public function getDvpFootnotes()
    {
        return $this->_dvpFootnotes;
    }

    public function getDvpFootnote()
    {
        $analysis = $this->getAnalysis();
        $notes = array();
        if (!empty($analysis['dpv_footnotes'])) {
            $footnotes = $this->getDvpFootnotes();
            $codes = str_split($analysis['dpv_footnotes'], 2);
            foreach ($codes as $code) {
                if (array_key_exists($code, $footnotes)) {
                    $notes[$code] = $footnotes[$code];
                }
            }
        }
        return $notes;
    }

    public function getFootnote()
    {
        $analysis = $this->getAnalysis();
        $notes = array();
        if (!empty($analysis['footnotes'])) {
            $footnotes = $this->getFootnotes();
            $codes = str_split($analysis['footnotes'], 2);
            foreach ($codes as $code) {
                if (array_key_exists($code, $footnotes)) {
                    $notes[$code] = $footnotes[$code];
                }
            }
        }
        return $notes;
    }

    public function getFootnotes()
    {
        return $this->_footnotes;
    }


    public function searchAddress($input = array(), $type = 'street')
    {
        $this->resetData();
        $this->_searchType = $type;
        $request = $this->getValidInputData($input);
        $url = $this->getApiUrl($this->_searchType);
        if ($request && $url) {
            $url .= $request;
            // echo $url . PHP_EOL;
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('X-Include-Invalid: true', 'X-Standardize-Only: true'));
            $result = curl_exec($ch);
            if (!curl_errno($ch)) {
                $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                if ($statusCode != 200) {
                    $message = $this->getStatusCode($statusCode);
                    if ($message) {
                        $this->addError($message);
                    }
                }
            } else {
                if ($error = curl_error($ch)) {
                    $this->addError($error);
                }
            }
            curl_close($ch);
            if (!$this->getErrors()) {
                if ($result) {
                    $this->_response = json_decode($result, TRUE);
                    return $this->_response;
                } else {
                    return TRUE; // frontend need to handle if data is TRUE
                }
            }
        }
	    return FALSE;
    }

    public function resetData()
    {
        $this->_request = $this->_response = $this->_errors = $this->_filers = array();
        $this->_reason = null;
    }

    public function getRequest()
    {
        return $this->_request;
    }

    public function getResponse()
    {
        return $this->_response;
    }

    public function getAnalysis()
    {
        if (is_array($this->_response) && !empty($this->_response[0]['analysis'])) {
            return $this->_response[0]['analysis'];
        }
    }

    public function getMetaData()
    {
        if (is_array($this->_response) && !empty($this->_response[0]['metadata'])) {
            return $this->_response[0]['metadata'];
        }
    }

    public function getComponents()
    {
        if (is_array($this->_response) && !empty($this->_response[0]['components'])) {
            return $this->_response[0]['components'];
        }
    }

    public function getIntlAddressFormat($input)
    {
        if ($input['country_id'] == 'CA') {
            $data = array();
            foreach ($input as $k => $v) {
                if ($k == 'street') {
                    foreach ($v as $idx => $v1) {
                        if ($v1) {
                            $data['address' . ($idx + 1)] = $v1;
                        }
                    }
                }
                if ($k == 'city') {
                    $data['locality'] = $v;
                }
                if ($k == 'state') {
                    $data['administrative_area'] = $v;
                }
                if ($k == 'zipcode' || $k == 'postcode') {
                    $data['postal_code'] = $v;
                }
                if ($k == 'country') {
                    $data['country'] = $v;
                }
            }
            if ($data) {
                return $data;
            }
        }
        return FALSE;
    }

    public function isMatched()
    {
        $analysis = $this->getAnalysis();
        $code = $analysis['dvp_match_code'];
        if ($analysis) {
            $code = $analysis['dvp_match_code'];
            if ($code == 'Y') {
                return TRUE;
            }
        }
        return FALSE;
    }
}

