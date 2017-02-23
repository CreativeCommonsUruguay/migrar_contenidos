<?php

namespace Drupal\migrar_contenidos\Form;

use Drupal\Core\Form\ConfigFormBase;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\File\File;
use \Drupal\Core\Url;

/**
 * Defines a form that configures forms module settings.
 */
class ModuleConfigurationForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'migrar_contenidos_admin_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'migrar_contenidos.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, Request $request = NULL) {

    $config = \Drupal::service('config.factory')->getEditable('migrar_contenidos.settings');

    $form['archivo_a_importarUsuarios'] = array(
      '#type' => 'managed_file',
      '#title' => t('Archivo CSV de usuarios:'),
      '#default_value' => '',
      '#upload_location' => 'public://',
      '#upload_validators' => array('file_validate_extensions' => array('csv')),
    );

    $form['archivo_a_importarTaxonomias'] = array(
      '#type' => 'managed_file',
      '#title' => t('Archivo CSV de taxonomias:'),
      '#default_value' => '',
      '#upload_location' => 'public://',
      '#upload_validators' => array('file_validate_extensions' => array('csv')),
    );

    $form['archivo_a_importarContenidos'] = array(
      '#type' => 'managed_file',
      '#title' => t('Archivo CSV de autores:'),
      '#default_value' => '',
      '#upload_location' => 'public://',
      '#upload_validators' => array('file_validate_extensions' => array('csv')),
    );



    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = \Drupal::service('config.factory')->getEditable('migrar_contenidos.settings');
    $values = $form_state->getValues();

    $config->set('idArchivoContenidos', $values['archivo_a_importarContenidos']) ->save();
    $config->set('idArchivoUsuarios', $values['archivo_a_importarUsuarios']) ->save();
    $config->set('idArchivoTaxonomias', $values['archivo_a_importarTaxonomias']) ->save();

    $url = Url::fromRoute('migrar_contenidos.content');
    $form_state->setRedirectUrl($url);

    parent::submitForm($form, $form_state);
  }
}
