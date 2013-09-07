<?php
/**
 * @package    Cmc
 * @author     Yves Hoppe <yves@compojoom.com>
 * @date       06.09.13
 *
 * @copyright  Copyright (C) 2008 - 2013 compojoom.com . All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

JLoader::discover('cmcHelper', JPATH_ADMINISTRATOR . '/components/com_cmc/helpers/');

/**
 * Class PlgUserCmc
 *
 * @since  1.4
 */
class PlgUserCmc extends JPlugin
{
	/**
	 * Prepares the data
	 *
	 * @param   string $context - the context
	 * @param   object $data - the data object
	 *
	 * @return bool
	 */

	function onContentPrepareData($context, $data)
	{
		// Check we are manipulating a valid form.
		if (!in_array(
			$context, array(
			'com_users.profile', 'com_users.user', 'com_users.registration', 'com_admin.profile')
		))
		{
			return true;
		}

		if (is_object($data))
		{
			// Extend form
		}

		return true;
	}

	/**
	 * Prepares the form
	 *
	 * @param   string  $form  - the form
	 * @param   object  $data  - the data object
	 *
	 * @return bool
	 */

	function onContentPrepareForm($form, $data)
	{
		if (!($form instanceof JForm))
		{
			$this->_subject->setError('JERROR_NOT_A_FORM');

			return false;
		}

		// Check we are manipulating a valid form.
		$name = $form->getName();

		if (!in_array(
			$name, array('com_admin.profile', 'com_users.user',
				'com_users.profile', 'com_users.registration'
			)
		))
		{
			return true;
		}

		$lang = JFactory::getLanguage();
		$lang->load('plg_user_cmc', JPATH_ADMINISTRATOR);

		$listid = $this->params->get('listid', "");
		$interests = $this->params->get('interests', '');
		$fields = $this->params->get('fields', '');

		$renderer = CmcHelperRegistrationrender::getInstance();
		$renderer->phoneFormat = $this->params->get("phoneFormat", "inter");
		$renderer->dateFormat = $this->params->get("dateFormat", "%Y-%m-%d");
		$renderer->address2 = $this->params->get("address2", 0);

		$formcode = "<form>\n";
		$formcode .= "<fields name=\"cmc\">\n";
		$formcode .= "<fieldset name=\"cmc\" label=\"PLG_USER_CMC_CMC_LABEL\">\n";

		// Adding Newsletter Checkbox
		$formcode .= '
					<field
						name="newsletter"
						type="checkbox"
						id="newsletter"
						description="PLG_USER_CMC_NEWSLETTER_DESC"
						value="1"
						default="0"
						label="PLG_USER_CMC_NEWSLETTER"
					/>
					';



		// Render Content
		$formcode .= $renderer->renderForm(
			$this->params->get('intro-text', ""),
			$this->params->get('outro-text-1', ""), $this->params->get('outro-text-2', ""),
			$fields, $interests, $listid, _CPLG_JOOMLA
		);



		$formcode .= "</fieldset>\n";
		$formcode .= "</fields>\n";
		$formcode .= "</form>";

		//var_dump($formcode);
		//die();

		// Inject fields into the form
		$form->load($formcode, false);

		return true;
	}

	/**
	 * Prepares the form
	 *
	 * @param   string   $user   - the not saved user obj
	 * @param   boolean  $isNew  - is the user new
	 * @param   object   $data   - the data object
	 *
	 * @return   void
	 */

	function onUserBeforeSave($user, $isNew, $data)
	{
		// Tab
	}


	/**
	 * Prepares the form
	 *
	 * @param   object   $data    - the users data
	 * @param   boolean  $isNew   - is the user new
	 * @param   object   $result  - the db result
	 * @param   string   $error   - the error message
	 *
	 * @return   boolean
	 */

	function onUserAfterSave($data, $isNew, $result, $error)
	{
		$userId = JArrayHelper::getValue($data, 'id', 0, 'int');

		if ($userId && $result && isset($data['cmc']) && (count($data['cmc'])))
		{
			// Save data
			var_dump($data);

			if ($data["cmc"]["newsletter"] != "1" && $isNew != false)
			{
				// Abort if Newsletter is not checked
				return true;
			}

			if ($data["block"] == 1)
			{
				// Temporary save user
				CmcHelperRegistration::saveTempUser($data["id"], $data["cmc"], _CPLG_JOOMLA);
			}
			else
			{
				if (!$isNew)
				{
					// Activate User to Mailchimp
					CmcHelperRegistration::activateTempUser(JFactory::getUser($data["id"]));
				}
				else
				{
					// Directly activate user
					CmcHelperRegistration::activateDirectUser(
						JFactory::getUser($data["id"]), $data["cmc"], _CPLG_JOOMLA
					);
				}
			}

			var_dump($isNew);
			var_dump($result);
			die("12");
		}

		return true;
	}


	/**
	 * Remove all Cmc information for the given user ID
	 *
	 * Method is called after user data is deleted from the database
	 *
	 * @param   array    $user     - Holds the user data
	 * @param   boolean  $success  - True if user was succesfully stored in the database
	 * @param   string   $msg      - Message
	 *
	 * @return boolean
	 */

	function onUserAfterDelete($user, $success, $msg)
	{
		if (!$success) {
			return false;
		}

		$userId = JArrayHelper::getValue($user, 'id', 0, 'int');

		if ($userId) {
			// Delete User from mailing list?
		}

		return true;
	}
}