<?php
/**
 * @package    Cmc
 * @author     Yves Hoppe <yves@compojoom.com>
 * @author     Daniel Dimitrov <daniel@compojoom.com>
 * @date       06.09.13
 *
 * @copyright  Copyright (C) 2008 - 2013 compojoom.com . All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Class CmcHelperRegistrationrender
 *
 * @since  1.4
 */
class CmcHelperXmlbuilder
{
	public $dateFormat, $phoneFormat, $address2;

	private static $instance = null;

	/**
	 * Gets a instance (SINGLETON) of this class
	 *
	 * @return CmcHelperXmlbuilder
	 */
	public static function getInstance()
	{
		if (null === self::$instance)
		{
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Renders the Plugin form
	 *
	 * @param   array   $fields     - The fields array
	 * @param   array   $interests  - The interests
	 * @param   string  $listid     - the list id
	 *
	 * @return string
	 */
	public function renderForm($fields, $interests, $listid)
	{
		$html = "<form>";
		$html .= '<fields name="cmc">';

		if (is_array($fields))
		{
			$html .= '<fieldset name="groups">';

			foreach ($fields as $f)
			{
				$field = explode(';', $f);
				$html .= $this->createXmlField($field);
			}

			$html .= '</fieldset>';
		}

		if (is_array($interests))
		{
			$html .= '<fieldset name="interests">';

			foreach ($interests as $i)
			{
				$interest = explode(';', $i);
				$groups = explode('####', $interest[3]);

				switch ($interest[1])
				{
					case 'checkboxes':
						$html .= '<field type="checkboxes" name="interests][' . $interest[0] . '"
								class="submitMerge inputbox cmc-checkboxes"
								labelclass="form-label cmc-label"
								label="' . $interest[2] . '"
								id="' . $interest[0] . '" >';

						foreach ($groups as $g)
						{
							$o = explode('##', $g);
							$html .= '<option value="' . $o[0] . '">' . JText::_($o[1]) . '</option>';
						}

						$html .= '</field>';
						break;
					case 'radio':
						$html .= '<field
							name="interests][' . $interest[0] . '"
							type="radio"
							default="0"
							label="' . $interest[2] . '"
							labelclass="form-label cmc-label">';

						foreach ($groups as $g)
						{
							$o = explode('##', $g);
							$html .= '<option value="' . $o[0] . '">' . JText::_($o[1]) . '</option>';
						}

						$html .= '</field>';
						break;
				}
			}

			$html .= '</fieldset>';
		}

		// Output the hidden stuff
		$html .= '<fieldset name="defaults">';
		$html .= '<field type="hidden" name="listid" value="' . $listid . '" />';
		$html .= '</fieldset>';

		$html .= '</fields>';
		$html .= '</form>';

		return $html;
	}

	/**
	 * Returns an xml formatted form field
	 *
	 * @param   array  $field  - the field array
	 *
	 * @return  string
	 */
	public function createXmlField($field)
	{
		$fieldtype = $field[1];

		// We need to return a xml formatted object for the joomla form

		if ($fieldtype == "text")
		{
			return $this->xmltext($field);
		}
		elseif ($fieldtype == "dropdown")
		{
			return $this->dropdown($field);
		}
		elseif ($fieldtype == "radio")
		{
			return $this->radio($field);
		}
		elseif ($fieldtype == "date")
		{
			return $this->date($field);
		}
		elseif ($fieldtype == "birthday")
		{
			return $this->birthday($field);
		}
		elseif ($fieldtype == "phone")
		{
			return $this->phone($field);
		}
		elseif ($fieldtype == "address")
		{
			return $this->address($field);
		}
		else
		{
			// Fallback, maybe should be a 404 not supported
			return $this->xmltext($field);
		}
	}

	/**
	 * Returns an xml formatted form field
	 *
	 * @param   array  $field   - the field array
	 * @param   array  $config  - the field type
	 *
	 * @return string
	 */
	public function xmltext($field, $config = array())
	{
		// Structure: EMAIL;email;Email Address;1;
		$validate = array(
			'email' => 'validate-email',
			'number' => 'validate-digits',
			'url' => 'validate-url',
			'phone' => 'validate-digits'
		);

		$type = isset($config['type']) ? $config['type'] : 'text';

		if (isset($config['class']))
		{
			$class[] = $config['class'];
		}

		if (isset($validate[$field[1]]))
		{
			$class[] = $validate[$field[1]];
		}

		$title = JText::_($field[2]);

		$x = "<field\n";
		$x .= "name=\"groups][" . $field[0] . "\"\n";
		$x .= "type=\"" . $type . "\"\n";
		$x .= "id=\"" . $field[0] . "\"\n";

		// Do we want a description here?
		$x .= "description=\"\"\n";
		$x .= "filter=\"string\"\n";
		$x .= 'class="inputbox input-medium" ';
		$x .= 'labelclass="form-label cmc-label" ';
		$x .= "label=\"" . $title . "\"\n";

		if ($field[3])
		{
			$x .= "required=\"required\"\n";
		}

		$x .= "/>\n";

		return $x;
	}

	/**
	 * Returns a drop-down input box element
	 *
	 * @param   array  $params  - Example FNAME;text;First Name;0;""
	 *
	 * @return string
	 */
	public function dropdown($params)
	{
		$choices = explode('##', $params[4]);
		$req = ($params[3]) ? ' required="required" ' : '';
		$title = JText::_($params[2]);

		$select = '<field
			id="' . $params[0] . '"
			name="groups][' . $params[0] . '"
			type="list"
			label="' . $title . '"
			labelclass="form-label cmc-label"
			default="0"
			' . $req . '
			class="inputbox">';

		if (!$params[3])
		{
			$select .= '<option value=""></option>';
		}

		foreach ($choices as $ch)
		{
			$select .= '<option value="' . $ch . '">' . $ch . '</option>';
		}

		$select .= '</field>';

		return $select;
	}

	/**
	 * Returns a radio input box element
	 *
	 * @param   array  $params  - Example FNAME;text;First Name;0;""
	 *
	 * @return string
	 */
	public function radio($params)
	{
		$choices = explode('##', $params[4]);
		$req = ($params[3]) ? 'required="required"' : '';
		$title = JText::_($params[2]);

		$radio = '<field
			name="groups][' . $params[0] . '"
			type="radio"
			' . $req . '
			default="0"
			class="inputbox"
			labelclass="form-label cmc-label"
			label="' . $title . '">';

		foreach ($choices as $ch)
		{
			$radio .= '<option value="' . $ch . '">' . $ch . '</option>';
		}

		$radio .= '</field>';

		return $radio;
	}

	/**
	 * Returns date input box element
	 *
	 * @param   array  $params  - Example FNAME;text;First Name;0;""
	 *
	 * @return string
	 */
	public function date($params)
	{
		$title = JText::_($params[2]);
		$req = $params[3] ? 'required="required"' : '';

		return '<field
			name="groups][' . $params[0] . '"
			type="calendar"
			class="inputbox input-small"
			labelclass="form-label cmc-label"
			label="' . $title . '"
			format="' . $this->dateFormat . '"
			' . $req . '
			maxlength="10"
		/>';
	}

	/**
	 * Returns a birthday input box element
	 *
	 * @param   array  $params  - Example FNAME;text;First Name;0;""
	 *
	 * @return string
	 */
	public function birthday($params)
	{
		$req = ($params[3]) ? 'required="required"' : '';
		$title = JText::_($params[2]);

		$address = '<field type="birthday"
					id="' . $params[0] . '_month"
					name="birthday"
					class="inputbox input-small cmc-birthday"
					labelclass="form-label cmc-label"
					' . $req . '
					label="' . $title . '" />';

		return $address;
	}

	/**
	 * Returns phone input box element
	 *
	 * @param   array  $params  - Example FNAME;text;First Name;0;""
	 *
	 * @return string
	 */
	public function phone($params)
	{
		$req = ($params[3]) ? 'required="required"' : '';
		$title = JText::_($params[2]);
		$inter = '';

		if ($this->phoneFormat == 'inter')
		{
			$inter = 'inter';
		}

		$phone = '
		<field name="groups][' . $params[0] . '"
		type="phone"
		id="cmc-phone-' . $params[0] . '"
		class="phone validate-digits ' . $inter . '"
		labelclass="form-label cmc-label"
		size="40"
		label="' . $title . '"
		' . $req . ' />';

		return $phone;
	}

	/**
	 * Returns address input box element
	 *
	 * @param   array  $params  - Example FNAME;text;First Name;0;""
	 *
	 * @return string
	 */
	public function address($params)
	{
		$req = $params[3] ? 'required="required "' : '';
		$title = JText::_($params[2]);

		$address = '<field type="spacer" name="addr" label="' . $title . '" />';
		$address .= '<field
                name="groups][' . $params[0] . '][addr1"
                type="text" default=""
                label="' . JText::_('CMC_STREET_ADDRESS') . '"
                class="inputbox input-medium"
                labelclass="form-label cmc-label"
                ' . $req . '
                />';

		if ($this->address2)
		{
			$address .= '<field
	                name="groups][' . $params[0] . '][addr2"
	                type="text" default=""
	                label="' . JText::_('CMC_STREET_ADDRESS2') . '"
	                class="inputbox input-medium"
	                labelclass="form-label cmc-label"
	                ' . $req . '
	                />';
		}

		$address .= '<field
                name="groups][' . $params[0] . '][city"
                type="text" default=""
                label="' . JText::_('CMC_CITY') . '"
                class="inputbox input-medium"
                labelclass="form-label cmc-label"
                ' . $req . '
                />';
		$address .= '<field
                name="groups][' . $params[0] . '][state"
                type="text" default=""
                label="' . JText::_('CMC_STATE') . '"
                class="inputbox input-medium"
                labelclass="form-label cmc-label"
                ' . $req . '
                />';
		$address .= '<field
                name="groups][' . $params[0] . '][zip"
                type="text" default=""
                label="' . JText::_('CMC_ZIP') . '"
                class="inputbox input-medium"
                labelclass="form-label cmc-label"
                ' . $req . '
                />';

		$address .= $this->getCountryDropdown($params[0], $params[0], JText::_('CMC_COUNTRY'), $req) . '<br />';

		return $address;
	}

	/**
	 * Returns date input box element
	 *
	 * @param   string  $name  - Name of the select
	 * @param   int     $id    - The date format for this field
	 * @param   string  $title - The field name prefix
	 * @param   boolean $req   - Is the field required?
	 *
	 * @return string
	 */
	private function getCountryDropdown($name, $id, $title, $req)
	{
		$options = CmcHelperCountries::getCountries();

		$select = '<field
			id="' . $id . '"
			name="groups][' . $name . '][country"
			type="list"
			label="' . $title . '"
			default="0"
			class="inputbox"
			labelclass="form-label cmc-label"
			' . $req . '
			>';

		$select .= '<option value=""></option>';

		foreach ($options as $k => $v)
		{
			$select .= '<option value="' . $k . '">' . ucwords(strtolower($v)) . '</option>';
		}

		$select .= '</field>';

		return $select;
	}
}