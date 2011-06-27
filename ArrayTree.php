<?php if (!defined('TL_ROOT')) die('You cannot access this file directly!');

/**
 * Contao Open Source CMS
 * Copyright (C) 2005-2011 Leo Feyer
 *
 * Formerly known as TYPOlight Open Source CMS.
 *
 * This program is free software: you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation, either
 * version 3 of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this program. If not, please visit the Free
 * Software Foundation website at <http://www.gnu.org/licenses/>.
 *
 * PHP version 5
 * @copyright  Felix Peters 2011
 * @author     Felix Peters - Wichteldesign
 * @package    wd
 * @license    LGPL
 * @filesource
 */


/**
 * Class ArrayTree
 *
 * @copyright  Felix Peters 2011
 * @author     Felix Peters - Wichteldesign
 * @package    Controller
 */
class ArrayTree extends Widget
{

	/**
	 * Submit user input
	 * @var boolean
	 */
	protected $blnSubmitInput = true;

	/**
	 * Template
	 * @var string
	 */
	protected $strTemplate = 'be_widget_chk';

	/**
	 * Options
	 * @var array
	 */
	protected $arrOptions = array();

	/**
	 * Label for Header
	 *
	 * @var String
	 */
	private $treeLabel = '';


	/**
	 * State - Display state od Elements
	 * @var array
	 */
	private $state = array();


	/**
	 * Add specific attributes
	 * @param string
	 * @param mixed
	 */
	public function __set($strKey, $varValue)
	{
		switch ($strKey)
		{
			case 'options':
				$this->arrOptions = deserialize($varValue);
				break;
					
			case 'treeLabel':
				$this->treeLabel = $varValue;
				break;

			case 'mandatory':
				$this->arrConfiguration['mandatory'] = $varValue ? true : false;
				break;

			default:
				parent::__set($strKey, $varValue);
				break;
		}
	}


	/**
	 * Clear result if nothing has been submitted
	 */
	public function validate()
	{
		parent::validate();

		if (!isset($_POST[$this->strName]))
		{
			$this->varValue = '';
		}
	}


	/**
	 * Generate the widget and return it as string
	 * @return string
	 */
	public function generate()
	{


		if (!$this->multiple && count($this->arrOptions) > 1)
		{
			$this->arrOptions = array($this->arrOptions[0]);
		}

		$this->state = $this->Session->get('arrayTree_groups');

		FB::log($this->state, 'cbg');

		// Toggle checkbox group
		if ($this->Input->get('atc'))
		{
			$this->state[$this->Input->get('atc')] = (isset($this->state[$this->Input->get('atc')]) && $this->state[$this->Input->get('atc')] == 1) ? 0 : 1;
			$this->Session->set('arrayTree_groups', $this->state);

			$this->redirect(preg_replace('/(&(amp;)?|\?)atc=[^& ]*/i', '', $this->Environment->request));
		}

		$tree = $this->buildOptions($this->arrOptions, 0);

		// Reset radio button selection
		if ($GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['multiple'])
		{
			$strReset = "\n" . '    <li class="tl_folder"><div class="tl_left">&nbsp;</div> <div class="tl_right"><label for="ctrl_'.$this->strId.'_0" class="tl_change_selected">'.$GLOBALS['TL_LANG']['MSC']['resetSelected'].'</label> <input type="radio" name="'.$this->strName.'" id="'.$this->strName.'_0" class="tl_tree_radio" value="" onfocus="Backend.getScrollOffset();" /></div><div style="clear:both;"></div></li>';
		}else{
			$strReset = "\n" . '    <li class="tl_folder"><div class="tl_left">&nbsp;</div> <div class="tl_right"><label for="check_all_'.$this->strId.'_0" class="tl_change_selected">'.$GLOBALS['TL_LANG']['MSC']['selectAll'].'</label> <input type="checkbox" id="check_all_' . $this->strId . '_0" class="tl_checkbox" value="" onclick="Backend.toggleCheckboxGroup(this, \'' . $this->strName . '\')" /></div><div style="clear:both;"></div></li>';
		}


		return '<ul class="tl_listing tree_view'.(strlen($this->strClass) ? ' ' . $this->strClass : '').'" id="'.$this->strId.'">
		  		<li class="tl_folder_top"><div class="tl_left">'.$this->generateImage('system/modules/wd_ArrayTree/html/img/chart_organisation.png').' '.$this->treeLabel .'</div> <div class="tl_right"><label for="ctrl_'.$this->strId.'" class="tl_change_selected">'.$GLOBALS['TL_LANG']['MSC']['changeSelected'].'</label> <input type="checkbox" name="'.$this->strName.'_save" id="ctrl_'.$this->strId.'" class="tl_tree_checkbox" value="1" onclick="Backend.showTreeBody(this, \''.$this->strId.'_parent\');" /></div><div style="clear:both;"></div></li><li class="parent" id="'.$this->strId.'_parent">
		  		<ul>'.$tree .$strReset.'</ul></li></ul>';
	}


	protected function buildOptions($optionRow, $level){

		$return = '';
		$intMargin = $level * 30;

		foreach ($optionRow as $i=>$arrOption)
		{
			// Check if Subitems
			if (is_numeric($i))
			{
				// Single Item
				$return .= $this->generateSingleRow($arrOption, $intMargin, $i);
			}else{
				// Build TreeNode
				$id = 'atc_' . $this->strId . '_' . standardize($i);
				$img = 'folPlus';
				$display = 'none';
				if (!isset($this->state[$id]) || !empty($this->state[$id])){
					$img = 'folMinus';
					$display = 'block';
				}
					
				$return .= $this->generateTreeNode($i, $intMargin, $id, $img);

				$return .= '<li id="' . $id . '" class="parent" style="display: '.$display.';"><ul class="level_'.$level.'">';
				$return .= $this->buildOptions($arrOption, $level + 1);
				$return .= '</ul></li>';

			}
		}

		return $return;

	}

	/**
	 * Generate a Single Tree-Row and return it as String
	 *
	 * @param $arrOption
	 * @param $intMargin
	 * @param $i
	 * @return string
	 */
	protected function generateSingleRow($arrOption, $intMargin, $i){
		$return .= "\n    " . '<li class="tl_file" onmouseover="Theme.hoverDiv(this, 1);" onmouseout="Theme.hoverDiv(this, 0);"><div class="tl_left" style="padding-left:'.($intMargin + $intSpacing).'px;">';
		$return .= '<label for="opt_'. $this->strId.'_'.$i .'">' . $arrOption['label'] . '</label></div> <div class="tl_right">';
		$return .= $this->generateInput($arrOption, $i);
		$return .= '</div><div style="clear:both;"></div></li>';

		return $return;
	}

	/**
	 * Generate a TreeNode an return it as String
	 *
	 * @param $label
	 * @param $intMargin
	 * @param $id
	 * @param $img
	 * @return string
	 */
	protected function generateTreeNode($label, $intMargin, $id, $img){
		$return .= "\n    " . '<li class="tl_file" onmouseover="Theme.hoverDiv(this, 1);" onmouseout="Theme.hoverDiv(this, 0);"><div class="tl_left" style="padding-left:'.($intMargin + $intSpacing).'px;">';
		$return .= '<a href="' . $this->addToUrl('atc=' . $id) . '" onclick="ArrayTree.toggleArrayTreeGroup(this, \'' . $id . '\'); Backend.getScrollOffset(); return false;"><img src="system/themes/' . $this->getTheme() . '/images/' . $img . '.gif" alt="toggle checkbox group" />'.$label.'</a>';
		$return .=  '</div> <div class="tl_right">';
		$return .= '</div><div style="clear:both;"></div></li>';

		return $return;
	}

	/**
	 * Generate a checkbox and return it as string
	 * @param array
	 * @param integer
	 * @return string
	 */
	protected function generateInput($arrOption, $i)
	{
		return sprintf('<input type="checkbox" name="%s" id="opt_%s" class="tl_checkbox" value="%s"%s%s onfocus="Backend.getScrollOffset();" />',
		$this->strName . ($this->multiple ? '[]' : ''),
		$this->strId.'_'.$i,
		($this->multiple ? specialchars($arrOption['value']) : 1),
		$this->isChecked($arrOption),
		$this->getAttributes()
		);
	}

	/**
	 * Check the Ajax pre actions
	 * @param string
	 * @param object
	 * @return string
	 */
	public function executePreActions($action)
	{
						
		if ($action == 'toggleArrayTreeGroup')
		{						
			$state = $this->Session->get('arrayTree_groups');
			
			$state[$this->Input->post('id')] = intval($this->Input->post('state'));

			$this->Session->set('arrayTree_groups', $state);
			exit; break;
		}
	}
}

?>