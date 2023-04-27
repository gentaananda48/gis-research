<?php

// Set headers to allow cross-origin resource sharing (CORS)
header("Access-Control-Allow-Origin=>*");
header("Content-Type=>application/json; charset=UTF-8");

// Define the data to be returned as JSON
$data = array(
	"altitude" => rand(50, 60),
	"arm_height_left" => rand(100,200),
	"arm_height_right" => (rand(1,100)) + 70,
	"bearing" => 238.5,
	"flow_meter_left" => "null",
	"flow_meter_right" => "null",
	"gas" => "null",
	"homogenity" => "null",
	"id" => "10000000f445427b",
	"latitude" => round((rand(1,1000)/10000000),7) + (-4.6136509),
	"longitude" => round((rand(1,1000)/10000000),7) + (105.371544433),
	"oil" => "null",
	"pump_switch_left" => (rand(0, 1) === 1) ? "true" : "false",
	"pump_switch_main" => (rand(0, 1) === 1) ? "true" : "false",
	"pump_switch_right" => (rand(0, 1) === 1) ? "true" : "false",
	"speed" => rand(0,10),
	"tank_level" => "null",
	"temperature_left" => (rand(1,100)/10) + 30,
	"temperature_right" => (rand(1,100)/10) + 30,
	"utc_timestamp" => time()
);

// Convert the data to JSON format
$json = json_encode($data);

// Return the JSON response
echo $json;
