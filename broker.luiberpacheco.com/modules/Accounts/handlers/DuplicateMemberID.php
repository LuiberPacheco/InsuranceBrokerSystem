<?php
/**
 * Short name duplicate checker handler field.
 *
 * @package Handler
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
/**
 * MemberID duplicate checker handler class.
 */
class Accounts_DuplicateMemberID_Handler /*Se crea en la base de datos la clase*/
{
	/** @var array List of fields for verification */
	const FIELDS = [
		'Accounts' => ['accountname'],
	];

	/**
	 * EditViewPreSave handler function.
	 *
	 * @param App\EventHandler $eventHandler
	 */
	public function editViewPreSave(App\EventHandler $eventHandler)
	{
		$recordModel = $eventHandler->getRecordModel();
		$response = ['result' => true];
		$values = [];
		foreach (self::FIELDS[$recordModel->getModuleName()] as $fieldName) {
			$fieldModel = $recordModel->getModule()->getFieldByName($fieldName);
			if ($fieldModel->isViewable() && ($value = $recordModel->get($fieldName))) {
				$values[] = $value;
			}
		}
		foreach (self::FIELDS as $moduleName => $fields) {
			$queryGenerator = new \App\QueryGenerator($moduleName);
			$queryGenerator->setStateCondition('All');
			$queryGenerator->setFields(['id'])->permissions = false;
			if ($moduleName === $recordModel->getModuleName() && $recordModel->getId()) {
				$queryGenerator->addCondition('id', $recordModel->getId(), 'n');
			}
			foreach ($fields as $fieldName) {
				$queryGenerator->addCondition($fieldName, $values, 'e', false);
			}
			if ($queryGenerator->createQuery()->exists()) {
				$response = [
					'result' => false,
					'hoverField' => reset($fields),
					'message' => App\Language::translateArgs(
						'LBL_DUPLICATE_FIELD_VALUE',
						$recordModel->getModuleName(),
						\App\Language::translate('FL_DUPLICATE_MEMBERID', $moduleName) . ': ' . implode(',', $values)
					),
				];
				break;
			}
		}
		return $response;
	}
}
