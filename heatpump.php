<?php
namespace Polaris;
class Heatpump {
	const HEATPUMP_URI = 'http://127.0.0.1:8080';
	const VARIABLES = [
		'hvac_modes'=>[
			['value'=>'heat','label'=>'Lämmitys'],
			['value'=>'cool','label'=>'Viilennys'],
			['value'=>'dry','label'=>'Kuivaus'],
			['value'=>'auto','label'=>'Automaatti']
		],
		'vane_modes'=>[
			['value'=>'upend','label'=>'1'],
			['value'=>'up','label'=>'2'],
			['value'=>'middle','label'=>'3'],
			['value'=>'down','label'=>'4'],
			['value'=>'downend','label'=>'5'],
			['value'=>'auto','label'=>'Automaatti'],
			['value'=>'swing','label'=>'Heiluri']
		]
	];
	public function __construct() {

	}

	public function getStatus() {
		try {
			return array_merge(json_decode(file_get_contents(self::HEATPUMP_URI.'/api/status'),true),self::VARIABLES);
		} catch(\Exception $e) {
			throw $e;
		}
	}

	public function setTemperature($temp=20) {
		if(!is_int($temp)) {
			throw new \Exception('Temperature must be integer.');
		}
		self::sendSettings(array('temp'=>$temp));
	}

	public function setMode($mode='heat') {
		if($mode=='heat'||$mode=='cool'||$mode=='dry'||$mode=='auto') {
			return self::sendSettings(array('hvac_mode'=>$mode));
		}
		throw new \Exception('Unknown mode');
	}

	public function setVane($vane=0) {
		return self::sendSettings(array('vane'=>$vane));
		throw new \Exception('Unknown vane direction');
	}

	public function setPower($power) {
		if($power) {
			$power=true;
		}
		return self::sendSettings(array('on'=>(bool)$power));
	}

	/**
	* Send settings array as a JSON message to heat pump controller.
	* @param array $settings
	*/
	private static function sendSettings($settings) {
		$defaults=array('apply'=>true);
		$message=json_encode(array_merge($settings,$defaults));
		try {
			$ch=\curl_init(self::HEATPUMP_URI.'/api/set');
			curl_setopt_array($ch,array(
				CURLOPT_POST=>true,
				CURLOPT_RETURNTRANSFER=>true,
				CURLOPT_HTTPHEADER => array(
					'Authorization: '.'hips',
					'Content-Type: application/json'
				),
				CURLOPT_POSTFIELDS => $message
			));
			$response = curl_exec($ch);
			if($response === false) {
				throw \Exception("API call failed.");
			}
			$responseData = json_decode($response, TRUE);
			return $responseData;
		} catch(\Exception $e) {
			throw $e;
		}
	}
}
