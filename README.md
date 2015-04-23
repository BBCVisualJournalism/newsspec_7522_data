## Affordability calculator data
Compiles data for the [affordability project](http://www.bbc.co.uk/news/business-23234033)

### How to run
1. Open ./scripts and update the file datacompile.php
```php
//output json data file
const JSON_DATA_FILE_TEST = '../uk50_v11.json';
//output js data file
const JS_DATA_FILE_TEST = '../uk50_v11.js';
//update location of your data CSV file
const DATA_FILE_SOURCE = '~/Downloads/toendMar2015withnulls.csv';
```

2. Run datacompile.php
```
php ./scripts/datacompile.php
```

### Known issues
Before compiling data file ensure the following are correct;
- Glasgow City should have ID of S12...43
- East Dunbartonshire should have ID of S12...09
