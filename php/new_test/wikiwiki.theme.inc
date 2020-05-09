<?php

/**
 * @file
 * Theme functions used for normal file output.
 */

/**
 * Implements template_preprocess_hook() for wikiwiki_views.
 *
 * @param array $vars
 *   All the variables available for the theme.
 */
function template_preprocess_wikiwiki_view(&$vars) {
  drupal_add_css(drupal_get_path('module', 'wikiwiki') . '/css/wikiwiki.css');

  $vars['wikiwiki_left'] = theme('wikiwiki_search_box');
  $vars['wikiwiki_left'] .= theme('wikiwiki_menu');
  $wikiwiki_right = '';
  $find = (isset($_GET['find']) && trim($_GET['find']) != "") ? filter_xss($_GET['find']) : NULL;

  if (isset($find)) {
    $wikiwiki_right .= theme('wikiwiki_search_page', $find);
  }
  elseif (isset($vars['wid'])) {
    // Adds Settings links if user has the permission.
    if (user_access('manage pages wikiwiki')) {
      $wikiwiki_right .= theme('wikiwiki_options', $vars['wid']);
    }

    // Pages.
    $query = "SELECT title, body, format FROM {wikiwiki} WHERE wid = %d";
    $r = db_fetch_array(db_query($query, $vars['wid']));

    $body = wikiwiki__filter_make('prepare', $r['format'], $r['body']);
    $body = wikiwiki__filter_make('process', $r['format'], $body);
    $wikiwiki_right .= '<h2 class="title">' . $r['title'] . '</h2>' . $body;
  }
  else {
    // Homepage.
    $settings = variable_get('wikiwiki_settings', array());
    $hp_text = wikiwiki__filter_make('prepare', $settings['hp_format'], $settings['hp_text']);
    $hp_text = wikiwiki__filter_make('process', $settings['hp_format'], $hp_text);
    if (isset($settings['hp_options']) && $settings['hp_options'] == 1) {
      $wikiwiki_right .= $hp_text;
    }
    elseif (isset($settings['hp_options']) && $settings['hp_options'] == 2) {
      $path = drupal_get_path('module', 'wikiwiki') . '/img/wikiwiki.png';
      $wikiwiki_right .= theme('image', $path, 'WikiWiki');
      $wikiwiki_right .= $hp_text;
    }
    else {
      $path = drupal_get_path('module', 'wikiwiki') . '/img/wikiwiki.png';
      $wikiwiki_right .= theme('image', $path, 'WikiWiki');
    }
  }
  $vars['wikiwiki_right'] = $wikiwiki_right;
}

/**
 * Implements template_preprocess_hook() for wikiwiki_menu.
 *
 * @param array $vars
 *   All the variables available for the theme.
 */
function template_preprocess_wikiwiki_menu(&$vars) {
  $vars['menu_link'] = menu_tree('wikiwiki');
}

/**
 * Implements template_preprocess_hook() for wikiwiki_options.
 *
 * @param array $vars
 *   All the variables available for the theme.
 */
function template_preprocess_wikiwiki_options(&$vars) {
  $wid = $vars['wid'];
  $view = l(t('View'), 'admin/wikiwiki/page/' . $wid);
  $edit = l(t('Edit'), 'admin/wikiwiki/page/' . $wid . '/edit');
  $delete = l(t('Delete'), 'admin/wikiwiki/page/' . $wid . '/delete');
  $vars['options'] = $view . ' | ' . $edit . ' | ' . $delete;
}

/**
 * Implements template_preprocess_hook() for wikiwiki_search_box.
 *
 * @param array $vars
 *   All the variables available for the theme.
 */
function template_preprocess_wikiwiki_search_box(&$vars) {
  $vars['form'] = drupal_get_form('wikiwiki_search_form');
}

/**
 * Implements template_preprocess_hook() for wikiwiki_search_page.
 *
 * @param array $vars
 *   All the variables available for the theme.
 */
function template_preprocess_wikiwiki_search_page(&$vars) {
  $find = filter_xss($vars['find']);
  $vars['rows'] = '';
  $vars['pager'] = '';
  $vars['noresults'] = t('Your search - <b>@find</b> - did not match any documents.', array('@find' => $find));

  $element = 0;
  $limit = 20;
  $query = "SELECT wid, title, body
            FROM {wikiwiki}
            WHERE title LIKE '%%%s%'
            OR body LIKE '%%%s%'
            ORDER BY timestamp DESC";
  $q = pager_query($query, $limit, $element, NULL, $find, $find);
  $rows = array();
  while ($r = db_fetch_array($q)) {
    $body = filter_xss($r['body']);
    $rows[] = array(
      'title' => l($r['title'], 'admin/wikiwiki/page/' . $r['wid']),
      'teaser' => trim(substr(strip_tags($body), 0, 200)) . ' ...',
    );
  }
  if (count($rows) > 0) {
    $vars['rows'] = $rows;
    $vars['pager'] = theme('pager', NULL, $limit, $element);
  }
}