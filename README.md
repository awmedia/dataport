dataport
========

Framework for fast, flexible and reusable data conversion.

- Read formats (xml, csv, json, array, etc.) from various sources (file, db, mongo, stream, webservice, etc.)
- Mapper to map source columns to destination columns
- Processors to convert or format columns values
- Filters to allow or disallow rows
- Write formats to various destinations (file, db, mongo, array, webservice, etc.)



Tmp docs:
$dataPorter->getFilters()->add(Filter::factory('quantity', function($value) { return $value > 0; }));
$dataPorter->getFilters()->add(Filter::factory('quantity', 'filter_var', array(':value', FILTER_VALIDATE_INT, array('options'=>array('min_range'=>0, 'max_range'=>100)))));
