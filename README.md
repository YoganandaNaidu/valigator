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
The name of the data to which filters are mapped. Typically variable names in a POST request.  A field may be mapped to no, one or multiple sanitization filters. A field may be mapped to no, one or multiple validation filters. Case-sensitive. For example, `username` is not the same as `userName`.
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
Human-readable label of the field which the programmer can set. If not set, labels default to upper-cased words of the field variable-name. For example, field `userName` by default will be labelled `User Name` (rad, isn't it!), but can be renamed by programmer to `Login ID`. Variable-names of following patterns are automatically detected: *snake_case*, *camelCase*, *PascalCase* and *train-case*. Some default label examples:
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

 * userName: required and must be an email ID
 * firstName: required and must be a name
 * lastName: required and must be a name
 * creditCardNumber: not mandatory, but if provided must be a valid credit card number
 * addressPINCode: not mandatory, but if provided must be a 6-digit number

Yes, you noticed it, camelCase is just my preference.

Now lets say we are receiving the following data in an array (if you are not receiving the data in an array, you will need to create an array):
``` php
// iteration 1
$inputData = [
  'salutation' => 'Mr.'                         // we aren't interested in validating this
  'userName' => '',                             // invalid data as it is empty
  'firstName' => '123',                         // invalid data as it isn't a name
                                                // notice that lastName is missing
  'creditCardNumber' => '0001-0001-0001-0001',  // not a valid credit card number
                                                // notice that addressPINCode is missing
]
```
Lets now create filters based on the data validation requirements we have, and add a few other useful things:
``` php
$myFilters = [
  'userName' => [
    'label' => 'Retail User Name',              // will overwrite default 'User Name'
  ],
  'firstName' => [
                                                // label will default to 'First Name'
  ],
  'lastName' => [
    'label' => 'Surname',                       // will overwrite default 'Last Name'
  ],
  'creditCardNumber' => [
                                                // label will default to 'Credit Card Number'
  ],
  'addressPINCode' => [
    'label' => 'Indian PIN Code',               // will overwrite default 'Address PIN Code'
  ],
];

// Important Notes:
//  1. 'userName', 'firstName', 'lastName', 'creditCardNumber' and 'addressPINCode' are our
//     **fields** of interest
//  2. Field names are case-sensitive: 'userName' is not the same as 'username'
//  3. The order of running filters is as follows:
//     a. All sanitizations first (if they exist) in order: 'userName' to 'addressPINCode'
//     b. Then all validations (if they exist) in order:  'userName' to 'addressPINCode'
//  4. If there are validation errors, they will be reported in exactly the same order, so
//     if you want some errors to be reported higher than the others, place the field higher
```

#### Work-in-progress on documentation, but the class is ready for Production use.
