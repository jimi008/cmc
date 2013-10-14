<?php
/**
 * @package    Cmc
 * @author     DanielDimitrov <daniel@compojoom.com>
 * @date       06.09.13
 *
 * @copyright  Copyright (C) 2008 - 2013 compojoom.com . All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.controllerlegacy');

/**
 * Class CmcControllerSubscription
 *
 * @since  1.0
 */
class CmcControllerSubscription extends JControllerLegacy
{
	/**
	 * Save the subscription
	 *
	 * @return void
	 */
	public function save()
	{
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$db = JFactory::getDBO();
		$query = $db->getQuery(true);

		$chimp = new cmcHelperChimp;

		$input = JFactory::getApplication()->input;
		$form = $input->get('jform', '', 'array');

		if (isset($form['groups']))
		{
			foreach ($form['groups'] as $key => $group)
			{
				$mergeVars[$key] = $group;
			}
		}

		if (isset($form['interests']))
		{
			foreach ($form['interests'] as $key => $interest)
			{
				// Take care of interests that contain a comma (,)
				if (is_array($interest))
				{
					array_walk($interest, create_function('&$val', '$val = str_replace(",","\,",$val);'));
					$mergeVars['GROUPINGS'][] = array('id' => $key, 'groups' => implode(',', $interest));
				}
				else
				{
					$mergeVars['GROUPINGS'][] = array('id' => $key, 'groups' => $interest);
				}
			}
		}

		$mergeVars['OPTINIP'] = $_SERVER['REMOTE_ADDR'];

		$listId = $form['listid'];
		$email = $mergeVars['EMAIL'];

		// Check if the user is in the list already
		$userlists = $chimp->listsForEmail($email);

		if ($userlists && in_array($listId, $userlists))
		{
			$updated = true;
		}
		else
		{
			$updated = false;
		}

		$chimp->listSubscribe($listId, $email, $mergeVars, 'html', true, true, true, false);


		if ($chimp->errorCode)
		{
			$response['html'] = $chimp->errorMessage;
			$response['error'] = true;
		}
		else
		{
			if (!$updated)
			{
				$query->insert('#__cmc_users')->columns('list_id,email,merges')
					->values($db->quote($listId) . ',' . $db->quote($email) . ',' . $db->quote(json_encode($mergeVars)));
				$db->setQuery($query);
				$db->execute();
			}
			else
			{
				$query->update('#__cmc_users')->set('merges = ' . $db->quote(json_encode($mergeVars)))
					->where('email = ' . $db->quote($email) . ' AND list_id = ' . $db->quote($listId));
				$db->setQuery($query);
				$db->execute();
			}

			$response['html'] = ($updated) ? 'updated' : 'saved';
			$response['error'] = false;
		}

		echo json_encode($response);
	}
}
