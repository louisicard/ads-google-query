<?php

namespace LouisSicard\GoogleQuery\Processor;

use CtSearchBundle\Processor\ProcessorFilter;
use LouisSicard\GoogleQuery\Classes\GoogleQuery;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class GoogleQueryFilter extends ProcessorFilter
{
  function getDisplayName()
  {
    return "Google query";
  }

  function getFields()
  {
    return array(
      'records'
    );
  }

  function getArguments()
  {
    return array('query' => 'Text query');
  }

  public function getSettingsForm($controller)
  {
    $formBuilder = parent::getSettingsForm($controller)
      ->add('setting_google_domain', TextType::class, array(
        'required' => false,
        'label' => $controller->get('translator')->trans('Google domain (E.g.: www.google.com)'),
      ))
      ->add('setting_nb_pages', TextType::class, array(
        'required' => false,
        'label' => $controller->get('translator')->trans('Number of pages'),
      ))
      ->add('ok', SubmitType::class, array('label' => $controller->get('translator')->trans('OK')));
    return $formBuilder;
  }

  function execute(&$document)
  {
    try {
      $settings = $this->getSettings();
      $googleDomain = isset($settings['google_domain']) ? $settings['google_domain'] : 'www.google.com';
      $nbPages = isset($settings['nb_pages']) ? (int)$settings['nb_pages'] : 1;
      $query = $this->getArgumentValue('query', $document);

      if (!empty($query)) {
        $googleQuery = new GoogleQuery($query, $nbPages, $googleDomain);
        return array('records' => $googleQuery->execute());
      }
      return array('records' => []);

    } catch (\Exception $ex) {
      return array('records' => []);
    }
  }

}