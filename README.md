## Valigator: Stand-alone PHP Class for Data Sanitization and Validation
Lovers of minimalism, rejoice! Valigator is a stand-alone PHP class for data sanitization and validation. It has no library dependencies, implements programmer-friendly filter syntax, and is highly flexible. Its just one single class file, include it and you are already on the move!

### Valigator, huh?
PHP API frameworks are picking up, fast. They are wonderfully minimalist, speedy, and vastly preferred over powerful yet sometimes clunky larger frameworks. To implement validations in API frameworks and projects, large vendor sources have to be installed, adding unnecessary additions to code-base and complexity. Valigator was created to address specifically that. And there's nothing that stops you from using Valigator in non-API projects. Go ahead, you'll love it!

#####    Valigator Checklist:
      ✓ PHP (5.5.\*, 5.6.\*, 7.\*)
      ✓ Simple
      ✓ Flexible
      ✓ Stand-alone
      ✓ Programmer-friendly
      ✓ Data Sanitization
      ✓ Data Validation

PS: Slim Framework 3 is groovy!

### Yet Another Vali[dg]ator
Maybe. Maybe not. Yeah, Valigator draws inspiration from some of the good, nay, great ones. And adds its own good bits too. Just to get you interested: Filter Aliasing, Multiple Arguments, Programmer-customizable Validation Error Messages and more.

## Anatomy of Valigator
### Terminology
 * **field**  
The name of the data to which filters are mapped. Typically variable names in a POST request.  A field may be mapped to no, one or multiple sanitization filters. A field may be mapped to no, one or multiple validation filters. Case-sensitive. For example, `loginId` is not the same as `loginid`.
 * **value**  
The value of the field on which the filters run. Typically mapped to variable names in a POST request. Case-sensitive unless made case-insensitive by filters running on it. For example, `email` filter doesn't care if the value passed has upper or lower case characters.
 * **filter**  
What some know as *rules*, Valigator prefers to call them *filters*. Because there are sanitization filters and validation filters, simple. Case-insensitive. For example, mis-typing `required` as `Required` makes no difference.
 * **args**  
Arguments passed to filters. You may pass no, one or multiple arguments to a filter. Case-insensitive, unless made case-sensitive by filters requiring it. For example, `startswith` filter can validate if a field value starts with characters passed as a case-sensitive argument.
 * **sanitization**  
Sanitization filters are known as simply 'sanitizations'. Sanitizations never error out so never emit any error messages.
 * **validation**  
And vanitization filters are known as simply 'validations'. A validation can either pass or fail. If it fails, it emits one error message.
 * **label**  
Human-readable label of the field which the programmer can set. If not set, labels default to upper-cased words of the field variable-name. For example, field `loginId` by default will be labelled `Login Id` (rad, isn't it!), but can be renamed by programmer to `Registered Login ID`. Variable-names of following patterns are automatically detected: *snake_case*, *camelCase*, *PascalCase* and *train-case*. Some default label examples:
  * `token` becomes `Token`
  * `project_title` becomes `Project Title`
  * `book-1` becomes `Book 1`
  * `debitCardNumber` becomes `Debit Card Number`
  * `FAQAnswer6` becomes `FAQ Answer 6`
  * `SuspenseAccount#` becomes `Suspense Account #`
 * **errormsg**  
Error messages emitted by validations (one per validation). Can be overwritten with custom error messages by the programmer per field per validation. Error messages may contain some special tags which will be replaced dynamically:  
`{field}` is replaced with label of the field  
`{value}` is replaced with value of the field  
`{filter}` is replaced with name of the filter  
`{args}` is replaced with delimited string of concatenated arguments to the filter  
`{arg1}`, ..., `{argn}` are replaced with individual arguments to the filter if they exist (note that there is no {arg0})

### The Gears and Wheels
Its easier to proceed from here with an example. Lets say we want to validate the following fields:

 * loginId: required and must be an email ID
 * name: required and must be a name
 * creditCardNumber: not mandatory, but if provided must be a valid credit card number
 * addressPINCode: not mandatory, but if provided must be a 6-digit number

Yes, you noticed it, camelCase is just my preference.

Now lets say we are receiving the following data in an array (if you are not receiving the data in an array, you will need to create an array):
``` php
<?php
// iteration 1
$inputData = [
  'salutation' => 'Mr.'                         // we aren't interested in validating this
  'loginId' => '',                              // invalid data as it is empty
                                                // notice that 'name' is missing
  'creditCardNumber' => '0001-0001-0001-0001',  // not a valid credit card number
                                                // notice that 'addressPINCode' is missing
]
```
Lets now create filters based on the data validation requirements we have, and add a few other useful things. Please read Important Notes in the code comments.
``` php
<?php
$myFilters = [
  'loginId' => [
    'label' => 'Retail User ID',                // overrides default 'Login Id'
    'sanitizations' => 'trim',                  // 'trim' is a popular filter, works exactly
                                                // like the PHP in-built trim()
    'validations' => 'required|email',          // multiple validation filters
  ],
  'name' => [
    'label' => 'Full Name',                     // overrides default 'Name'
    'sanitization' => 'trim',                   // singular 'sanitization' works too
    'validation' => 'required|personname',      // singular 'validation' works too
  ],
  'creditCardNumber' => [
                                                // label defaults to 'Credit Card Number'
    'sanitizations' => 'trim|creditcard',       // multiple sanitization filters
    'validations' => 'creditcard',              // if present, must be credit card number
  ],
  'addressPINCode' => [
    'label' => 'Indian PIN Code',               // overrides default 'Address PIN Code'
                                                // no sanitization filters here
    'validations' => 'numeric|exactlen:6',      // if present, must be numeric of exactly 6
                                                // characters length
  ],
];

// Important Notes:
//  1. 'loginId', 'name', 'creditCardNumber' and 'addressPINCode' are our **fields** of
//     interest
//  2. Field names are case-sensitive: 'loginId' is not the same as 'loginid'
//  3. Important understanding about filters:
//     a. Sanitization filters will modify input, and will never emit errors
//     b. Validation filters will never modify input, but can emit errors
//  4. The order of running filters is as follows:
//     a. All sanitizations first (if they exist) in order: 'loginId' to 'addressPINCode'
//     b. Then all validations (if they exist) in order:  'loginId' to 'addressPINCode'
//  5. If there are validation errors, they will be reported in exactly the same order, so
//     if you want some errors to be reported higher than the others, place the field higher
//  6. You can use the following keywords interchangeably, whatever makes you comfortable:
//     a. 'sanitization' <=> 'sanitizations'
//     b. 'validation' <=> 'validations'
//  7. Multiple filters can be set for each field, for sanitizations or validations, the
//     delimiter is '|'. Filters are run in the same order from left to right. Output of first
//     sanitization filter is passed to the second one, output of second to the third and so on.
//     Output of sanitization is sent to validation filters.
//  8. For most validation filters except 'required', if input is absent or empty, validation
//     will pass. Simply add 'required' filter to the beginning of validation filters if the
//     value must be present.
```

Now lets run the validator:
``` php
<?php
require 'Valigator.php';

$myValigator = new \Fishfin\Valigator($myFilters);

if ($myValigator->run($inputData)) {
  // at least one validation failed
  $myValidationErrorsArray = $myValigator->getValidationErrors();
} else {
  // all validations passed
}
```

#### Work-in-progress on documentation, but the class is ready for Production use.
