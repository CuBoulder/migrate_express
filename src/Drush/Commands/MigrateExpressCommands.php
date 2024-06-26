<?php

namespace Drupal\migrate_express\Drush\Commands;

use Consolidation\OutputFormatters\StructuredData\RowsOfFields;
use Drupal\Core\Utility\Token;
use Drupal\shortcode\ShortcodeService;
use Drush\Attributes as CLI;
use Drush\Commands\DrushCommands;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * A Drush commandfile.
 */
final class MigrateExpressCommands extends DrushCommands {

  /**
   * Constructs a MigrateExpressCommands object.
   */
  public function __construct(
    private readonly Token $token,
    private readonly ShortcodeService $shortcode,
  ) {
    parent::__construct();
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('token'),
      $container->get('shortcode'),
    );
  }

  /**
   * Convert shortcodes in to CKEditor5 HTML.
   */
  #[CLI\Command(name: 'migrate_express:shortcode-convert', aliases: ['scc'])]
  #[CLI\Option(name: 'option-name', description: 'Option description')]
  #[CLI\Usage(name: 'migrate_express:shortcode-convert', description: 'Usage description')]
  public function commandName($arg1, $options = ['option-name' => 'default']) {

    $this->logger()->success(dt('Loading service...'));
    $sc = \Drupal::service('shortcode');

    $this->logger()->success(dt('Loading nids...'));
    $nids = \Drupal::entityQuery('node')->condition('type','basic_page')->accessCheck(FALSE)->execute();
    $this->logger()->success(print_r($nids, true));

    $this->logger()->success(dt('Loading nodes...'));
    $nodes =  \Drupal\node\Entity\Node::loadMultiple($nids);
    $this->logger()->success(print_r($nodes, true));

    $block_content_fields = [];
    $block_content_fields[] = 'body'; //value, summary, format
    $block_content_fields[] = 'field_text'; //value, format
    $block_content_fields[] = 'field_text_block_body'; //value, format
    $block_content_fields[] = 'field_video_reveal_text_overlay'; //value, format

    $media_fields = [];
    $media_fields[] = 'field_media_image_caption'; //value, format

    $node_fields = [];
    $node_fields[] = 'body'; //value, summary, format
    $node_fields[] = 'field_collection_item_summary'; //value, format
    $node_fields[] = 'field_newsletter_intro_body'; //value, format
    $node_fields[] = 'field_ucb_person_address'; //value, format
    $node_fields[] = 'field_ucb_person_office_hours'; //value, format


    $paragraph_fields = [];
    $paragraph_fields[] = 'field_article_text'; //value, format
    $paragraph_fields[] = 'field_expandable_content_body'; //value, format
    $paragraph_fields[] = 'field_grid_layout_content_text'; //value, format
    $paragraph_fields[] = 'field_newsletter_block_text'; //value, format
    $paragraph_fields[] = 'field_newsletter_content_text'; //value, format
    $paragraph_fields[] = 'field_sequence_item_descr'; //value, format
    $paragraph_fields[] = 'field_slide_image_text'; //value, format
    $paragraph_fields[] = 'field_text'; //value, format
    $paragraph_fields[] = 'field_ucb_faq_answer'; //value, format
    $paragraph_fields[] = 'field_ucb_faq_section_body'; //value, format


    $taxonomy_term_fields = [];
    $taxonomy_term_fields[] = 'field_newsletter_footer'; //value, format


    foreach ($nodes as $node)
    {
      $this->logger()->success(dt('Processing body field...'));
      $node->set('body',  ['value' => $sc->postprocessText($sc->process($node->get('body')->value), 'en'), 'format' => $node->get('body')->format, 'summary' => $node->get('body')->summary]);
      $node->save();

    }
//
//    $this->logger()->success(dt('Processing body field...'));
//
//    $this->logger()->success(print_r($sc->process($nodes[57]->get('body')->value, true)));
//
//
//    $nodes[57]->set('body',  ['value' => $sc->postprocessText($sc->process($nodes[57]->get('body')->value), 'en'), 'format' => $nodes[57]->get('body')->format, 'summary' => $nodes[57]->get('body')->summary]);
//    $this->logger()->success(dt('Saving...'));
//    $nodes[57]->save();
    $this->logger()->success(dt('Done...'));

    $this->logger()->success(dt('Achievement unlocked.'));
  }

  /**
   * An example of the table output format.
   */
  #[CLI\Command(name: 'migrate_express:token', aliases: ['token'])]
  #[CLI\FieldLabels(labels: [
    'group' => 'Group',
    'token' => 'Token',
    'name' => 'Name'
  ])]
  #[CLI\DefaultTableFields(fields: ['group', 'token', 'name'])]
  #[CLI\FilterDefaultField(field: 'name')]
  public function token($options = ['format' => 'table']): RowsOfFields {
    $all = $this->token->getInfo();
    foreach ($all['tokens'] as $group => $tokens) {
      foreach ($tokens as $key => $token) {
        $rows[] = [
          'group' => $group,
          'token' => $key,
          'name' => $token['name'],
        ];
      }
    }
    return new RowsOfFields($rows);
  }

}
