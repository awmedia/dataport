<?php

/**
 * DataPorter example page
 * @Exampe: convert format from array to csv.
 * With Mapper and Processor.
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Aw\DataPort\DataPorter,
    Aw\DataPort\Filter,
    Aw\DataPort\Reader,
    Aw\DataPort\Reader\Adapter\ArrayReaderAdapter,
    Aw\DataPort\Mapper,
    Aw\DataPort\Writer,
    Aw\DataPort\Writer\Adapter\CsvFileWriterAdapter;

$data = array(
    array('name'=>'Game of Thrones', 'year'=>'2011', 'votes'=>3963, 'score'=>8.8),
    array('name'=>'Band of Brothers', 'year'=>'2001', 'votes'=>1434, 'score'=>8.7),
    array('name'=>'The Sopranos', 'year'=>'1999', 'votes'=>605, 'score'=>8.6),
    array('name'=>'Breaking Bad', 'year'=>'2008', 'votes'=>2951, 'score'=>8.6),
    array('name'=>'Sherlock', 'year'=>'2010', 'votes'=>1182, 'score'=>8.5)
);

// Settings
$targetFilename = 'formatTarget.csv';

// Create DataPorter instance
$dataPorter = new DataPorter();

// Create Reader
$reader = new Reader(new ArrayReaderAdapter($data));

// Create Mapper
$mapper = new Mapper(array(
    'title' => 'name',
    'started' => 'year',
    'voted' => 'votes',
    'score' => 'score'
));

// Create processors.
// Using Callables is more easy and flexible.
// For reusable processores extend the Processer class.
$dataPorter->getProcessors()->add(function($row) {
    $row['voted'] *= 2;
    $row['title'] = trim($row['title']) . ' | ported';
    return $row;
});

// Create Writer
$writerAdapter = new CsvFileWriterAdapter($targetFilename);
$writer = new Writer($writerAdapter);

// Configure DataPorter
$dataPorter->setReader($reader)
           ->setMapper($mapper)
           ->setWriter($writer);
           

// Start session
$result = $dataPorter->port();

echo '<pre>';
print_r($result);
print_r(file_get_contents($targetFilename));