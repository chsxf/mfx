<?php
/**
 * Secured download handler
 * 
 * @author Christophe SAUVEUR <chsxf.pro@gmail.com>
 */

namespace chsxf\MFX;

use chsxf\MFX\DataValidator\FieldType;
use chsxf\MFX\DataValidator\Filter\RegExp;
use chsxf\MFX\DataValidator\Filter\ExistsInDB;

/**
 * Secured downloads handling class
 */
class SecuredDownload implements IRouteProvider {
	
	/**
	 * @mfx_subroute
	 * 
	 * @param array $params
	 */
	public static function get(array $params) {
		$validator = self::__buildInputValidator();
		if (!$validator->validate($params))
			CoreManager::dieWithStatusCode(400);
		
		// Add to download log
		$dbm = DatabaseManager::open('__mfx');
		$dbm->beginTransaction();
		// -- Key ID
		$sql = "SELECT `sdk`.`secured_download_key_id`, `sd`.`secured_download_path`
					FROM `mfx_secured_downloads_keys` AS `sdk`
					LEFT JOIN `mfx_secured_downloads` AS `sd`
						ON `sd`.`secured_download_id` = `sdk`.`secured_download_id`
					WHERE `sdk`.`secured_download_key` = ?";
		$keyData = $dbm->getRow($sql, DBM_OBJECT, $validator['0']);
		if ($keyData === false) {
			$dbm->rollBack();
			CoreManager::dieWithStatusCode(500);
		}
		// -- Checking path
		$filePath = Config::get('secured_downloads.root_url') . $keyData->secured_download_path;
		if (!file_exists($filePath)) {
			$dbm->rollBack();
			CoreManager::dieWithStatusCode(500);
		}
		// -- Update download count
		$sql = "UPDATE `mfx_secured_downloads_keys`
					SET `secured_download_key_count` = `secured_download_key_count` + 1
					WHERE `secured_download_key_id` = ?";
		if ($dbm->exec($sql, $keyData->secured_download_key_id) === false) {
			$dbm->rollBack();
			CoreManager::dieWithStatusCode(500);
		}
		// -- Add log entry
		$sql = "INSERT INTO `mfx_secured_downloads_log` (`secured_download_key_id`, `secured_download_log_ip`) VALUE (?, ?)";
		$ip = empty($_SERVER['REMOTE_ADDR']) ? NULL : $_SERVER['REMOTE_ADDR'];
		if ($dbm->exec($sql, $keyData->secured_download_key_id, $ip) === false) {
			$dbm->rollBack();
			CoreManager::dieWithStatusCode(500);
		}
		$dbm->commit();
		
		// Sending file
		CoreManager::flushAllOutputBuffers();
		$fileinfo = pathinfo($filePath);
		header('Content-Type: ' . FileTools::mimeTypeFromExtension($fileinfo['extension']));
		header('Content-Disposition: attachment; filename=' . $fileinfo['basename']);
		header('Content-Length: ' . filesize($filePath));
		readfile($filePath);
		exit();
	}
	
	private static function __buildInputValidator() {
		$validator = new DataValidator();
		$field = $validator->createField('0', new FieldType());
		$field->addFilter(new RegExp(RegExp::REGEX_LCALPHANUMERIC));
		$field->addFilter(new ExistsInDB('mfx_secured_downloads_keys', 'secured_download_key', NULL, '__mfx'));
		return $validator;
	}
	
}