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

## Terminology
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
Human-readable rename of the field which the programmer can set. If not set, labels default to upper-cased words of the field variable-name. For example, field `userName` by default will be labelled `User Name` (rad, isn't it!), but can be renamed by programmer to `Log-in ID`. Variable-names of following patterns are automatically detected: *snake_case*, *camelCase*, *PascalCase* and *train-case*.
 * **errormsg**  
Error messages emitted by validations (one per validation). Can be overwritten with custom error messages by the programmer per field per validation. Error messages may contain some special tags which will be replaced dynamically:  
`{field}` is replaced with label of the field  
`{value}` is replaced with value of the field  
`{filter}` is replaced with name of the filter  
`{args}` is replaced with delimited string of concatenated arguments to the filter  
`{arg1}`, ..., `{arg2}` are replaced with individual arguments to the filter if they exist (note that there is no {arg0})

#### Work In Progress on documentation...
