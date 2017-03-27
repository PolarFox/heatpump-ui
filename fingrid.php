<?php
#curl -X GET --header 'Accept: application/json' --header 'x-api-key: AM6jSqBWln2sFZrYQ2AHR2iXib1sHVk1cgI4Yjy2' 'https://api.fingrid.fi/v1/variable/117/events/json?start_time=2017-03-18T10%3A03%3A14Z&end_time=2017-03-25T10%3A03%3A14Z'
namespace Polaris;
class Fingrid {
	const FINGRID_API='https://api.fingrid.fi/v1';
	const TASESAHKO=117;
	private static $cache;

	public function getSevenDayAveragePrice() {
		$prices=self::getSevenDayPriceData();
		return (float)array_sum(array_map(function($var) {
			return $var['value'];
		}, $prices))/(float)count($prices);
	}

	public function getCurrentPrice() {
		$prices=self::getSevenDayPriceData();
		return $prices;
	}

	private static function getSevenDayPriceData() {
		if(self::fetchCached()['7dayavg']['expires']<time()) {
			$start_time=strftime("%Y-%m-%dT%H:%I:%SZ",strtotime('-7 days'));
			$end_time=strftime("%Y-%m-%dT%H:%I:%SZ",time());
			$prices=self::getJSON('/variable/117/events/json?start_time='.$start_time.'&end_time='.$end_time);
			self::setCache(array('7dayavg'=>array('prices'=>$prices,'expires'=>strtotime('+1 day'))));
		} else {
			$prices=self::fetchCached()['7dayavg']['prices'];
		}
		return $prices;
	}

	/**
	* Send settings array as a JSON message to heat pump controller.
	* @param array $settings
	*/
	private static function getJSON($uri) {
		$request_uri=self::FINGRID_API.$uri;
		try {
			$ch=\curl_init($request_uri);
			curl_setopt_array($ch,array(
				CURLOPT_RETURNTRANSFER=>true,
				CURLOPT_HTTPHEADER => array(
					'x-api-key: '.\Polaris\Config::get('fingrid_key'),
					'Content-Type: application/json'
				)
			));
			$response = curl_exec($ch);
			if($response === false) {
				throw new \Exception("API call failed: ".curl_error($ch));
			}
			$responseData = json_decode($response, TRUE);
			return $responseData;
		} catch(\Exception $e) {
			throw $e;
		}
	}

	private function fetchCached() {
		self::$cache=json_decode(file_get_contents('/tmp/fingrid.json'),true);
		return self::$cache;
	}

	private function setCache($vars) {
		self::fetchCached();
		foreach($vars As $key=>$value) {
			self::$cache[$key]=$value;
		}
		file_put_contents('/tmp/fingrid.json',json_encode(self::$cache));
	}
}
