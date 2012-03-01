<?php
/**
 * Address Form
 * @author Pavel Kovalyov <pavlo.kovalyov@gmail.com>
 */
abstract class Forms_Address_Abstract extends Zend_Form {

	public function init() {
		$this->setMethod(Zend_Form::METHOD_POST);

		$this->addElement(new Zend_Form_Element_Text(array(
			'name'     => 'firstname',
			'label'    => 'First Name'
		)));

		$this->addElement(new Zend_Form_Element_Text(array(
			'name'     => 'lastname',
			'label'    => 'Last Name',
		)));

		$this->addElement(new Zend_Form_Element_Text(array(
			'name'  => 'company',
			'label' => 'Company'
		)));

		$this->addElement(new Zend_Form_Element_Text(array(
			'name'     => 'email',
			'label'    => 'E-mail'
		)));

		$this->addElement(new Zend_Form_Element_Text(array(
			'name'     => 'address1',
			'label'    => 'Address 1',
		)));

		$this->addElement(new Zend_Form_Element_Text(array(
			'name'     => 'address2',
			'label'    => 'Address 2'
		)));

		$this->addElement(new Zend_Form_Element_Select(array(
			'name'         => 'country',
			'label'        => 'Country',
			'multiOptions' =>  Tools_Geo::getCountries(true),
		)));

		$this->addElement(new Zend_Form_Element_Text(array(
			'name'     => 'city',
			'label'    => 'City',
		)));

		$this->addElement(new Zend_Form_Element_Select(array(
			'name'         => 'state',
			'label'        => 'State',
			'multiOptions' =>  Tools_Geo::getState(null, true),
		)));

		$this->addElement(new Zend_Form_Element_Text(array(
			'name'     => 'zip',
			'label'    => 'ZIP Code',
		)));

		$this->addElement(new Zend_Form_Element_Text(array(
			'name'     => 'phone',
			'label'    => 'Phone'
		)));

//		$this->addElement(new Zend_Form_Element_Hash(array(
//			'name'       => 'nocsrf',
//			'salt'       => SITE_NAME
//		)));

		$this->getElement('state')->addValidator(new Zend_Validate_Db_RecordExists(array(
			'table' => 'shopping_list_state',
			'field' => 'id'
		)),true);
	}

	public function setDefault($name, $value) {
		switch ($name) {
			case 'state':
				$list = Tools_Geo::getState($this->getElement('country')->getValue(), true);
				if (empty ($list) || !array_key_exists($value, $list)) {
					return $this;
				}
				if (!count($this->getElement('country')->getMultiOptions())) {
					$this->getElement('state')->setMultiOptions(Tools_Geo::getState($value, true));
				}
				$this->getElement($name)->setMultiOptions($list);
				break;
			case 'country':
				$this->getElement('state')->setMultiOptions(Tools_Geo::getState($value, true));
				break;
		}
		parent::setDefault($name, $value);
	}
}