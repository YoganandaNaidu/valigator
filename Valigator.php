<?php

namespace Fishfin;

/**
 * Standalone data sanitization and validation class.
 *
 * @author      fishfin
 * @link        http://aalapshah.in
 * @version     0.0.1-alpha
 * @license     MIT
 * 
 * Valigator is a standalone PHP sanitization and validation class that does not
 * require any framework to do its job. It is a very detailed implementation and
 * I hope you will find it useful.
 * 
 * Valigator should work with PHP 5.4.* upwards.
 * 
 * Data sanitization and validation can be run independently of each other, or
 * in succession.
 */
class Valigator
{
    // Customer sanitization methods
    protected static $_customSanitizations = array();

    // Custom validation methods
    protected static $_customValidations = array();
    
    // Multibyte supported
    protected $_mbSupported = FALSE;

    // Default validation error messages
    protected $_validationErrorMsgs = [
        'default' => '{field} is invalid',
        'default_long' => 'Field {field} with value \'{value}\' failed validation {filter}',
        'inexistent_validation' => 'Validation filter {filter} does not exist for {field}, please contact the application owner',
        'alphabetic' => '{field} may only contain alphabetic characters',
        'alphanumeric' => '{field} may only contain alpha-numeric characters',
        'boolean' => '{field} field may only contain a true or false value',
        'creditcard' => '{field} does not contain a valid credit card number',
        'date' => '{field} is not a valid date',
        'email' => '{field} is not a valid email address',
        'endswith' => '{field} does not end with {arg1}',
        'equalsfield' => '{field} does not equal {arg1}',
        'exactlen' => '{field} must be exactly {arg1} characters long',
        'fileextension' => '{field} does not have a valid file extension',
        'float' => '{field} may only contain a float value',
        'guidv4' => '{field} is not a valid GUID (v4)',
        'iban' => '{field} is not a valid IBAN',
        'inlist' => '{field} must be one of these values: {args}',
        'integer' => '{field} may only contain an integer value',
        'ip' => '{field} does not contain a valid IP address',
        'ipv4' => '{field} does not contain a valid IPv4 address',
        'ipv6' => '{field} does not contain a valid IPv6 address',
        'jsonstring' => '{field} is not a JSON-encoded string',
        'maxlen'=> '{field} must be {arg1} or shorter in length',
        'maxnumeric' => '{field} must be a numeric value, equal to or lower than {arg1}',
        'minage' => 'The {field} field needs to have an age greater than or equal to {arg1}',
        'minlen'=> '{field} must be {arg1} or longer in length',
        'minnumeric' => 'The {field} field needs to be a numeric value, equal to, or higher than {arg1}',
        'mismatch' => 'There is no validation rule for {field}',
        'notinlist' => '{field} cannotbe one of these values {args}',
        'numeric' => '{field} may only contain numeric characters',
        'personname' => '{field} does not seem to contain a person\'s name',
        'phonenumber' => '{field} does not seem to contain a valid phone number',
        'regex' => '{field} did not match regular expression: {arg1}',
        'required' => '{field} is required',
        'requiredfile' => 'File is required for {field}',
        'startswith' => '{field} does not start with {arg1}',
        'streetaddress' => '{field} does not seem to be a valid street address',
        'url' => 'The {field} field is required to be a valid URL',
        'urlexists' => '{field} URL does not exist',
    ];

    //Singleton instance of Valigator
    protected static $_instance = NULL;
    
    // Contains fields mapping:
    // 'field1' => [
    //   -- label set to upper-case words by the class by default - , can be
    //   -- overwritten using setFieldLabel method.
    //   'label' => 'Field 1' or 'New Label',
    //   'sanitizations' => [ 'filter' => 'somefilter',
    //                      'args' => ['arg1', 'arg2'],
    //   ]
    //   'validations' => [ 'filter' => 'somefilter',
    //                      'args' => ['arg1', 'arg2'],
    //                      'errormsg' => '{field} with value {value} failed validation.',
    //   ]
    // ]
    protected $_filters = array();

    // Instance attribute containing errors from last run
    // Will contain the following format if validation error encountered
    // 'field1' => 'error message'
    //
    // The error message is picked up in following order:
    //   - Custom error message set for field if internal validation used
    //   - Internal error message if internal validation used
    //   - Custom error message set in addCustomValidation if custom validation used
    //   - Custom error message set for field if custom validation used
    //
    // Following replacements are done automatically in the error message:
    // {field}   : label of the field validated.
    // {value}   : value the field held when it was validated. If value was not
    //             set, 'empty' (without quotes) will be printed. If value
    //             contained an array, comma-separated string will be printed.
    // {filter}  : filter used for validation.
    // {args}    : comma-delimited args list as string
    // {arg<n>}  : argument passed to filter, where <n> is a valid argument
    //             number.
    //      
    protected $_validationErrors = array();

    // Validation rules for execution
    protected $validation_rules = array();

    // Filter rules for execution
    protected $filter_rules = array();

    // All HTML Tags that will be removed by sanitize_basichtmltags method
    protected static $_basicHTMLTags = '<a><b><blockquote><br><code><dd><dl>'
            . '<em><hr><h1><h2><h3><h4><h5><h6><i><img><label><li><p><span>'
            . '<strong><sub><sup><ul>';

    // All noise words that will be removed by sanitize_noisewords method
    protected static $_enNoiseWords = 'about,after,all,also,an,and,another,any,'
            . 'are,as,at,be,because,been,before,being,between,both,but,by,came,'
            . 'can,come,could,did,do,each,for,from,get, got,has,had,he,have,'
            . 'her,here,him,himself,his,how,if,in,into,is,it,its,it\'s,like,'
            . 'make,many,me,might,more,most,much,must,my,never,now,of,on,only,'
            . 'or,other,our,out,over,said,same,see,should,since,some,still,'
            . 'such,take,than,that,the,their,them,then,there,these,they,this,'
            . 'those,through,to,too,under,up,very,was,way,we,well,were,what,'
            . 'where,which,while,who,with,would,you,your,a,b,c,d,e,f,g,h,i,j,k,'
            . 'l,m,n,o,p,q,r,s,t,u,v,w,x,y,z,$,1,2,3,4,5,6,7,8,9,0,_';

    // map options passed to functions(parameters) to be called internally
    protected $_synonyms = [
        'allow_fraction' => FILTER_FLAG_ALLOW_FRACTION,
        'allow_hex' => FILTER_FLAG_ALLOW_HEX,
        'allow_octal' => FILTER_FLAG_ALLOW_OCTAL,
        'allow_scientific' => FILTER_FLAG_ALLOW_SCIENTIFIC,
        'allow_thousand' => FILTER_FLAG_ALLOW_THOUSAND,
        'alphabet' => 'alphabetic',
        'bool' => 'boolean',
        'encode_amp' => FILTER_FLAG_ENCODE_AMP,
        'encode_high' => FILTER_FLAG_ENCODE_HIGH,
        'encode_low' => FILTER_FLAG_ENCODE_LOW,
        'fileext' => 'fileextension',
        'host_required' => FILTER_FLAG_HOST_REQUIRED,
        'int' => 'integer',
        'ipv4' => FILTER_FLAG_IPV4,
        'ipv6' => FILTER_FLAG_IPV6,
        'path_required' => FILTER_FLAG_PATH_REQUIRED,
        'query_required' => FILTER_FLAG_QUERY_REQUIRED,
        'no_encode_quotes' => FILTER_FLAG_NO_ENCODE_QUOTES,
        'no_priv_range' => FILTER_FLAG_NO_PRIV_RANGE,
        'no_res_range' => FILTER_FLAG_NO_RES_RANGE,
        'null_on_failure' => FILTER_NULL_ON_FAILURE,
        'num' => 'numeric',
        'number' => 'numeric',
        'scheme_required' => FILTER_FLAG_SCHEME_REQUIRED,
        'str' => 'string',
        'strip_high' => FILTER_FLAG_STRIP_HIGH,
        'strip_low' => FILTER_FLAG_STRIP_LOW,
        'strip_backtick' => FILTER_FLAG_STRIP_BACKTICK,        
    ];
    
    public function dummy($parm) {
        //return $this->_convertVariableNameToUpperCaseWords($parm);
        return $this->_convertFieldFiltersStringToArray($parm);
        //return $this->_convertFieldFiltersArrayToString($this->_convertFieldFiltersStringToArray($parm));
    }

    /**
     * Magic method to generate the validation error messages.
     * 
     * @param array Input data of the form {'field1' => 'value1', 'field2' =>
     * 'value2'}
     *
     * @return object
     */
    public function __construct(array $filters = array())
    {
        $this->_mbSupported = function_exists('mb_detect_encoding');

        foreach ($filters as $field => $fieldFilter) {
            $this->_filters[$field]['label'] = isset($fieldFilter['label'])
                    ? $fieldFilter['label']
                    : $this->_convertVariableNameToUpperCaseWords($field);
            if (isset($fieldFilter['sanitizations'])) {
                $this->setSanitizations(array($field => $fieldFilter['sanitizations']));
            }
            if (isset($fieldFilter['validations'])) {
                $this->setValidations(array($field => $fieldFilter['validations']));
            }
        }

        return $this;
    }

    /**
     * Magic method to generate the validation error messages.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->get_readable_errors(true);
    }

    /**
     * Ensure that the field counts match the validation rule counts.
     *
     * @param array $data
     */
    private function _checkFields(array $data)
    {
        $ruleset = $this->validation_rules();
        $mismatch = array_diff_key($data, $ruleset);
        $fields = array_keys($mismatch);

        foreach ($fields as $field) {
            $this->_validationErrors[] = array(
                'field' => $field,
                'value' => $data[$field],
                'rule' => 'mismatch',
                'param' => null,
            );
        }
    }

    /**
     * Converts input validation rules array to string. Some examples of
     * conversion are:
     *
     * {'filter' => 'filterName', 'args' => {'arg1', 'arg2'}, 'errormsg' =>
     * 'Error Text'} to "filterName:arg1,arg2;'Error Text'"
     *
     * @param array $fieldFilterArray
     *
     * @return string
     */
    private function _convertFieldFiltersArrayToString(array $fieldFilterArray)
    {
        $fieldFilterString = '';

        if (is_array($fieldFilterArray)) {
            $fieldValidationsFlattened = array();
            foreach ($fieldFilterArray as $fieldValidation) {
                if (isset($fieldValidation['filter'])) {
                    $fieldValidationsFlattened[] =
                            $fieldValidation['filter'] . ':'
                            . (isset($fieldValidation['args'])
                                    ? implode(',', $fieldValidation['args'])
                                    : '') . ';\''
                            . (isset($fieldValidation['errormsg'])
                                    ? $fieldValidation['errormsg']
                                    : '') . '\'';                    
                }
            }
            $fieldFilterString = implode('|', $fieldValidationsFlattened);
        }

        return $fieldFilterString;
    }

    /**
     * Converts input validation rules string to array. Some examples of
     * conversion are:
     *
     * "filterName:arg1,arg2;'Error Text'" to {'filter' => 'filterName',
     * 'args' => {'arg1', 'arg2'}, 'errormsg' => 'Error Text'}
     *
     * "filterName:arg1" to {'filter' => 'filterName',
     * 'args' => {'arg1'}, 'errormsg' => ''}
     *
     * "filterName:;'Error Text'" to {'filter' => 'filterName',
     * 'args' => {}, 'errormsg' => 'Error Text'}
     *
     * @param string $fieldFilterString
     *
     * @return array
     *
     * @throws Exception if preg_match_all fails
     */
    private function _convertFieldFiltersStringToArray(string $fieldFilterString
            , bool $isValidation = TRUE)
    {
        $fieldFilterArray = array();
        $filters = array();

        if (!preg_match_all('/'                     // group0: filter group
                                                    //     begin parsing filter name
                . '[\|\s\'"]*'                      //                            no-capture: pipe, none or more spaces, single or double quotes
                . '(?P<filter>[^:;].+?)'          // group1: filter name          capture   : at least one char (any char), cannot start with : or ; (as they are used later in parsing), lazy (stop at first match)
                . '(?:[\s\'"]*)'                    //                            no-capture: none or more spaces, single or double quotes
                                                    //     end parsing filter name
                . '(?:$|\|'                         //                            no-capture: end-of-string or pipe (filter name with no args or message)
                .   '|'                             //                            or
                                                    //     begin parsing arguments list
                .   '(?::'                          //                            no-capture: colon (start of args)
                .     '(?P<args>.*?)'               // group2: arguments          capture   : none or more characters, lazy (stop at first match)
                                                    //     end parsing arguments list
                .     '(?:'                         //                            no-capture: followed by...
                .       '(?:$|\|)'                  //                            no-capture: end-of-string or pipe (i.e. filter name with no message)
                .       '|'                         //                            or
                .       '(?:;'                      //                            no-capture: semi-colon (end of args)
                                                    //     begin parsing custom error message
                .         '(?:'                     //                            no-capture: followed by
                .           '[\s]*'                 //                            no-capture: leading spaces
                .           '(?P<quote>[\'"]?)'     // group3: begin-quote        capture   : none or more spaces, single or double quotes
                .           '(?P<errormsg>.*?)'     // group4: custom error msg   capture   : none or more characters, lazy (stop at first match)
                .           '\g{quote}'             //                            no-capture: same as start quote
                .           '[\s]*'                 //                            no-capture: trailing spaces
                .           '(?:$|\|)'              // no-capture: end-of-string or pipe
                .         ')'
                                                    //     end parsing custom error message
                .       ')'
                .     ')'
                .   ')'
                . ')'
                . '/i',
                $fieldFilterString, $filters, PREG_SET_ORDER)) {
            throw new \Exception('Invalid filter encountered: ' . $fieldFilterString);
        }

        foreach ($filters as $filter) {
            if (isset($filter['filter'])
                    && ($this->_mbSupported
                            ? (mb_strcut($filter['filter'], 0, 1) != '/')
                            : (substr($filter['filter'], 0, 1) != '/'))) {
                $fieldFilter = [
                    'filter' => strtolower($filter['filter']),
                    'args' => (isset($filter['args']) ? array_map('trim', explode(",", $filter['args'])) : array()),
                ];
                if ($isValidation) {
                    $fieldFilter['errormsg'] = isset($filter['errormsg']) ? $filter['errormsg'] : '';
                }
                $fieldFilterArray[] = $fieldFilter;
            }
        }

        return $fieldFilterArray;
    }

    /**
     * Converts snake_case, camelCase, PascalCase, lisp-case, Train-Case to
     * Human Readable Upper Case Words string.
     *
     * @param string $developerReadable
     *
     * @return string String
     */
    private function _convertVariableNameToUpperCaseWords(string $developerReadable)
    {
        return ucwords(
            preg_replace('/[\s_-]*'            // space, _ or - ignore
                . '('                          // group 1 start
                . '(?:[A-Z]?[a-z]+)'           // ?: ignore group, 0-1 capital letters, 1+ small letters
                . '|'                          // or
                . '(?:[A-Z]+(?=[A-Z][^A-Z]))'  // ?: ignore group, 1+ capital letters, lookahead 1st capital letter followed by at least one small letter
                . '|'                          // or
                . '(?:[A-Z])'                  // ?: ignore group, 1 capital letter
                . '|'                          // or
                . '(?:[^A-Za-z-]+)'            // ?: ignore group, 1+ any letters
                . ')'                          // group 1 end
                . '[\s_-]*/',                  // space, _ or - ignore
                '\1 ',                         // replace with self followed by blank
                $developerReadable)
        );
    }

    /**
     * Function to create and return previously created instance
     *
     * @return Valigator
     */
    private static function _getInstanceOfSelf()
    {
        if (self::$instance === NULL) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Retrieves standard validation error messages from internal array
     *
     * @param string  $key
     * 
     * @return string Error msg
     */
    private function _getValidationErrorMsg(string $key)
    {
        if (array_key_exists($key, $this->_validationErrorMsgs)) {
            $errorMsg = $this->_validationErrorMsgs[$key];
        } elseif (array_key_exists($key = $this->_getSynonym($key)
                , $this->_validationErrorMsgs)) {
            $errorMsg = $this->_validationErrorMsgs[$key];
        } else {
            $errorMsg = $this->_validationErrorMsgs['default_long'];            
        }
        
        return $errorMsg;
    }

    /**
     * Replaces tags in error messages with values.
     *
     * @param string $errorMsg
     * @param string $field
     * @param mixed  $input (which has field => value) or value
     * @param string $filter
     * @param array  $args
     *
     * @return string Converted error message
     */
    private function _readableErrorMsg(string $errorMsg, string $field = ''
            , $input = NULL, string $filter = '', array $args = array())
    {
        $errorMsg = str_replace('{field}', $this->_filters[$field]['label']
                , $errorMsg);

        $errorMsg = str_replace('{filter}', $filter, $errorMsg);

        $errorMsg = str_replace('{args}', implode(', ', $args), $errorMsg);

        foreach ($args as $index => $arg) {
            $argIndex = $index + 1;
            $errorMsg = str_replace("{arg{$argIndex}}", $arg, $errorMsg);
        }

        $value = is_array($input)
                ? (($field != '' && isset($input[$field]))
                        ? $input[$field]
                        : '')
                : (string) $input;
        if ($value == '') {
            $value = 'empty';
        }

        $errorMsg = str_replace('{value}', $value, $errorMsg);

        return $errorMsg;            
    }

    /**
     * Gets synonym from local array.
     *
     * @param string $lookFor
     *
     * @return mixed String or number, based on True(boolean) or the array of error messages
     */
    private function _getSynonym(string $lookFor)
    {
        return array_key_exists($lookForLowerCase = strtolower($lookFor), $this->_synonyms)
                ? $this->_synonyms[$lookForLowerCase] : $lookFor;            
    }

    /**
     * Adds a custom sanitization using a callback function.
     *
     * @param string   $sanitization
     * @param callable $callback
     *
     * @return null
     *
     * @throws Exception
     */
    public static function addCustomSanitization(string $filter
            , callable $callback)
    {
        $filter = strtolower($filter);
        $method = "sanitize_{$filter}";

        if (method_exists(__CLASS__, $method) || isset(self::$_customSanitizations[$filter])) {
            throw new \Exception("Sanitization filter {$filter} already exists");
        }

        self::$_customSanitizations[$filter] = $callback;

        return;
    }

    /**
     * Adds a custom validation rule using a callback function.
     *
     * @param string   $validation
     * @param callable $callback
     *
     * @return null
     *
     * @throws Exception
     */
    public static function addCustomValidation(string $filter
            , callable $callback, string $defaultErrorMsg = NULL)
    {
        $filter = strtolower($filter);
        $method = "validate_{$filter}";

        if (method_exists(__CLASS__, $method) || isset(self::$_customValidations[$filter])) {
            throw new \Exception("Validation filter {$filter} already exists.");
        }

        self::$_customValidations[$filter]['callback'] = $callback;

        if ($defaultErrorMsg === NULL) {
            $defaultErrorMsg = $this->_validationErrorMsgs['default_long'];
        }
        self::$_customValidations[$filter]['errormsg'] = $defaultErrorMsg;

        return;
    }

    /**
     * Sanitize the input data.
     *
     * @param array $input
     * @param null  $fields
     * @param bool  $utf8_encode
     *
     * @return array
     */
    public function cleanse(array $input, array $fields = array(), $utf8_encode = true)
    {
        $magic_quotes = (bool) get_magic_quotes_gpc();

        if (empty($fields)) {
            $fields = array_keys($input);
        }

        $return = array();

        foreach ($fields as $field) {
            if (!isset($input[$field])) {
                continue;
            } else {
                $value = $input[$field];
                if (is_array($value)) {
                    $value = $this->cleanse($value);
                }
                if (is_string($value)) {
                    if ($magic_quotes === true) {
                        $value = stripslashes($value);
                    }

                    if (strpos($value, "\r") !== false) {
                        $value = trim($value);
                    }

                    if (function_exists('iconv') && function_exists('mb_detect_encoding') && $utf8_encode) {
                        $current_encoding = mb_detect_encoding($value);

                        if ($current_encoding != 'UTF-8' && $current_encoding != 'UTF-16') {
                            $value = iconv($current_encoding, 'UTF-8', $value);
                        }
                    }

                    $value = filter_var($value, FILTER_SANITIZE_STRING);
                }

                $return[$field] = $value;
            }
        }

        return $return;
    }

    /**
     * Clears sanitizations for input fields. If no input is sent, sanitizations
     * of all fields are cleared.
     *
     * @param mixed $clearSanitizationsForFields
     *
     * @return object
     */
    public function clearSanitizations($clearSanitizationsForFields = NULL)
    {
        if ($clearSanitizationsForFields === NULL) {
            $clearSanitizationsForFields = array_keys($this->_filters);
        } else {
            $clearSanitizationsForFields = (array) $clearValidationsForFields;
        }

        foreach ($clearSanitizationsForFields as $field) {
            if (isset($this->_filters[$field])) {
                $this->_filters[$field]['sanitizations'] = array();
                //unset($this->_filters[$field]['sanitizations']);
            }
        }
        
        return $this;
    }

    /**
     * Clears validations for input fields. If no input is sent, validations of
     * all fields are cleared.
     *
     * @param mixed $clearValidationsForFields
     *
     * @return object
     */
    public function clearValidations($clearValidationsForFields = NULL)
    {
        if ($clearValidationsForFields === NULL) {
            $clearValidationsForFields = array_keys($this->_filters);
        } else {
            $clearValidationsForFields = (array) $clearValidationsForFields;
        }

        foreach ($clearValidationsForFields as $field) {
            if (isset($this->_filters[$field])) {
                $this->_filters[$field]['validations'] = array();
                //unset($this->_filters[$field]['validations']);
            }
        }
        
        return $this;
    }

    /**
     * Return the error array from the last validation run.
     *
     * @return array
     */
    public function getValidationErrors()
    {
        return $this->_validationErrors;
    }

    /**
     * Shorthand method for running inline data sanitization.
     *
     * @param array $data
     * @param array $filters
     *
     * @return mixed
     */
    public static function sanitizeInline(array $data, array $filters)
    {
        $valigator = self::getInstanceOfSelf();

        return $valigator->sanitize($data, $filters);
    }

    /**
     * Shorthand method for running inline data validation.
     *
     * @param array $data       The data to be validated
     * @param array $validators The Valigator validators
     *
     * @return mixed True(boolean) or the array of error messages
     */
    public static function validateInline(array $data, array $validators)
    {
        $valigator = self::getInstanceOfSelf();

        $valigator->validations($validators);

        if ($valigator->run($data) === false) {
            return $valigator->get_readable_errors(false);
        } else {
            return true;
        }
    }

    /**
     * Perform XSS clean to prevent cross site scripting.
     *
     * @static
     *
     * @param array $data
     *
     * @return array
     */
    public static function xss_clean(array $data)
    {
        foreach ($data as $k => $v) {
            $data[$k] = filter_var($v, FILTER_SANITIZE_STRING);
        }

        return $data;
    }

    /**
     * Helper method to extract an element from an array safely
     *
     * @param mixed $key
     * @param array $array
     * @param mixed $default
     * 
     * @return mixed
     */
    public static function field($key, array $array, $default = NULL)
    {
      if(!is_array($array)) {
        return NULL;
      }

      if(isset($array[$key])) {
        return $array[$key];
      } else {
        return $default;
      }
    }

    /**
     * Set/overwrite field labels.
     *
     * @param array $fieldLabels
     *
     * @return object
     */
    public function setLabels(array $fieldLabels)
    {
        foreach ($fieldLabels as $field => $label) {
            $this->_filters[$field]['label'] = $label;
        }
        
        return $this;
    }

    /**
     * Adds sanitizations for input fields.
     *
     * @param array $fieldSalitizations
     *
     * @return object
     */
    public function setSanitizations(array $fieldSalitizations
            , bool $mergeBefore = FALSE)
    {
        foreach($fieldSalitizations as $field => $fieldFiltersString) {

            if (!isset($this->_filters[$field]['sanitizations'])) {
                $this->_filters[$field]['sanitizations'] = array();
            }

            $fieldFiltersArray =
                    $this->_convertFieldFiltersStringToArray($fieldFiltersString, FALSE);

            $this->_filters[$field]['sanitizations'] = ($mergeBefore === TRUE)
                    ? array_merge_recursive($fieldFiltersArray,
                            $this->_filters[$field]['sanitizations'])
                    : array_merge_recursive($this->_filters[$field]['sanitizations']
                            , $fieldFiltersArray);
        }
        
        return $this;
    }

    /**
     * Adds validations for input fields.
     *
     * @param array $fieldValidations
     *
     * @return object
     */
    public function setValidations(array $fieldValidations)
    {
        foreach($fieldValidations as $field => $fieldFiltersString) {
            if (!isset($this->_filters[$field]['validations'])) {
                $this->_filters[$field]['validations'] = array();
            }

            $fieldFiltersArray =
                    $this->_convertFieldFiltersStringToArray($fieldFiltersString);

            $this->_filters[$field]['validations'] =
                    array_merge_recursive($this->_filters[$field]['validations']
                            , $fieldFiltersArray);
        }
        
        return $this;
    }

    /**
     * Run sanitizations filtering and validation after each other.
     *
     * @param array $data
     * @param bool  $checkFields
     *
     * @return array
     *
     * @throws Exception
     */
    public function run(array $input, $checkFields = false)
    {
        $sanitizedInput = $this->runSanitizations($input);

        $validated = $this->runValidations($sanitizedInput);

        if ($checkFields === TRUE) {
            $this->_checkFields($sanitizedInput);
        }

        if ($validated !== TRUE) {
            return FALSE;
        }

        return $sanitizedInput;
    }

    /**
     * Sanitize the input data according to the specified sanitizations set.
     *
     * @param mixed $input
     *
     * @throws Exception
     *
     * @return mixed
     *
     * @throws Exception
     */
    public function runSanitizations(array $input)
    {
        foreach ($this->_filters as $field => $fieldFilters) {
            if (!array_key_exists($field, $input)
                    || !isset($fieldFilters['sanitizations'])) {
                continue;
            }

            foreach ($fieldFilters['sanitizations'] as $fieldFilter) {
                $filter = $fieldFilter['filter'];
                $filterSynonym = $this->_getSynonym($filter);

                if (count($fieldFilter['args']) > 0) {
                    $args = $fieldFilter['args'];
                    foreach($args as $i => $arg) {
                        $argsSynonyms[$i] = $this->_getSynonym($arg);
                    }                    
                } else {
                    $args = $argsSynonyms = NULL;
                }

                if ($filter == 'default') {
                    $method = "sanitize_{$filter}";
                    $value = isset($input[$field]) ? $input[$field] : NULL;
                    $input[$field] = $this->$method($value, $argsSynonyms);                    
                } elseif (isset(self::$_customSanitizations[$filter])) {
                    $input[$field] = call_user_func(self::$_customSanitizations[$filter]
                            , $input[$field], $argsSynonyms);
                } elseif (is_callable(array($this, "sanitize_{$filter}"))) {
                    $method = "sanitize_{$filter}";
                    $input[$field] = $this->$method($input[$field], $argsSynonyms);
                } elseif (is_callable(array($this, "sanitize_{$filterSynonym}"))) {
                    $method = "sanitize_{$filterSynonym}";
                    $input[$field] = $this->$method($input[$field], $argsSynonyms);
                } elseif (function_exists($filter)) {
                    $input[$field] = $filter($input[$field]);
                } elseif (function_exists($filterSynonym)) {
                    $input[$field] = $filterSynonym($input[$field]);
                } else {
                    throw new Exception("Sanitization filter {$filter} does not exist.");
                }
            }
        }

        return $input;
    }

    /**
     * Perform data validation against the provided ruleset.
     *
     * @param mixed $input
     *
     * @return mixed
     */
    public function runValidations(array $input)
    {
        $this->_validationErrors = array();

        foreach ($this->_filters as $field => $fieldFilters) {
            if (!isset($fieldFilters['validations'])) {
                continue;
            }

            foreach ($fieldFilters['validations'] as $fieldFilter) {
                $filter = $fieldFilter['filter'];
                $filterSynonym = $this->_getSynonym($filter);


                $args = $fieldFilter['args'];
                if (count($fieldFilter['args']) > 0) {
                    foreach($args as $i => $arg) {
                        $argsSynonyms[$i] = $this->_getSynonym($arg);
                    }                    
                } else {
                    $argsSynonyms = $args;
                }

                $validationErrorMsg = array();
                if (isset(self::$_customValidations[$filter]['callback'])) {
                    $validationPassed = call_user_func(self::$_customValidations[$filter]['callback']
                            , $field, $input, $argsSynonyms);
                    if (!$validationPassed) {
                        $validationErrorMsg[] = $fieldFilter['errormsg'];
                        $validationErrorMsg[] = self::$_customValidations[$filter]['errormsg'];
                        $validationErrorMsg[] = $this->_validationErrorMsgs['default_long'];
                    }
                } elseif (is_callable(array($this, $method = "validate_{$filter}"))
                          || is_callable(array($this, $method = "validate_{$filterSynonym}"))) {
                    //$method = "validate_{$filter}";
                    $validationPassed = $this->$method($field, $input, $argsSynonyms);
                    if (!$validationPassed) {
                        $validationErrorMsg[] = $fieldFilter['errormsg'];
                        $validationErrorMsg[] = $this->_getValidationErrorMsg($filter);
                    }
                } else {
                    $validationPassed = FALSE;
                    $validationErrorMsg[] = $this->_getValidationErrorMsg('inexistent_validation');
                }

                if (!$validationPassed) {
                    foreach($validationErrorMsg as $errorMsg) {
                        if ($errorMsg != '') {
                            $this->_validationErrors[] = [
                                    $field => $this->_readableErrorMsg($errorMsg
                                            , $field, $input, $filter, $args)];
                            break;
                        }
                    }
                }
            }
        }

        return (count($this->_validationErrors) > 0)
                    ? $this->_validationErrors
                    : TRUE;
    }

    /**
     * Overloadable method to invoke validation.
     *
     * @param array $input
     * @param $rules
     * @param $field
     *
     * @return bool
     */
    protected function shouldRunValidation(array $input, $rules, $field)
    {
        return in_array('required', $rules) || (isset($input[$field]) && trim($input[$field]) != '');
    }

    /**
     * Process the validation errors and return human readable error messages.
     *
     * @param bool   $convert_to_string = false
     * @param string $field_class
     * @param string $error_class
     *
     * @return array
     * @return string
     */
    public function get_readable_errors($convert_to_string = false, $field_class = 'valigator-field', $error_class = 'valigator-error-message')
    {
        if (empty($this->_validationErrors)) {
            return ($convert_to_string) ? null : array();
        }

        $resp = array();

        foreach ($this->_validationErrors as $e) {
            $field = ucwords(str_replace(array("_", "-"), chr(32), $e['field']));
            $param = $e['param'];

            // Let's fetch explicit field names if they exist
            if (array_key_exists($e['field'], self::$fields)) {
                $field = self::$fields[$e['field']];
            }

            switch ($e['rule']) {
                case 'mismatch' :
                    $resp[] = "There is no validation rule for <span class=\"$field_class\">$field</span>";
                    break;
                case 'validate_required' :
                    $resp[] = "The <span class=\"$field_class\">$field</span> field is required";
                    break;
                case 'validate_valid_email':
                    $resp[] = "The <span class=\"$field_class\">$field</span> field is required to be a valid email address";
                    break;
                case 'validate_max_len':
                    $resp[] = "The <span class=\"$field_class\">$field</span> field needs to be $param or shorter in length";
                    break;
                case 'validate_min_len':
                    $resp[] = "The <span class=\"$field_class\">$field</span> field needs to be $param or longer in length";
                    break;
                case 'validate_exact_len':
                    $resp[] = "The <span class=\"$field_class\">$field</span> field needs to be exactly $param characters in length";
                    break;
                case 'validate_numeric':
                    $resp[] = "The <span class=\"$field_class\">$field</span> field may only contain numeric characters";
                    break;
                case 'validate_integer':
                    $resp[] = "The <span class=\"$field_class\">$field</span> field may only contain a numeric value";
                    break;
                case 'validate_boolean':
                    $resp[] = "The <span class=\"$field_class\">$field</span> field may only contain a true or false value";
                    break;
                case 'validate_float':
                    $resp[] = "The <span class=\"$field_class\">$field</span> field may only contain a float value";
                    break;
                case 'validate_valid_url':
                    $resp[] = "The <span class=\"$field_class\">$field</span> field is required to be a valid URL";
                    break;
                case 'validate_url_exists':
                    $resp[] = "The <span class=\"$field_class\">$field</span> URL does not exist";
                    break;
                case 'validate_valid_ip':
                    $resp[] = "The <span class=\"$field_class\">$field</span> field needs to contain a valid IP address";
                    break;
                case 'validate_valid_cc':
                    $resp[] = "The <span class=\"$field_class\">$field</span> field needs to contain a valid credit card number";
                    break;
                case 'validate_valid_name':
                    $resp[] = "The <span class=\"$field_class\">$field</span> field needs to contain a valid human name";
                    break;
                case 'validate_contains':
                    $resp[] = "The <span class=\"$field_class\">$field</span> field needs to contain one of these values: ".implode(', ', $param);
                    break;
                case 'validate_contains_list':
                    $resp[] = "The <span class=\"$field_class\">$field</span> field needs to contain a value from its drop down list";
                    break;
                case 'validate_doesnt_contain_list':
                    $resp[] = "The <span class=\"$field_class\">$field</span> field contains a value that is not accepted";
                    break;
                case 'validate_street_address':
                    $resp[] = "The <span class=\"$field_class\">$field</span> field needs to be a valid street address";
                    break;
                case 'validate_date':
                    $resp[] = "The <span class=\"$field_class\">$field</span> field needs to be a valid date";
                    break;
                case 'validate_min_numeric':
                    $resp[] = "The <span class=\"$field_class\">$field</span> field needs to be a numeric value, equal to, or higher than $param";
                    break;
                case 'validate_max_numeric':
                    $resp[] = "The <span class=\"$field_class\">$field</span> field needs to be a numeric value, equal to, or lower than $param";
                    break;
                case 'validate_starts':
                    $resp[] = "The <span class=\"$field_class\">$field</span> field needs to start with $param";
                    break;
                case 'validate_extension':
                    $resp[] = "The <span class=\"$field_class\">$field</span> field can have the following extensions $param";
                    break;
                case 'validate_required_file':
                    $resp[] = "The <span class=\"$field_class\">$field</span> field is required";
                    break;
                case 'validate_equalsfield':
                    $resp[] = "The <span class=\"$field_class\">$field</span> field does not equal $param field";
                    break;
                case 'validate_min_age':
                    $resp[] = "The <span class=\"$field_class\">$field</span> field needs to have an age greater than or equal to $param";
                    break;
                default:
                    $resp[] = "The <span class=\"$field_class\">$field</span> field is invalid";
            }
        }

        if (!$convert_to_string) {
            return $resp;
        } else {
            $buffer = '';
            foreach ($resp as $s) {
                $buffer .= "<span class=\"$error_class\">$s</span>";
            }

            return $buffer;
        }
    }

    /**
     * Process the validation errors and return an array of errors with field names as keys.
     *
     * @param $convert_to_string
     *
     * @return array | null (if empty)
     */
    public function get_errors_array($convert_to_string = NULL)
    {
        if (empty($this->_validationErrors)) {
            return ($convert_to_string) ? null : array();
        }

        $resp = array();

        foreach ($this->_validationErrors as $e) {
            $field = ucwords(str_replace(array('_', '-'), chr(32), $e['field']));
            $param = $e['param'];

            // Let's fetch explicit field names if they exist
            if (array_key_exists($e['field'], self::$fields)) {
                $field = self::$fields[$e['field']];
            }

            switch ($e['rule']) {
                case 'mismatch' :
                    $resp[$field] = "There is no validation rule for $field";
                    break;
                case 'validate_max_len':
                    $resp[$field] = "The $field field needs to be $param or shorter in length";
                    break;
                case 'validate_min_len':
                    $resp[$field] = "The $field field needs to be $param or longer in length";
                    break;
                case 'validate_numeric':
                    $resp[$field] = "The $field field may only contain numeric characters";
                    break;
                case 'validate_integer':
                    $resp[$field] = "The $field field may only contain a numeric value";
                    break;
                case 'validate_float':
                    $resp[$field] = "The $field field may only contain a float value";
                    break;
                case 'validate_valid_url':
                    $resp[$field] = "The $field field is required to be a valid URL";
                    break;
                case 'validate_url_exists':
                    $resp[$field] = "The $field URL does not exist";
                    break;
                case 'validate_valid_ip':
                    $resp[$field] = "The $field field needs to contain a valid IP address";
                    break;
                case 'validate_valid_cc':
                    $resp[$field] = "The $field field needs to contain a valid credit card number";
                    break;
                case 'validate_valid_name':
                    $resp[$field] = "The $field field needs to contain a valid human name";
                    break;
                case 'validate_contains':
                    $resp[$field] = "The $field field needs to contain one of these values: ".implode(', ', $param);
                    break;
                case 'validate_contains_list':
                    $resp[$field] = "The $field field needs to contain a value from its drop down list";
                    break;
                case 'validate_doesnt_contain_list':
                    $resp[$field] = "The $field field contains a value that is not accepted";
                    break;
                case 'validate_street_address':
                    $resp[$field] = "The $field field needs to be a valid street address";
                    break;
                case 'validate_date':
                    $resp[$field] = "The $field field needs to be a valid date";
                    break;
                case 'validate_min_numeric':
                    $resp[$field] = "The $field field needs to be a numeric value, equal to, or higher than $param";
                    break;
                case 'validate_max_numeric':
                    $resp[$field] = "The $field field needs to be a numeric value, equal to, or lower than $param";
                    break;
                case 'validate_min_age':
                    $resp[$field] = "The $field field needs to have an age greater than or equal to $param";
                    break;
                default:
                    $resp[$field] = "The $field field is invalid";
            }
        }

        return $resp;
    }

    // ** ------------------------- Filters --------------------------------------- ** //

    /**
     * Translate an input string to a desired language [DEPRECIATED].
     *
     * Any ISO 639-1 2 character language code may be used
     *
     * See: http://www.science.co.il/language/Codes.asp?s=code2
     *
     * @param string $value
     * @param array  $args
     *
     * @return string
     */
    /*
    protected function filter_translate($value, $args = NULL)
    {
        $input_lang  = 'en';
        $output_lang = 'en';

        if(is_null($args))
        {
            return $value;
        }

        switch(count($args))
        {
            case 1:
                $input_lang  = $args[0];
                break;
            case 2:
                $input_lang  = $args[0];
                $output_lang = $args[1];
                break;
        }

        $text = urlencode($value);

        $translation = file_get_contents(
            "http://ajax.googleapis.com/ajax/services/language/translate?v=1.0&q={$text}&langpair={$input_lang}|{$output_lang}"
        );

        $json = json_decode($translation, true);

        if($json['responseStatus'] != 200)
        {
            return $value;
        }
        else
        {
            return $json['responseData']['translatedText'];
        }
    }
    */

    /**
     * Sanitize the string by urlencoding characters.
     *
     * Usage: '<index>' => 'urlencode'
     *
     * @param string $value
     * @param array  $args
     *
     * @return string
     */
    protected function sanitize_urlencode($value, $args = NULL)
    {
        return filter_var($value, FILTER_SANITIZE_ENCODED);
    }

    /**
     * Filter out all HTML tags except the defined basic tags.
     *
     * @param string $value
     * @param array  $args
     *
     * @return string
     */
    protected function sanitize_basichtmltags($value, $args = NULL)
    {
        return strip_tags($value, self::$_basicHTMLTags);
    }

    /**
     * Sets default value if input is NULL
     *
     * @param string $value
     * @param array  $args
     *
     * @return string
     */
    protected function sanitize_default($value, $args = NULL)
    {
        return ($args === NULL) ? $args[0] : $value;
    }

    /**
     * Sanitize the string by removing illegal characters from emails.
     *
     * Usage: '<index>' => 'sanitize_email'
     *
     * @param string $value
     * @param array  $args
     *
     * @return string
     */
    protected function sanitize_email($value, $args = NULL)
    {
        return filter_var($value, FILTER_SANITIZE_EMAIL);
    }

    /**
     * Sanitize the string by removing illegal characters from float numbers.
     *
     * @param string $value
     * @param array  $args
     *
     * @return string
     */
    protected function sanitize_float($value, $args = NULL)
    {
        return filter_var($value, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    }

    /**
     * Sanitize the string by converting HTML characters to their HTML entities.
     *
     * Usage: '<index>' => 'htmlencode'
     *
     * @param string $value
     * @param array  $args
     *
     * @return string
     */
    protected function sanitize_htmlencode($value, $args = NULL)
    {
        return filter_var($value, FILTER_SANITIZE_SPECIAL_CHARS);
    }

    /**
     * Replace noise words in a string (http://tax.cchgroup.com/help/Avoiding_noise_words_in_your_search.htm).
     *
     * Usage: '<index>' => 'noise_words'
     *
     * @param string $value
     * @param array  $args
     *
     * @return string
     */
    protected function sanitize_noisewords($value, $args = NULL)
    {
        $value = preg_replace('/\s\s+/u', chr(32), $value);

        $value = " $value ";

        $words = explode(',', self::$_enNoiseWords);

        foreach ($words as $word) {
            $word = trim($word);

            $word = " $word "; // Normalize

            if (stripos($value, $word) !== false) {
                $value = str_ireplace($word, chr(32), $value);
            }
        }

        return trim($value);
    }

    /**
     * Remove all known punctuation from a string.
     *
     * Usage: '<index>' => 'nopunctuation'
     *
     * @param string $value
     * @param array  $args
     *
     * @return string
     */
    protected function sanitization_nopunctuation($value, $args = NULL)
    {
        return preg_replace("/(?![.=$'€%-])\p{P}/u", '', $value);
    }

    /**
     * Sanitize the string by removing illegal characters from numbers.
     *
     * @param string $value
     * @param array  $args
     *
     * @return string
     */
    protected function sanitize_number($value, $args = NULL)
    {
        return filter_var($value, FILTER_SANITIZE_NUMBER_INT);
    }

    /**
     * Sanitize the string by removing any script tags.
     *
     * Usage: '<index>' => 'sanitize_string'
     *
     * @param string $value
     * @param array  $args
     *
     * @return string
     */
    protected function sanitize_string($value, $args = NULL)
    {
        return filter_var($value, FILTER_SANITIZE_STRING);
    }

    /**
     * Trims leading and trailing spaces.
     *
     * Usage: '<index>' => 'sanitize_string'
     *
     * @param string $value
     * @param array  $args
     *
     * @return string
     */
    protected function sanitize_trim($value, $args = NULL)
    {
        return ($args == NULL) ? trim($value) : trim($value, implode('', $args));
    }

    /**
     * Convert the provided numeric value to a whole number.
     *
     * @param string $value
     * @param array  $args
     *
     * @return string
     */
    protected function sanitize_whole_number($value, $args = NULL)
    {
        return intval($value);
    }

    // ** ------------------------- Validators ------------------------------------ ** //
    /**
     * Determine if the provided value contains only alpha characters.
     *
     * Usage: '<index>' => 'alphabetic'
     *
     * @param string $field
     * @param array  $input
     * @param null   $args
     *
     * @return mixed
     */
    protected function validate_alphabetic($field, $input, $args = NULL)
    {
        return (!isset($input[$field]) || empty($input[$field])
                || (preg_match(
                        '/^([a-zÀÁÂÃÄÅÇÈÉÊËÌÍÎÏÒÓÔÕÖßÙÚÛÜÝàáâãäåçèéêëìíîïðòóôõöùúûüýÿ])+$/i'
                        , $input[$field])
                    === 1));
    }

    /**
     * Determine if the provided value contains only alpha-numeric characters.
     *
     * Usage: '<index>' => 'alphanumeric'
     *
     * @param string $field
     * @param array  $input
     * @param null   $args
     *
     * @return mixed
     */
    protected function validate_alphanumeric($field, $input, $args = NULL)
    {
        return (!isset($input[$field]) || empty($input[$field])
                || (preg_match(
                        '/^([a-zÀÁÂÃÄÅÇÈÉÊËÌÍÎÏÒÓÔÕÖßÙÚÛÜÝàáâãäåçèéêëìíîïðòóôõöùúûüýÿ0-9])+$/i'
                        , $input[$field])
                    === 1));
    }

    /**
     * Determine if the provided value is a PHP accepted boolean.
     *
     * Usage: '<index>' => 'boolean'
     *
     * @param string $field
     * @param array  $input
     * @param null   $args
     *
     * @return mixed
     */
    protected function validate_boolean($field, $input, $args = NULL)
    {
        return (!isset($input[$field]) || empty($input[$field])
                || $input[$field] === TRUE || $input[$field] === FALSE);
    }

    /**
     * Determine if the input is a valid credit card number.
     *
     * See: http://stackoverflow.com/questions/174730/what-is-the-best-way-to-validate-a-credit-card-in-php
     * Usage: '<index>' => 'creditcard'
     *
     * @param string $field
     * @param array  $input
     *
     * @return mixed
     */
    protected function validate_creditcard($field, $input, $args = NULL)
    {
        if (!isset($input[$field]) || empty($input[$field])) {
            return TRUE;
        }

        $number = preg_replace('/\D/', '', $input[$field]);
        var_dump($number);

        $number_length = function_exists('mb_strlen')
                ? mb_strlen($number)
                : strlen($number);

        $parity = $number_length % 2;

        $total = 0;

        for ($i = 0; $i < $number_length; ++$i) {
            $digit = $number[$i];

            if ($i % 2 == $parity) {
                $digit *= 2;

                if ($digit > 9) {
                    $digit -= 9;
                }
            }

            $total += $digit;
        }

        return ($total % 10 == 0);
    }

    /**
     * Determine if the provided input is a valid date (ISO 8601).
     *
     * Usage: '<index>' => 'date'
     *
     * @param string $field
     * @param string $input date ('Y-m-d') or datetime ('Y-m-d H:i:s')
     * @param null   $args
     *
     * @return mixed
     */
    protected function validate_date($field, $input, $args = NULL)
    {
        return (!isset($input[$field]) || empty($input[$field])
                || (date('Y-m-d', strtotime($input[$field])) == $input[$field])
                || (date('Y-m-d H:i:s', strtotime($input[$field]))
                        == $input[$field]));
    }

    /**
     * Determine if the provided email is valid.
     *
     * Usage: '<index>' => 'valid_email'
     *
     * @param string $field
     * @param array  $input
     * @param null   $args
     *
     * @return mixed
     */
    protected function validate_email($field, $input, $args = NULL)
    {
        return (!isset($input[$field]) || empty($input[$field])
                || (filter_var($input[$field], FILTER_VALIDATE_EMAIL) !== FALSE));
    }

    /**
     * Determine if the provided value starts with param.
     *
     * Usage: '<index>' => 'starts,Z'
     *
     * @param string $field
     * @param array  $input
     *
     * @return mixed
     */
    protected function validate_endswith($field, $input, $args = NULL)
    {
        return (!isset($input[$field]) || empty($input[$field])
                || count($args) == 0
                || (strpos($input[$field], $args[0])
                    === (strlen($input[$field]) - strlen($args[0])))
                || (isset($args[1]) && $args[1] === 'caseinsensitive'
                    && (stripos($input[$field], $args[0])
                        === (strlen($input[$field]) - strlen($args[0]))))
                );
    }

    /**
     * Determine if the provided field value equals current field value.
     *
     * Usage: '<index>' => 'equalsfield,Z'
     *
     * @param string $field
     * @param string $input
     * @param string $args field to compare with
     *
     * @return mixed
     */
    protected function validate_equalsfield($field, $input, $args = NULL)
    {
        return (!isset($input[$field]) || empty($input[$field])
                || (isset($input[$args[0]]) &&
                    $input[$field] == $input[$args[0]]));
    }

    /**
     * Determine if the provided value length matches a specific value.
     *
     * Usage: '<index>' => 'exact_len,5'
     *
     * @param string $field
     * @param array  $input
     * @param null   $args
     *
     * @return mixed
     */
    protected function validate_exactlen($field, $input, $args = NULL)
    {
        return (!isset($input[$field]) || empty($input[$field])
                || (isset($args[0]) && (function_exists('mb_strlen')
                     ? (mb_strlen($input[$field]) == (int) $args[0])
                     : (strlen($input[$field]) == (int) $args[0]))));
    }

    /**
     * check the uploaded file for extension
     * for now checks onlt the ext should add mime type check.
     *
     * Usage: '<index>' => 'starts,Z'
     *
     * @param string $field
     * @param array  $input
     *
     * @return mixed
     */
    protected function validate_fileextension($field, $input, $args = NULL)
    {
        return (!isset($input[$field]) || empty($input[$field])
                || ($input[$field]['error'] === 4)
                || in_array(pathinfo($input[$field]['name'])['extension']
                        , $args));
    }

    /**
     * Determine if the provided value is a valid float.
     *
     * Usage: '<index>' => 'float'
     *
     * @param string $field
     * @param array  $input
     * @param null   $args
     *
     * @return mixed
     */
    protected function validate_float($field, $input, $args = NULL)
    {
        return (!isset($input[$field]) || empty($input[$field])
                || (filter_var($input[$field], FILTER_VALIDATE_FLOAT) !== FALSE));
    }

    /**
     * Determine if the provided field value is a valid GUID (v4)
     *
     * Usage: '<index>' => 'guidv4'
     *
     * @param string $field
     * @param string $input
     * @param string $args field to compare with
     * @return mixed
     */
    protected function validate_guidv4($field, $input, $args = NULL)
    {
        return (!isset($input[$field]) || empty($input[$field])
                || (preg_match(
                        '/\{?[A-Z0-9]{8}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{12}\}?$/'
                        , $input[$field])
                    === 1));
    }

    /**
     * Determine if the provided value is a valid IBAN (international bank
     * account number)
     *
     * Usage: '<index>' => 'iban'
     *
     * @param string $field
     * @param array  $input
     *
     * @return mixed
     */
    protected function validate_iban($field, $input, $args = NULL)
    {
        if (!isset($input[$field]) || empty($input[$field])) {
            return TRUE;
        }

        static $character = array(
            'A' => 10, 'C' => 12, 'D' => 13, 'E' => 14, 'F' => 15, 'G' => 16,
            'H' => 17, 'I' => 18, 'J' => 19, 'K' => 20, 'L' => 21, 'M' => 22,
            'N' => 23, 'O' => 24, 'P' => 25, 'Q' => 26, 'R' => 27, 'S' => 28,
            'T' => 29, 'U' => 30, 'V' => 31, 'W' => 32, 'X' => 33, 'Y' => 34,
            'Z' => 35, 'B' => 11
        );

        if (!preg_match("/\A[A-Z]{2}\d{2} ?[A-Z\d]{4}( ?\d{4}){1,} ?\d{1,4}\z/", $input[$field])) {
            return FALSE;
        }

        $iban = str_replace(' ', '', $input[$field]);
        $iban = substr($iban, 4).substr($iban, 0, 4);
        $iban = strtr($iban, $character);

        return (bcmod($iban, 97) == 1);
    }

    /**
     * Verify that a value is contained within the pre-defined value set.
     *
     * Usage: '<index>' => 'inlist:value,value,value'
     *
     * @param string $field
     * @param array  $input
     * @param null   $args
     *
     * @return mixed
     */
    protected function validate_inlist($field, $input, $args = NULL)
    {
        return (!isset($input[$field]) || empty($input[$field])
                || in_array(trim(strtolower($input[$field])), $args));
    }

    /**
     * Determine if the provided value is a valid integer.
     *
     * Usage: '<index>' => 'integer'
     *
     * @param string $field
     * @param array  $input
     * @param null   $args
     *
     * @return mixed
     */
    protected function validate_integer($field, $input, $args = NULL)
    {
        return (!isset($input[$field]) || empty($input[$field])
                || (filter_var($input[$field], FILTER_VALIDATE_INT) !== FALSE));
    }

    /**
     * Determine if the provided value is a valid IP address.
     *
     * Usage: '<index>' => 'valid_ip'
     *
     * @param string $field
     * @param array  $input
     *
     * @return mixed
     */
    protected function validate_ip($field, $input, $args = NULL)
    {
        return (!isset($input[$field]) || empty($input[$field])
                || (filter_var($input[$field], FILTER_VALIDATE_IP) !== FALSE));
    }

    /**
     * Determine if the provided value is a valid IPv4 address.
     *
     * Usage: '<index>' => 'valid_ipv4'
     *
     * @param string $field
     * @param array  $input
     *
     * @return mixed
     *
     * @see http://pastebin.com/UvUPPYK0
     */

    /*
     * What about private networks? http://en.wikipedia.org/wiki/Private_network
     * What about loop-back address? 127.0.0.1
     */
    protected function validate_ipv4($field, $input, $args = NULL)
    {
        return (!isset($input[$field]) || empty($input[$field])
                || (filter_var($input[$field], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)
                    !== FALSE));
    }

    /**
     * Determine if the provided value is a valid IPv6 address.
     *
     * Usage: '<index>' => 'valid_ipv6'
     *
     * @param string $field
     * @param array  $input
     *
     * @return mixed
     */
    protected function validate_ipv6($field, $input, $args = NULL)
    {
        return (!isset($input[$field]) || empty($input[$field])
                || (filter_var($input[$field], FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)
                    !== FALSE));
    }

    /**
     * Json validatior.
     *
     * Usage: '<index>' => 'valid_json_string'
     *
     * @param string $field
     * @param array  $input
     *
     * @return mixed
     */
    protected function validate_jsonstring($field, $input, $args = NULL)
    {
        return (!isset($input[$field]) || empty($input[$field])
                || (is_string($input[$field])
                   && is_object(json_decode($input[$field])))
                );
    }

    /**
     * Determine if the provided value length is less or equal to a specific value.
     *
     * Usage: '<index>' => 'max_len,240'
     *
     * @param string $field
     * @param array  $input
     * @param null   $args
     *
     * @return mixed
     */
    protected function validate_maxlen($field, $input, $args = NULL)
    {
        return (!isset($input[$field]) || empty($input[$field])
                || (function_exists('mb_strlen')
                     ? (mb_strlen($input[$field]) <= (int) $args[0])
                     : (strlen($input[$field]) <= (int) $args[0])));
    }

    /**
     * Determine if the provided numeric value is lower or equal to a specific value.
     *
     * Usage: '<index>' => 'max_numeric,50'
     *
     * @param string $field
     * @param array  $input
     * @param null   $args
     *
     * @return mixed
     */
    protected function validate_maxnumeric($field, $input, $args = NULL)
    {
        return (!isset($input[$field]) || empty($input[$field])
                || (is_numeric($input[$field])
                    && is_numeric($args[0])
                    && ($input[$field] <= $args[0])));
    }

    /**
     * Determine if the provided input meets age requirement (ISO 8601).
     *
     * Usage: '<index>' => 'min_age,13'
     *
     * @param string $field
     * @param string $input date ('Y-m-d') or datetime ('Y-m-d H:i:s')
     * @param string $args int
     *
     * @return mixed
     */
    protected function validate_minage($field, $input, $args = NULL)
    {
        if (!isset($input[$field]) || empty($input[$field])) {
            return TRUE;
        }

        $cdate1 = new DateTime(date('Y-m-d', strtotime($input[$field])));
        $today = new DateTime(date('d-m-Y'));

        $interval = $cdate1->diff($today);

        return ($interval->y >= $args[0]);
    }

    /**
     * Determine if the provided value length is more or equal to a specific value.
     *
     * Usage: '<index>' => 'min_len,4'
     *
     * @param string $field
     * @param array  $input
     * @param null   $args
     *
     * @return mixed
     */
    protected function validate_minlen($field, $input, $args = NULL)
    {
        return (!isset($input[$field]) || empty($input[$field])
                || (function_exists('mb_strlen')
                     ? (mb_strlen($input[$field]) >= (int) $args[0])
                     : (strlen($input[$field]) >= (int) $args[0])));
    }

    /**
     * Determine if the provided numeric value is higher or equal to a specific value.
     *
     * Usage: '<index>' => 'min_numeric,1'
     *
     * @param string $field
     * @param array  $input
     * @param null   $args
     * @return mixed
     */
    protected function validate_minnumeric($field, $input, $args = NULL)
    {
        return (!isset($input[$field]) || empty($input[$field])
                || (is_numeric($input[$field])
                    && is_numeric($args[0])
                    && ($input[$field] >= $args[0])));
    }

    /**
     * Verify that a value is NOT contained within the pre-defined value set.
     * OUTPUT: will NOT show the list of values.
     *
     * Usage: '<index>' => 'doesnt_contain_list,value;value;value'
     *
     * @param string $field
     * @param array  $input
     *
     * @return mixed
     */
    protected function validate_notinlist($field, $input, $args = NULL)
    {
        return (!isset($input[$field]) || empty($input[$field])
                || !in_array(trim(strtolower($input[$field])), $args));
    }

    /**
     * Determine if the provided value is a valid number or numeric string.
     *
     * Usage: '<index>' => 'numeric'
     *
     * @param string $field
     * @param array  $input
     * @param null   $args
     *
     * @return mixed
     */
    protected function validate_numeric($field, $input, $args = NULL)
    {
        return (!isset($input[$field]) || empty($input[$field])
                || is_numeric($input[$field]));
    }

    /**
     * Determine if the input is a valid human name [Credits to http://github.com/ben-s].
     *
     * See: https://github.com/Wixel/Valigator/issues/5
     * Usage: '<index>' => 'valid_name'
     *
     * @param string $field
     * @param array  $input
     *
     * @return mixed
     */
    protected function validate_personname($field, $input, $args = NULL)
    {
        return (!isset($input[$field]) || empty($input[$field])
                || (preg_match(
                        '/^([a-zÀÁÂÃÄÅÇÈÉÊËÌÍÎÏÒÓÔÕÖßÙÚÛÜÝàáâãäåçèéêëìíîïñðòóôõöùúûüýÿ\s\'-])+$/i'
                        , $input[$field])
                    === 1));
    }

    /**
     * Determine if the provided value is a valid phone number.
     *
     * Usage: '<index>' => 'phone_number'
     *
     * @param string $field
     * @param array  $input
     *
     * @return mixed
     *
     * Examples:
     *
     *    555-555-5555: valid
     *    5555425555: valid
     *    555 555 5555: valid
     *    1(519) 555-4444: valid
     *    1 (519) 555-4422: valid
     *    1-555-555-5555: valid
     *    1-(555)-555-5555: valid
     */
    protected function validate_phonenumber($field, $input, $args = NULL)
    {
        return (!isset($input[$field]) || empty($input[$field])
                || preg_match('/^(\d[\s-]?)?[\(\[\s-]{0,2}?\d{3}[\)\]\s-]{0,2}?\d{3}[\s-]?\d{4}$/i'
                        , $input[$field]));
    }

    /**
     * Custom regex validator.
     *
     * Usage: '<index>' => 'regex,/your-regex-expression/'
     *
     * @param string $field
     * @param array  $input
     *
     * @return mixed
     */
    protected function validate_regex($field, $input, $args = NULL)
    {
        return (!isset($input[$field]) || empty($input[$field])
                || preg_match($args[0], $input[$field]));
    }

    /**
     * Check if the specified key is present and not empty.
     *
     * Usage: '<index>' => 'required'
     *
     * @param string $field
     * @param array  $input
     * @param null   $args
     *
     * @return mixed
     */
    protected function validate_required($field, $input, $args = NULL)
    {
        return (isset($input[$field]) &&
                    ($input[$field] === FALSE || $input[$field] === 0
                    || $input[$field] === 0.0 || $input[$field] === '0'
                    || !empty($input[$field])));
    }

      /**
       * checks if a file was uploaded.
       *
       * Usage: '<index>' => 'required_file'
       *
       * @param  string $field
       * @param  array $input
       *
       * @return mixed
       */
      protected function validate_requiredfile($field, $input, $args = NULL)
      {
          return ($input[$field]['error'] === 4);
      }

    /**
     * Determine if the provided value starts with param.
     *
     * Usage: '<index>' => 'starts,Z'
     *
     * @param string $field
     * @param array  $input
     *
     * @return mixed
     */
    protected function validate_startswith($field, $input, $args = NULL)
    {
        return (!isset($input[$field]) || empty($input[$field])
                || count($args) == 0
                || (strpos($input[$field], $args[0]) === 0)
                || (isset($args[1]) && $args[1] === 'caseinsensitive'
                    && stripos($input[$field], $args[0]) === 0));
    }

    /**
     * Determine if the provided input is likely to be a street address using weak detection.
     *
     * Usage: '<index>' => 'street_address'
     *
     * @param string $field
     * @param array  $input
     *
     * @return mixed
     */
    protected function validate_streetaddress($field, $input, $args = NULL)
    {
        if (!isset($input[$field]) || empty($input[$field])) {
            return TRUE;
        }

        // Theory: 1 number, 1 or more spaces, 1 or more words
        $hasLetter = preg_match('/[a-zA-Z]/', $input[$field]);
        $hasDigit = preg_match('/\d/', $input[$field]);
        $hasSpace = preg_match('/\s/', $input[$field]);

        return ($hasLetter && $hasDigit && $hasSpace);
    }

    /**
     * Determine if the provided value is a valid URL.
     *
     * Usage: '<index>' => 'valid_url'
     *
     * @param string $field
     * @param array  $input
     * @param null   $args
     *
     * @return mixed
     */
    protected function validate_url($field, $input, $args = NULL)
    {
        return (!isset($input[$field]) || empty($input[$field])
                || (filter_var($input[$field], FILTER_VALIDATE_URL) !== FALSE));
    }

    /**
     * Determine if a URL exists & is accessible.
     *
     * Usage: '<index>' => 'url_exists'
     *
     * @param string $field
     * @param array  $input
     * @param null   $args
     *
     * @return mixed
     */
    protected function validate_urlexists($field, $input, $args = NULL)
    {
        if (!isset($input[$field]) || empty($input[$field])) {
            return TRUE;
        }

        $url = parse_url(strtolower($input[$field]));

        if (isset($url['host'])) {
            $url = $url['host'];
        }

        if (function_exists('checkdnsrr')) {
            return checkdnsrr($url);
        } else {
            return (gethostbyname($url) != $url);
        }
    }

    /**
     * Trims whitespace only when the value is a scalar.
     *
     * @param mixed $value
     *
     * @return mixed
     */
    private function trimScalar($value)
    {
        if (is_scalar($value)) {
            $value = trim($value);
        }

        return $value;
    }
} // EOC
