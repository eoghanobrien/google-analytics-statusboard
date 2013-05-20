<?php

// Add the zend library to the include path
set_include_path(realpath(dirname(__FILE__)).'/vendor/zend/gdata/library/');

// Start the autoloader
require 'vendor/autoload.php';

$email     = 'YOUR EMAIL ADDRESS';
$password  = 'YOUR PASSWORD';
$profileID = 'YOUR PROFILE ID';

$service   = Zend_Gdata_Analytics::AUTH_SERVICE_NAME;
$client    = Zend_Gdata_ClientLogin::getHttpClient($email, $password, $service);
$analytics = new Zend_Gdata_Analytics($client);

$startDate = strtotime('-6 days');
$endDate   = strtotime('today');

$query = $analytics->newDataQuery()
				   ->setProfileId($profileID)
				   ->addMetric(Zend_Gdata_Analytics_DataQuery::METRIC_VISITS)
				   ->addMetric(Zend_Gdata_Analytics_DataQuery::METRIC_NEW_VISITS)
				   ->addMetric(Zend_Gdata_Analytics_DataQuery::METRIC_VISITORS)
				   ->addDimension(Zend_Gdata_Analytics_DataQuery::DIMENSION_DAY)
				   ->setStartDate(date('Y-m-d', $startDate))
				   ->setEndDate(date('Y-m-d', $endDate))
				   ->setMaxResults(10);
  
$result = $analytics->getDataFeed($query);
$days   = count($result);

$graphVisits    = array();
$graphNewVisits = array();
$graphVisitors  = array();

foreach($result as $k => $row)
{
	if ($k === 0) {
		$date = date('jS F', $startDate);
	}
	elseif ($k === $days) {
		$date = date('jS F', $endDate);
	}
	else {
		$date = date('jS F', strtotime(($k - 6).' days'));
	}
	
	$graphVisits[] = array(
		'title' => $date,
		'value' => $row->getMetric(Zend_Gdata_Analytics_DataQuery::METRIC_VISITS)->getValue()
	);
	$graphNewVisits[] = array(
		'title' => $date,
		'value' => $row->getMetric(Zend_Gdata_Analytics_DataQuery::METRIC_NEW_VISITS)->getValue()
	);
	$graphVisitors[] = array(
		'title' => $date,
		'value' => $row->getMetric(Zend_Gdata_Analytics_DataQuery::METRIC_VISITORS)->getValue()
	);
}

$graph = array(
	"graph" => array(
		"title" => "Blog | Visitors This Week",
		"type"  => "line",
		"total" => true,
		"refreshEveryNSeconds" => 300,
		"xAxis" => array(
			"showEveryLabel" => true,
			"minValue" => date('jS F', $startDate),
			"maxValue" => date('jS F', $endDate)
		),
		"datasequences" => array(
			array(
				"title"      => "Total Hits",
				"datapoints" => $graphVisits,
				"color"      => "green"
			),
			array(
				"title"      => "New",
				"datapoints" => $graphNewVisits,
				"color"      => "orange"
			),
			array(
				"title"      => "Unique",
				"datapoints" => $graphVisitors,
				"color"      => "yellow"
			)
		)
	)
);

header('Content-Type: application/json');
exit(
	json_encode($graph/*, JSON_PRETTY_PRINT*/)
);