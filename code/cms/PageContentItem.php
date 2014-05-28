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

if (class_exists('SiteTree')) {

/**
 * Handle multiple images or blocks of content on a single
 * page. To just get a single image per page, see PageMainImage.
 * 
 * <code>
 * class Page extends SiteTree {
 *     private static $has_many = array(
 *         'CarouselItems' => 'PageCarouselItem'
 *     );
 *     public function getCMSFields() {
 *         $fields = parent::getCMSFields();
 *         PageCarouselItem::addCMSFieldsTo($this, $fields,
 *             $this->CarouselItems());
 *         return $fields;
 *     }
 * }
 * class PageCarouselItem extends PageContentItem {
 * }
 * </code>
 * 
 * @see PageMainImage
 * @see PatchworkGridFieldSortableRows
 */
class PageContentItem extends DataObject {
	private static $db = array(
		'Title' => 'Text',
		'Link' => 'Text',
		'Content' => 'HTMLText',
		'ExtraClasses' => 'Text',
		'SpecialTemplate' => 'Text',
		'Sort' => 'Int'
	);
	
	private static $has_one = array(
		'Page' => 'Page',
		'DesktopImage' => 'Image',
		'TabletImage' => 'Image',
		'MobileImage' => 'Image'
	);
	
	private static $autoversioned = array(
		'Page' => true
	);
	
	private static $extensions = array(
		"Versioned('Stage', 'Live')",
		"Autoversioned",
		"VersionedHooks",
		"VersionedStatus"
	);
	
	public static $default_sort = 'Sort, ID';
	
	public function Inner($contentClass = '') {
		$templates = array(
			$this->SpecialTemplate,
			$this->ClassName,
			'PageContentItem'
		);
		return $this->renderWith($templates, array(
			'ContentClass' => $contentClass
		));
	}
	
	public function Thumbnail() {
		// Go from smallest to largest and use the first available
		// image for the thumbnail.
		$images = array(
			'MobileImage',
			'TabletImage',
			'DesktopImage'
		);
		foreach ($images as $name) {
			$image = $this->$name();
			if ($image->exists())
				return $image->CroppedImage(32, 32);
		}
		
		// Fallback in case there are no linked images.
		$image = Image::create();
		$image->Name = 'image-missing.png';
		$image->Filename = "patchwork/images/{$image->Name}";
		return $image;
	}
	
	/**
	 * @FIXME: It would be great to have a way to unpublish items
	 * (without deleting the appropriate table rows manually).
	 */
	public static function addCMSFieldsTo(Page $page, FieldList $fields,
			DataList $items) {
		$itemClass = get_called_class();
		$itemClasses = "{$itemClass}s";
		
		$itemObject = $itemClass::create();
		
		$itemsFieldConfig = GridFieldConfig_RelationEditor::create(count($items));
		$itemsFieldColumns = $itemsFieldConfig->getComponentByType('GridFieldDataColumns');
		
		if (class_exists('GridFieldSortableRows'))
			$itemsFieldConfig->addComponent(new PatchworkGridFieldSortableRows('Sort'));
		
		$itemsFieldColumns->setDisplayFields(array(
			'Thumbnail' => $itemObject->fieldLabel('Thumbnail'),
			'Title' => $itemObject->fieldLabel('Title'),
			'Link' => $itemObject->fieldLabel('Link'),
			'ExtraClasses' => $itemObject->fieldLabel('ExtraClasses'),
			'PublishedToLive' => 'PublishedToLive'
		));
		
		$itemsField = new GridField(
			$itemClasses,
			$itemClass,
			$items,
			$itemsFieldConfig
		);
		
		$tabName = _t("{$itemClass}.TabName", $itemClasses);
		$fields->findOrMakeTab("Root.{$itemClasses}", $tabName);
		$fields->addFieldToTab("Root.{$itemClasses}", $itemsField);
	}
	
	public function getCMSFields() {
		$fields = parent::getCMSFields();
		
		$tabs = array(
			'Advanced' => array(
				'ExtraClasses' => 'TextField',
				'SpecialTemplate' => 'TextField'
			),
			'Content' => array(
				'Content' => 'HTMLEditorField'
			)
		);
		
		foreach ($tabs as $tab => $specs) {
			$fields->findOrMakeTab("Root.{$tab}", $this->fieldLabel($tab));
			foreach ($specs as $name => $type) {
				$fields->removeByName("Root.{$name}");
				$fields->addFieldToTab("Root.{$tab}", new $type($name,
					$this->fieldLabel($name)));
			}
		}
		
		$fieldTransformation = new FormTransformation_SpecificFields(array(
			'Title' => 'TextField',
			'Link' => 'TextField',
			'ExtraClasses' => 'TextField',
			'SpecialTemplate' => 'TextField'
		));
		if (class_exists('GridFieldSortableRows')) {
			$fieldTransformation->addField('Sort', function (FormField $field) {
				return $field->performDisabledTransformation();
			});
		}
		$replaceField = function (FieldList $fields, $tab, FormField $field) {
			$fields->replaceField($field->getName(), $field);
		};
		$this->autoScaffoldFormFields($fields, null, get_class($this),
			$this, $fieldTransformation, $replaceField);
		
		return $fields;
	}
	
	public function fieldLabels($includerelations = true) {
		$labels = parent::fieldLabels($includerelations);
		
		$labels['Thumbnail'] = _t('PageContentItem.Thumbnail', 'Thumbnail');
		
		$labels['Title'] = _t('PageContentItem.Title', 'Title');
		$labels['Link'] = _t('PageContentItem.Link', 'Link');
		$labels['Content'] = _t('PageContentItem.Content', 'Content');
		$labels['ExtraClasses'] = _t('PageContentItem.ExtraClasses', 'Extra classes');
		$labels['SpecialTemplate'] = _t('PageContentItem.SpecialTemplate', 'Special template');
		$labels['Sort'] = _t('PageContentItem.Sort', 'Sort');
		
		if ($includerelations) {
			$labels['Page'] = $labels['PageID']
				= _t('PageContentItem.Page', 'Page');
			$labels['DesktopImage'] = $labels['DesktopImageID']
				= _t('PageContentItem.DesktopImage', 'Desktop image');
			$labels['TabletImage'] = $labels['TabletImageID']
				= _t('PageContentItem.TabletImage', 'Tablet image');
			$labels['MobileImage'] = $labels['MobileImageID']
				= _t('PageContentItem.MobileImage', 'Mobile image');
		}
		
		return $labels;
	}
	
}

}
