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
jimport('joomla.application.component.view');

/**
 * Class CmcViewCpanel
 *
 * @since  1.3
 */
class CmcViewCpanel extends JViewLegacy
{
	/**
	 * Displays the view
	 *
	 * @param   string  $tpl  - custom template
	 *
	 * @return mixed|void
	 */
	public function display($tpl = null)
	{
		$this->checkAndFixDatabase();
		$this->addToolbar();
		parent::display($tpl);
	}

	/**
	 * Ads the toolbar buttons
	 *
	 * @return void
	 */
	public function addToolbar()
	{
		JToolBarHelper::title(JText::_('COM_CMC_CPANEL'), 'cpanel');
		JToolBarHelper::preferences('com_cmc');
	}

	/**
	 * Gets the account details from mailchimp
	 *
	 * @return mixed
	 */
	public function getAccountDetails()
	{
		$cache = JFactory::getCache('mod_ccc_cmc_mailchimp', 'output');
		$cache->setCaching(true);
		$details = $cache->get('details');

		if (!$details)
		{
			$chimp = new cmcHelperChimp;
			$data = $chimp->getAccountDetails();
			$details = serialize($data);
			$cache->store(($details), 'details');
		};

		return unserialize($details);
	}

	/**
	 * Checks the database for missing / outdated tables and installs or
	 * updates the database using the SQL xml file if necessary.
	 *
	 * @return	void
	 */
	public function checkAndFixDatabase()
	{
		$dbInstaller = new CompojoomDatabaseInstaller(
			array(
			'dbinstaller_directory'	=> JPATH_ADMINISTRATOR . '/components/com_cmc/sql/xml'
			)
		);

		$dbInstaller->updateSchema();
	}
}
