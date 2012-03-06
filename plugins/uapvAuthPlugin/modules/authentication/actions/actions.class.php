<?php

/**
 * authentication actions.
 */
class authenticationActions extends sfActions
{
  public function executeLogin (sfWebRequest $request)
  {
    $this->form = new uapvLoginForm() ;

    if($request->isMethod('post'))
    {
      $form_info = $request->getParameter('login') ;
      $this->form->bind($form_info);
      if ($this->form->isValid() || $request->getParameter('create_account'))
      {
			
        // Quel type d'authentification a été choisi lors de l'installation?
        $auth_type = sfConfig::get('app_authentication_type') ;
        if($auth_type == 'bdd')
        {
            $bd = new uapvDB() ;
            $mail_filter_enabled = sfConfig::get('parameters_uapv_auth_plugin_enable_mail_filter');
            // ==================== Demande d'un nouveau mot de passe
            if ($request->getParameter('ask_mail')) {
                $mail_filter_valid = false;
                if ($mail_filter_enabled == "yes") {
                    if (preg_match(sfConfig::get('parameters_uapv_auth_plugin_pattern_mail_filter'), $request->getParameter('email_for_new_pass'))) {
                        $mail_filter_valid = true;
                    }
                }
                else {
                    $mail_filter_valid = true;
                }
                if ($mail_filter_valid) {
                    $resp = $bd->sendNewPass($request->getParameter('email_for_new_pass')) ;
                    if ($resp) {
                        $notice = sfConfig::get('parameters_uapv_auth_plugin_new_password_sent');
                        $this->getContext()->getUser()->setFlash('notice', $notice, false) ;
                    }
                    else {
                        $error = sfConfig::get('parameters_uapv_auth_plugin_error_occured');
                        $this->getContext()->getUser()->setFlash('error', $error, false) ;
                    }
                }
                else {
                    $error = sfConfig::get('parameters_uapv_auth_plugin_use_email_address');
                    $this->getContext()->getUser()->setFlash('error', $error, false) ;
                }

            }
            // ======================= Demande de création d'un nouveau compte
            else if ($request->getParameter('create_account')) {
                if ($request->getParameter('login[firstname]') && $request->getParameter('login[lastname]') && $request->getParameter('login[email]')) {
                    if ($bd->isUserRegistered($form_info['username'])) {
                        $error = sfConfig::get('parameters_uapv_auth_plugin_user_already_registered');
                        $this->getContext()->getUser()->setFlash('error', $error, false) ;
                    }
                    else {
                        if ($bd->isEmailRegistered($form_info['email'])) {
                            $error = sfConfig::get('parameters_uapv_auth_plugin_email_already_registered');
                            $this->getContext()->getUser()->setFlash('error', $error, false) ;
                        }
                        else {
                            $mail_filter_valid = false;

                            if ($mail_filter_enabled == "yes") {
                                if (preg_match(sfConfig::get('parameters_uapv_auth_plugin_pattern_mail_filter'), $form_info['email'])) {
                                    $mail_filter_valid = true;
                                }
                            }
                            else {
                                $mail_filter_valid = true;
                            }                            

                            if ($mail_filter_valid) {
                                    $resp = $bd->addUserInDB($form_info['username'], $form_info['firstname'],$form_info['lastname'],$form_info['email'] ) ;			
                                    if ($resp) 
                                    {
                                        $error = sfConfig::get('parameters_uapv_auth_plugin_user_already_registered');
                                        $this->getContext()->getUser()->setFlash('error', $error, false) ;
                                    }
                                    else {
                                        $notice = sfConfig::get('parameters_uapv_auth_plugin_account_created');
                                        $this->getContext()->getUser()->setFlash('notice', $notice, false) ;
                                    }
                            }
                            else {
                                $error = sfConfig::get('parameters_uapv_auth_plugin_use_your_email_address_for_login');
                                $this->getContext()->getUser()->setFlash('error', $error, false) ;
                            }
                        }
                    }
                }
                else {
                    $error = sfConfig::get('parameters_uapv_auth_plugin_fill_all_fields');
                    $this->getContext()->getUser()->setFlash('error', $error, false) ;
                }
            }
            // =================== Demande d'authentification
            else 
            {
                $resp = $bd->checkPassword($form_info['username'], $form_info['password']) ;
                if($resp)
                {
                    $this->getContext()->getUser()->signIn($form_info['username']) ;
                    $this->getContext()->getUser()->addCredentials('member') ;
                    $this->redirect($request->getReferer()) ;
                }
                else
                {
                    $error = sfConfig::get('parameters_uapv_auth_plugin_wrong_login_or_password');
                    $this->getContext()->getUser()->setFlash('error', $error, false) ;
                }
            }
        }
        else if($auth_type == 'ldap')
        {
            $ldap = new uapvLdap() ;
            $this->getContext()->set('ldap',$ldap);

            // "uid=..." à changer, pour utiliser les paramètres de configuration
            // pour que ça marche avec un LDAP qui n'a pas des uid mais des trululuid.
            $resp = $ldap->checkPassword(sfConfig::get('app_profile_var_translation_uid','uid')."=".$form_info['username'], $form_info['password']) ;

            if($resp)
            {
                // Si l'utilisateur a entré le bon login et le bon mdp, on l'autorise
                // à accéder à l'appli.
                $this->getContext()->getUser()->signIn($form_info['username']) ;
                $this->getContext()->getUser()->addCredentials('member') ;
                $this->redirect($request->getReferer()) ;
            }
            else
            {
                $error = sfConfig::get('parameters_uapv_auth_plugin_wrong_login_or_password');
                $this->getContext()->getUser()->setFlash('error', $error) ;

            }
        }
      }
    }
  }

  public function executeLogout (sfWebRequest $request) 
  {
    $this->getContext()->getUser()->signOut ();
    error_reporting (ini_get ('error_reporting') & ~E_STRICT & ~E_NOTICE);

    if(sfConfig::get ('app_cas_server_host'))
    {
      // Le filtre uapvSecurityFilterCas n'ayant pas forcément été déclenché
      // on force l'appel phpCas::client()
      phpCAS::client (sfConfig::get ('app_cas_server_version', CAS_VERSION_2_0),
                      sfConfig::get ('app_cas_server_host', 'localhost'),
                      sfConfig::get ('app_cas_server_port', 443),
                      sfConfig::get ('app_cas_server_path', ''),
                      false); // Don't call session_start again,
                              // symfony already did it

      // Redirection vers le CAS
      phpCAS::logoutWithRedirectService ($request->getParameter ('redirect',
                                           $this->getContext ()
                                                ->getController ()
                                                ->genUrl ('@homepage', true)));
    }
    else $this->redirect('@homepage', true) ;
  }
}
