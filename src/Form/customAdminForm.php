<?php  
/*
|--------------------------------------------------------------------------
| customAdminForm.php
|--------------------------------------------------------------------------|
| author Gianlucca Augusto <gianlucca.augusto@extreme.digital>
| version 1.0
| copyright Proderj 2022.
*/

/**  
 * @file  
 * Contains Drupal\custom\Form\customAdminForm.  
 */  

namespace Drupal\custom\Form;  

use Drupal\Core\Form\ConfigFormBase;  
use Drupal\Core\Form\FormStateInterface;  

/**
 * Configuração do formulário de custom
 */
class customAdminForm extends ConfigFormBase {  
  /**  
   * {@inheritdoc}  
   */  
  protected function getEditableConfigNames() {  
    return [  
      'custom.adminsettings',  
    ];  
  }  

  /**  
   * {@inheritdoc}  
   */  
  public function getFormId() {  
    return 'custom_admin_form';  
  }  
  
  /**  
   * {@inheritdoc}  
   */  
  public function buildForm(array $form, FormStateInterface $form_state) {  
    $config = $this->config('custom.adminsettings');  

    $form['custom_admin_email'] = array(  
      '#type' => 'email',  
      '#title' => $this->t('Email'),  
      '#description' => $this->t('Endereço de e-mail para o qual os dados do formulário de custom devem ser enviados'),  
      '#default_value' => $config->get('custom_admin_email'),  
      '#required' => TRUE,
    );
    $form['submit'] = array(
      '#type' => 'submit',
      '#value' => t('Salvar'),
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    /**
     * Valida email
     */
    $custom_admin_email = trim($form_state->getValue('custom_admin_email'));
  
    if ($custom_admin_email !== '' && !\Drupal::service('email.validator')->isValid($custom_admin_email)) {
      $form_state->setErrorByName('custom_admin_email', $this->t('E-mail inválido!'));  
    }
  }

  /**  
   * {@inheritdoc}  
   */  
  public function submitForm(array &$form, FormStateInterface $form_state) {  
    $this->config('custom.adminsettings')  
      ->set('custom_admin_email', trim($form_state->getValue('custom_admin_email')))  
      ->save();  
  }    
}
