<?php


class Openiview_Form_LoginForm extends Zend_Form 
{
	
	public function __construct($option = null)
	{
		parent::__construct($option);
		
		$this->setName('LogIn');
		$this->setMethod('post');
		$this->setAction(Zend_Controller_Front::getInstance()->getBaseUrl() .  '/authentication/login');
		
		$username = new Zend_Form_Element_Text('username');
		$username->setLabel('User Name:')
				 ->setRequired();
				 
		$password = new Zend_Form_Element_Password('password');
		$password->setLabel('Password:')
			     ->setRequired();
			     
		$login = new Zend_Form_Element_Submit('Login');
		$login->setLabel('Login');
		
		$this->addElements(array($username , $password , $login));
		
		
	}
}
