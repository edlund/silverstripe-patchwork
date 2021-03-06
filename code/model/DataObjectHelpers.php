<?php

/**
 * Copyright (c) 2013, Redema AB - http://redema.se/
 * 
 * Redistribution and use in source and binary forms, with or without modification,
 * are permitted provided that the following conditions are met:
 * 
 * * Redistributions of source code must retain the above copyright notice,
 *   this list of conditions and the following disclaimer.
 * 
 * * Redistributions in binary form must reproduce the above copyright notice,
 *   this list of conditions and the following disclaimer in the documentation
 *   and/or other materials provided with the distribution.
 * 
 * * Neither the name of Redema, nor the names of its contributors may be used
 *   to endorse or promote products derived from this software without specific
 *   prior written permission.
 * 
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
 * ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR
 * ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON
 * ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 * SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

/**
 * Mixed utilities and helpers which are too small to be given
 * a new extension.
 */
class DataObjectHelpers extends DataExtension {
	
	/**
	 * $Pos in a template will give "1" as the first position.
	 * Feed that value to this method to get the proper position.
	 */
	public function ActualPos($pos) {
		return (int)$pos - 1;
	}
	
	/**
	 * Quickly scaffold form fields.
	 * 
	 * @param FieldList $fields
	 * @param null|string $tab
	 * @param string $class
	 * @param DataObject $object
	 * @param null|FormTransformation $formTransformation
	 * @param callable(FieldList, string, FormField) $callback
	 */
	public function autoScaffoldFormFields(FieldList $fields,
			$tab, $class, DataObject $object, $formTransformation, $callback) {
		$config = Config::inst();
		$db = array_keys((array)$config->get($class, 'db'));
		$has = array_keys((array)$config->get($class, 'has_one'));
		array_walk($has, function (&$item) {
			$item = "{$item}ID";
		});
		
		foreach (array_merge($db, $has) as $name) {
			$dbField = $object->dbObject($name);
			$formField = $dbField->scaffoldFormField($object->fieldLabel($name));
			if ($formTransformation)
				$formField = $formTransformation->transform($formField);
			call_user_func($callback, $fields, $tab, $formField);
		}
	}
	
	/**
	 * Quickly scaffold Extension form fields into a tab for the
	 * given field list.
	 * 
	 * @param FieldList $fields
	 * @param string $tab
	 * @param string $class
	 * @param DataObject $object
	 * @param null|FormTransformation $formTransformation
	 */
	public function autoScaffoldExtensionFormFields(FieldList $fields,
			$tab, $class, DataObject $object, $formTransformation) {
		$addFieldToTab = function (FieldList $fields, $tab, FormField $field) {
			$fields->addFieldToTab($tab, $field);
		};
		$fields->findOrMakeTab($tab, $object->fieldLabel(
			str_replace('.', '_', $tab)));
		$this->autoScaffoldFormFields($fields, $tab, $class, $object,
			$formTransformation, $addFieldToTab);
	}
	
	/**
	 * Automatically translate the options in a DropdownField.
	 */
	public function autoTranslateDropdown($baseEntity, DropdownField $dropdownField) {
		$options = $dropdownField->getSource();
		$i18nOptions = array();
		foreach ($options as $option => $ignored) {
			$i18nOptions[$option] = _t("{$baseEntity}_{$option}", $option);
		}
		$dropdownField->setSource($i18nOptions);
	}
}


