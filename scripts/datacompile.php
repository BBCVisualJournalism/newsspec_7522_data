<?php
ini_set('memory_limit', '2000M');

// require 'Config.php';

class HousingAffordability
{
	const GEOGRAPHY_SOURCE_FILE = '../sourcedata/uk50.json';
	const JSON_DATA_FILE_TEST = '../uk50_v14.json';
	const JS_DATA_FILE_TEST = '../uk50_v14.js';
    const DATA_FILE_SOURCE = '/Users/ashtoc03/Downloads/June2016affordabilityMay2016_withnulls.csv';

	public function __construct() {
		$this->compileDataFile();
	}

	public function getDataAsArray() {
		$dataArray = array();
		$fileHandle = $this->getCSVDataFromUrl(self::DATA_FILE_SOURCE);

		if ($fileHandle !== false) {
			while (($data = fgetcsv($fileHandle, 20000, ',')) !== false) {
				$dataArray[] = $data;
			}
		} else {
			$error = error_get_last();
			throw new Exception($error['message']);
		}

		return $dataArray;
	}

	protected function getCSVDataFromUrl($datafile) {
		echo "... read data from csv file\n";
		return @fopen($datafile, 'r');
	}

	public function compileDataFile($geographySourceFile = self::GEOGRAPHY_SOURCE_FILE) {
		try {
			echo "... add data to topojson: $geographySourceFile\n";
			$geographiesData = json_decode(file_get_contents(dirname(__FILE__) . '/' . $geographySourceFile));
			$housingData = $this->getOwnershipByPropertyType();

			foreach($geographiesData->objects->raw->geometries as $geography) {
				$code = $geography->properties->code;
				if ($housingData[$code]) {
					$geography->properties->data = $housingData[$code];
				} else {
					var_dump('Problem processing ' . $geography->properties->name . ', location code: ' . $geography->properties->code);
				}
			}

			file_put_contents(dirname(__FILE__) . '/' . self::JSON_DATA_FILE_TEST, 'housingDataCallback(' . json_encode($geographiesData, true) . ');');
			file_put_contents(dirname(__FILE__) . '/' . self::JS_DATA_FILE_TEST, 'define(' . json_encode($geographiesData, true) . ');');

			echo json_encode(array('success' => 'successful update'), true);
		} catch (Exception $e) {
			echo json_encode(array('error' => $e->getMessage()), true);
		}
	}

	public function getOwnershipByPropertyType() {
		echo "... create properties data object\n";
		$ownershipByPropertyTypeData = array();

		$dataArray = $this->getDataAsArray();
		array_shift($dataArray);

		foreach($dataArray as $council) {
			$councilCode = trim($council[1]);
			$bedrooms = ((int) $council[4]) - 1;

			$ownershipByPropertyTypeData[$councilCode][$bedrooms] = array(
					'lq' => array($this->cleanUpValue($council[11]), $this->cleanUpValue($council[5])),
					'mq' => array($this->cleanUpValue($council[12]), $this->cleanUpValue($council[6])),
					'uq' => array($this->cleanUpValue($council[13]), $this->cleanUpValue($council[7]))
			);
		}

		return $ownershipByPropertyTypeData;
	}

	public function cleanUpValue($str) {
		if ((strpos(strtolower($str), "ull") > 0) || empty($str)) {
			return "null";
		}

// 		$str = preg_replace('/[£,?,å£]/', '', $str);
		$str = preg_replace('/[^0-9]/', '', $str);
// 		var_dump((float) $str); exit();
		return (int) $str;
	}

}

$haff = new HousingAffordability();

?>