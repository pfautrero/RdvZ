<?php

/**
  * Classe de connection à une base de données utilisée pour
  * l'authentification d'utilisateurs.
  *
  */

class uapvDB
{
  /**
   * Ressource de connection Base de Données
   * @var resource
   */
  private $bdCon = null;

  /**
    * Options de connections
    * @var array
    */
  private $config ;
  
  /**
   * Contructeur
   *
   * @param array $conf Paramètres de connection à la bas. Les paramètres
   *                    obligatoires sont : "host", "pdo", "dbname", 
                        "username", "password"
   */
  public function __construct ($conf = null)
  {
    if (! is_array ($conf))
    {
      $conf = array ();

      $conf['host'] = sfConfig::has('app_bdd_server_host')   ? sfConfig::get('app_bdd_server_host')   : 'localhost' ;
      $conf['pdo']  = sfConfig::has('app_bdd_server_pdo')    ? sfConfig::get('app_bdd_server_pdo')    : 'mysql' ;
      $conf['db']   = sfConfig::has('app_bdd_server_dbname') ? sfConfig::get('app_bdd_server_dbname') : 'rdvz' ;
      $conf['user'] = sfConfig::get('app_bdd_server_username') ;
      $conf['pass'] = sfConfig::get('app_bdd_server_password') ;
      $conf['mail_sender'] = sfConfig::get('app_mail_sender');
      $conf['org_name'] = sfConfig::get('app_org_name');
      $conf['subject_mail_new_account'] = sfConfig::get('parameters_uapv_auth_plugin_mail_new_account_subject');
      $conf['body_mail_new_account'] = sfConfig::get('parameters_uapv_auth_plugin_mail_new_account_body');
      $conf['subject_mail_new_password'] = sfConfig::get('parameters_uapv_auth_plugin_mail_new_password_subject');
      $conf['body_mail_new_password'] = sfConfig::get('parameters_uapv_auth_plugin_mail_new_password_body');
      $conf['footer_mail_new_password'] = sfConfig::get('parameters_uapv_auth_plugin_mail_new_password_footer');
      $conf['footer_mail_new_account'] = sfConfig::get('parameters_uapv_auth_plugin_mail_new_account_footer');
      $conf['uapv_user_name'] = sfConfig::get('parameters_uapv_auth_plugin_user_name');	  
      $conf['user_password'] = sfConfig::get('parameters_uapv_auth_plugin_user_password');	  
      $conf['new_user_password'] = sfConfig::get('parameters_uapv_auth_plugin_new_user_password');
	  
      $conf['user_tab']     = sfConfig::has('app_bdd_infos_user_table_name') ? sfConfig::get('app_bdd_infos_user_table_name') : 'user' ;
      $conf['user_login']   = sfConfig::has('app_bdd_infos_user_login_field') ? sfConfig::get('app_bdd_infos_user_login_field') : 'login' ;
      $conf['user_pass']    = sfConfig::has('app_bdd_infos_user_pass_field') ? sfConfig::get('app_bdd_infos_user_pass_field') : 'pass' ;
      $conf['pass_crypt']   = sfConfig::has('app_bdd_infos_user_pass_encrypt') ? sfConfig::get('app_bdd_infos_user_pass_encrypt') : 'sha1' ;
      $conf['user_mail']    = sfConfig::has('app_bdd_infos_user_mail_field') ? sfConfig::get('app_bdd_infos_user_mail_field') : 'mail' ;
      $conf['user_name']    = sfConfig::has('app_bdd_infos_user_name_field') ? sfConfig::get('app_bdd_infos_user_name_field') : 'name' ;
      $conf['user_surname']    = sfConfig::has('app_bdd_infos_user_surname_field') ? sfConfig::get('app_bdd_infos_user_surname_field') : 'surname' ;
    }

    $this->config = $conf;
  }
  /**
   * 
   * 
   */
  public function connect()
  {
    if ($this->bdCon !== null)
      return ;

    //$dsn = $this->config['pdo'].':dbname='.$this->config['db'].';host='.$this->config['host'] ;
    //$dbh = new PDO($dsn, $this->config['user'] , $this->config['pass']) ; 

    $dsn = $this->config['pdo']."://".$this->config['user'].":".$this->config['pass']."@".$this->config['host']."/".$this->config['db'] ;

    $this->bdCon = Doctrine_Manager::connection($dsn) ;
  }

  /**
    * Fonction qui regarde dans la table utilisateurs si le couple login/password
    * entré existe vraiment.
    *
    * @return true or false
    */
  public function checkPassword($user, $pass)
  {
    $this->connect() ;
    $user = addslashes($user);
    $pass = addslashes($pass);
    $crypt = $this->config['pass_crypt'] ;

    switch($crypt)
    {
      case 'md5' :   $pass = md5($pass) ;
                     break ;
      case 'sha1' :  $pass = sha1($pass) ;
                     break ;
      default :      break ;
    }

    $q = $this->bdCon->prepare("select count(*) from ".$this->config['user_tab']." where ".$this->config['user_login']."='$user' and ".$this->config['user_pass']."='$pass'") ;
    $q->execute() ;
    $res = $q->fetch() ;
    return $res[0] ;
  }
  /**
   * Récupération des champs d'un user
   * 
   */
  public function getUser($login)
  {
    $this->connect() ;

    $q = $this->bdCon->prepare("select * from ".$this->config['user_tab']." where ".$this->config['user_login']."='$login'") ;
    $q->execute() ;
    $res = $q->fetch() ;
    return $res ;
  }
  /**
   * Renvoi un user s'il existe
   * 
   */
  public function isUserRegistered($user)
  {
    $user = addslashes($user);
    $this->connect() ;
     $q = $this->bdCon->prepare("select * from ".$this->config['user_tab']." where ".$this->config['user_login']."='$user'");
    $q->execute();
    $res = $q->fetch();
    return $res;

  }
  /**
   * renvoi un user si l'adresse mail existe
   * 
   */
  public function isEmailRegistered($email)
  {
    $user = addslashes($email);
    $this->connect() ;
     $q = $this->bdCon->prepare("select * from ".$this->config['user_tab']." where ".$this->config['user_mail']."='$email'");
    $q->execute();
    $res = $q->fetch();
    return $res;

  }
  /**
   * génère un mot de passe à l'aide d'une chaine définie dans config/parameters.yml
   * 
   */
    public function generatePassword( $chrs = "") {

        if( $chrs == "" ) $chrs = 8;
        $chaine = ""; 
        $list = sfConfig::get('parameters_uapv_auth_plugin_password_chars'); 
        mt_srand((double)microtime()*1000000);
        $newstring="";
        while( strlen( $newstring )< $chrs ) {
                $newstring .= $list[mt_rand(0, strlen($list)-1)];
        }
        return $newstring;
    }  
  
  /**
   *  Ajout d'un user en bdd + envoi du mail d'inscription
   *  paramétrage du mail dans config/parameters.yml
   */  
  public function addUserInDB($login, $firstname, $lastname, $email)
  {
    $this->connect() ;
    $login = utf8_decode(addslashes($login));
    $firstname = utf8_decode(addslashes($firstname));
    $lastname = utf8_decode(addslashes($lastname));
    $email = utf8_decode(addslashes($email));

    $crypt = $this->config['pass_crypt'] ;
    $pass=$this->generatePassword();
    if ($crypt == 'md5')
    {
        $crypted = md5($pass) ;
    }
    else
    {
        $crypted = sha1($pass) ;
    }
	
    $q = $this->bdCon->prepare("insert into ".$this->config['user_tab']." set 
			".$this->config['user_login']."='$login', 
			".$this->config['user_pass']."='$crypted',
			".$this->config['user_name']." = '$firstname',
			".$this->config['user_surname']." = '$lastname',
			".$this->config['user_mail']." = '$email'			
			") ;
    $resp = $q->execute() ;
    if ($resp) {
        // send a mail
        $to = $email;

        $subject = $this->config['subject_mail_new_account'];
        $message = $this->config['body_mail_new_account'].
                                $this->config['uapv_user_name'].$login.
                                $this->config['user_password'].$pass.
                                $this->config['footer_mail_new_account'].
                                $this->config['org_name']."\n".
                                $this->config['mail_sender'];
        $headers = 'From: '.$this->config['mail_sender']."\r\n" .
        'Reply-To: '.$this->config['mail_sender']."\r\n" .
        'X-Mailer: PHP/' . phpversion(). "\r\n" .
        'Bcc: '.$this->config['mail_sender']. "\r\n" .
        'Content-type: text/plain; charset=UTF-8';
        error_reporting(0);
        mail($to ,$subject ,$message, $headers);
    }
    return $res ;
  } 
  /**
   *  envoi par mail d'un nouveau mot de passe
   * 
   */
 public function sendNewPass($email)
  {
    $email = addslashes($email);
    $this->connect() ;
    $crypt = $this->config['pass_crypt'] ;
	$pass=$this->generatePassword();
	if ($crypt == 'md5')
	{
            $crypted = md5($pass) ;
	}
	else
	{
            $crypted = sha1($pass) ;
	}
    $q = $this->bdCon->prepare("select * from ".$this->config['user_tab']." where ".$this->config['user_mail']."='$email'");
    $q->execute();
            
    $res = $q->fetch();	

    if ($res) {
        $login = $res['login'];
        $q = $this->bdCon->prepare("update ".$this->config['user_tab']." set ".$this->config['user_pass']."='$crypted' where ".$this->config['user_mail']."='$email'") ;
        $res1 = $q->execute() ;

        if ($res1) {
            // send a mail
            $to = $email;

            $subject = $this->config['subject_mail_new_password'];
            $message = $this->config['body_mail_new_password'].
                                    $this->config['uapv_user_name'].$login.
                                    $this->config['new_user_password'].$pass.
                                    $this->config['footer_mail_new_password'].
                                    $this->config['org_name']."\n".
                                    $this->config['mail_sender'];
            $headers = 'From: '.$this->config['mail_sender']."\r\n" .
            'Reply-To: '.$this->config['mail_sender']."\r\n" .
            'X-Mailer: PHP/' . phpversion(). "\r\n" .
            'Bcc: '.$this->config['mail_sender']. "\r\n" .
            'Content-type: text/plain; charset=UTF-8';
            error_reporting(0);
            mail($to ,$subject ,$message, $headers);
        }
    }
    return $res ;
  }  
}