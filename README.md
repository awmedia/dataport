dataport
========

Library for fast and abstract data conversion.



Tmp docs:


$dataPorter->getFilters()->add(Filter::factory('quantity', function($value) { return $value > 0; }));
$dataPorter->getFilters()->add(Filter::factory('quantity', 'filter_var', array(':value', FILTER_VALIDATE_INT, array('options'=>array('min_range'=>0, 'max_range'=>100)))));
