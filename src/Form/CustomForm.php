<?php
/*
|--------------------------------------------------------------------------
| customForm.php
|--------------------------------------------------------------------------|
| author Gianlucca Augusto <gianlucca.augusto@extreme.digital>
| version 1.0
| copyright Proderj 2022.
*/
 
namespace Drupal\custom\Form;

use Drupal\Core\Database\Database;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

class CustomForm extends FormBase {
    public function getFormId() {
      return 'custom_form_id';
    }

    public function buildForm(array $form, FormStateInterface $form_state) {

        $form['nome'] = array(
            '#type' => 'textfield',
            '#title' => t('Nome Completo'),
            '#size' => 60,      
            '#maxlength' => 60,
            '#required' => TRUE,
            '#attributes'=> [
              'placeholder' => 'Nome Completo',
              'class' => ['inputs']
            ]
        );

        $form['email'] = array(
            '#type' => 'email',
            '#title' => t('E-mail'),
            '#size' => 60,
            '#maxlength' => 100,
            '#required' => TRUE,
            '#attributes'=> [
              'placeholder' => 'E-mail válido',
              'class' => ['inputs']
            ]
        );

        $form['telefone'] = array(
            '#type' => 'textfield',
            '#title' => t('Telefone'),
            '#size' => 60,
            '#maxlength' => 100,
            '#required' => TRUE,
            '#attributes'=> [
              'placeholder' => 'DDD + Telefone',
              'class' => ['inputs']
            ]
        );

        $form['msg'] = array(
            '#type' => 'textarea',
            '#title' => t('Mensagem'),
            '#size' => 60,
            '#maxlength' => 1500,
            '#required' => TRUE,
            '#resizable' => 'none',
            '#attributes'=> [
              'class' => ['inputs'],
              'placeholder' => 'Mensagem',
            ]
        );

        $form['#attributes']['enctype'] = 'multipart/form-data';
        $form['submit'] = array(
          '#type' => 'submit',
          '#value' => t('Enviar'),
          '#attributes'=> [
            'class' => ['botao']
          ]
        );
        return $form;
    }

   /**
   * @return array
   */
  private function getAllowedFileExtensions(){
    return array('pdf');
  }

  /**
   * @param $entity_type
   * @return string
   */
  public function buildFileLocaton($entity_type){
    // Build file location
    return $entity_type.'/'.date('Y_m_d');
  }

    /**
     * {@inheritdoc}
    */
    public function validateForm(array &$form, FormStateInterface $form_state) {
        /**
         * Valida nome
         * Apenas letras
         */
        $nome = trim($form_state->getValue('nome'));
        
        if (!preg_match("/^([a-zA-Z ]+)$/", $nome)) {
            $form_state->setErrorByName('nome', $this->t('Carateres inválidos no seu nome'));
        }
        
        /**
         * Valida email
         */
        $email = trim($form_state->getValue('email'));
    
        if ($email !== '' && !\Drupal::service('email.validator')->isValid($email)) {
        $form_state->setErrorByName('email', $this->t('Endereço de email inválido'));  
        }
    }

    /**
     * {@inheritdoc}
    */

    public function submitForm(array &$form, FormStateInterface $form_state) {
        /**
         * Pega os dados do Imput
         */
        $nome = trim($form_state->getValue('nome'));
        $email = trim($form_state->getValue('email'));
        $telefone = trim($form_state->getValue('telefone'));
        $msg = trim($form_state->getValue('msg'));
    
        $files = $form_state->getValue('files');

        /**
        * Pegando os arquivos
        */
        $filenames = array();
        foreach ($files as $fid) {
        $file = File::load($fid);
        $file->setPermanent();
        $file->save();
        $name = $file->getFilename();
        $url = file_create_url($file->getFileUri());
        $filenames [] = [$name, $url];
        
        }
        /**
         * Pega o email que será enviado 
         */
        $config = $this->config('custom.adminsettings');
        $custom_admin_email = trim($config->get('custom_admin_email'));
        
        if ($custom_admin_email) {
            /**
             * Envia email
             */
            $this->logger($str);
            $mail_manager = \Drupal::service('plugin.manager.mail');
            $langcode = \Drupal::currentUser()->getPreferredLangcode();

            $params['message']['nome'] = $nome;
            $params['message']['email'] = $email;
            $params['message']['telefone'] = $telefone;
            $params['message']['msg'] = $msg;
        
            $params['message']['customfiles'] = $filenames;

            
            $to = $custom_admin_email;
            //envia email para o email que foi salvo no painel de administrativo
            $result = $mail_manager->mail('custom', 'custom_notificacao', $to, $langcode, $params, NULL, 'true');
            //envia protocolo para o usuário que solicitou o email
            $result = $mail_manager->mail('custom', 'custom_protocolo', $email, $langcode, $params, NULL, 'true');
        }

        /**
         * Retorna mensagem Sucesso
         */
        \Drupal::messenger()->addStatus(t('Obrigado ' . $nome . ',sua mensagem foi enviada com sucesso, para seu email!'));
    }
}
