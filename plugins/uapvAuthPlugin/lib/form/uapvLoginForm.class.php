<?php

class uapvLoginForm extends sfForm
{
  public function configure()
  {
    $this->setWidgets(array(
        'username' => new sfWidgetFormInput(), 
        'password' => new sfWidgetFormInputPassword(),
	'firstname' => new sfWidgetFormInput(), 
	'lastname' => new sfWidgetFormInput(),
        'email' => new sfWidgetFormInput(),
      
    ));
    $this->widgetSchema->setLabels(array(
        'username'  => 'identifiant',
        'password'  => 'mot de passe',
        'firstname' => 'prénom',
        'lastname'  => 'nom',
        'email'     => 'email',
    ));
    $this->widgetSchema->setNameFormat('login[%s]');

    $this->setValidators(array(
        'username' => new sfValidatorString(array('required' => false)), 
        'password' => new sfValidatorString(array('required' => false)),
        'lastname' => new sfValidatorString(array('required' => false)),
        'firstname' => new sfValidatorString(array('required' => false)),
        'email' => new sfValidatorString(array('required' => false)),
    ));
  }
}

