<?php

namespace Drupal\digitalia_muni_computed_titles\Service;

/**
 * The ComputedTitles service. Returns computed title for Issues and Volumes.
 */
class ComputedTitles {

  public function getComputedTitle($node) {

    // Page titles for issues in format [serial] [year], vol.[volume], iss.[issue]

    if ($node->getType() == 'issue') {
      $volume_id = $node->get('field_member_of')->getValue()[0]['target_id'];
      $volume = \Drupal\node\Entity\Node::load($volume_id);

      $computed_title = '';

      if ($volume) {
        $v_title = $volume->getTitle();
        $v_type = t('vol.');
        $serial_id = $volume->get('field_member_of')->getValue()[0]['target_id'];
        $serial =  \Drupal\node\Entity\Node::load($serial_id);
        if ($serial) {
          $s_title = $serial->getTitle();
          $computed_title = $s_title.' ';
        }
        $year = $node->get('field_publication_year')->getValue()[0]['value'];
        $computed_title = $computed_title.$year.', '.$v_type.' '.$v_title.', ';
      }

      $title = $node->getTitle();
      $type = t('iss.');

      $computed_title = $computed_title.$type.' '.$title;
      return $computed_title;

    // Page titles for volumes in format [serial] [year], vol.[volume]

    } elseif ($node->getType() == 'volume') {
      $title = $node->getTitle();
      $type = t('vol.');

      $serial_id = $node->get('field_member_of')->getValue()[0]['target_id'];
      $serial =  \Drupal\node\Entity\Node::load($serial_id);
      if ($serial) {
        $s_title = $serial->getTitle();
        $computed_title = $s_title.' ';
      }

      $year = $node->get('field_years')->getValue()[0]['first'];
      $year_to = $node->get('field_years')->getValue()[0]['second'];
      if ($year_to) {
        $year = $year.'-'.$year_to;
      }

      $computed_title = $computed_title.$year.', '.$type.' '.$title;
      return $computed_title;

    // Return preferred name for authors, instead of their ID

    } elseif ($node->getType() == 'author') {
      $query = \Drupal::entityQuery('node')->condition('field_author_id', $node->id());
      $nids = $query->execute();
      $preferred_name = "";
      foreach($nids as $nid) {
        $author_name = \Drupal\node\Entity\Node::load($nid);
        if($author_name->get('field_preferred')->getValue()[0]['value'] == 1) {
          $preferred_name = $author_name->getTitle();
        }
      }
      if(!empty($preferred_name)) {
        return $preferred_name;
      }

    // Only node title for other content types.

    } else {
      return $node->getTitle();
    }
  }
}
