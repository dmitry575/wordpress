<?php
/*
Plugin Name: WP Comment Replace
Plugin URI: http://prodvigenie-kazan.ru/
Description: Замена ссылок в комментариях на свои
Version: 1.1
Author: Dmitry
Author URI: http://prodvigenie-kazan.ru/
Text Domain: WP-Comment-Replace
Domain Path: /lang
*/
class WPCommentReplace
  {
  const FILE_URLS      = "urls.txt";
  const FILE_ANCHORS   = "anchors.txt";
  const FILE_STOPWORDS = "stopwords.txt";
  //---
  private $m_urls;
  private $m_urls_count;
//---
  private $m_anchors;
  private $m_anchors_count;
//---
  private $m_stopwords;
  private $m_stopwords_count;
  //---
  private $m_clear_links = false;
  private $m_max_comments = 0;
  private $m_max_links = 0;

  /**
   * Инцилизация данных
   */
  public function Init()
    {
    $fname = dirname(__FILE__) . "/" . self::FILE_URLS;
    if(file_exists($fname))
      {
      $wp_comment_replace_urls = explode("\n", file_get_contents($fname));
      array_walk($wp_comment_replace_urls, create_function('&$val', '$val = !is_array($val) ? trim($val):$val;'));
      $this->m_urls       = $wp_comment_replace_urls;
      $this->m_urls_count = count($wp_comment_replace_urls);
      }
    //---
    $fname = dirname(__FILE__) . "/" . self::FILE_ANCHORS;
    if(file_exists($fname))
      {
      $wp_comment_replace_urls = explode("\n", file_get_contents($fname));
      array_walk($wp_comment_replace_urls, create_function('&$val', '$val = !is_array($val) ? trim($val):$val;'));
      $this->m_anchors       = $wp_comment_replace_urls;
      $this->m_anchors_count = count($wp_comment_replace_urls);
      }
    //---
    $fname = dirname(__FILE__) . "/" . self::FILE_STOPWORDS;
    if(file_exists($fname))
      {
      $wp_comment_replace_urls = explode("\n", file_get_contents($fname));
      array_walk($wp_comment_replace_urls, create_function('&$val', '$val = !is_array($val) ? trim($val):$val;'));
      $this->m_stopwords       = $wp_comment_replace_urls;
      $this->m_stopwords_count = count($wp_comment_replace_urls);
      }
    }

  public function GetRandUrl()
    {
    if($this->m_urls_count > 0) return $this->m_urls[rand(0, $this->m_urls_count - 1)];
    return '';
    }

  public function GetUrlsStr()
    {
    return !empty($this->m_urls) ? join("\r\n", $this->m_urls) : '';
    }

  public function GetAnchorsStr()
    {
    return !empty($this->m_anchors) ? join("\r\n", $this->m_anchors) : '';
    }

  public function GetStopwordsStr()
    {
    return !empty($this->m_stopwords) ? join("\r\n", $this->m_stopwords) : '';
    }

  public function SaveUrls($data)
    {
    $fname = dirname(__FILE__) . "/" . self::FILE_URLS;
    $infos = explode("\n", $data);
    array_walk($infos, create_function('&$val', '$val = !is_array($val) ? trim($val):$val;'));
    $this->m_urls       = $infos;
    $this->m_urls_count = count($this->m_urls);
    file_put_contents($fname, $data);
    }

  public function SaveAnchors($data)
    {
    $fname = dirname(__FILE__) . "/" . self::FILE_ANCHORS;
    $infos = explode("\n", $data);
    array_walk($infos, create_function('&$val', '$val = !is_array($val) ? trim($val):$val;'));
    $this->m_anchors       = $infos;
    $this->m_anchors_count = count($this->m_anchors);
    file_put_contents($fname, $data);
    }

  public function SaveStopWords($data)
    {
    $fname = dirname(__FILE__) . "/" . self::FILE_STOPWORDS;
    $infos = explode("\n", $data);
    array_walk($infos, create_function('&$val', '$val = !is_array($val) ? trim($val):$val;'));
    $this->m_stopwords       = $infos;
    $this->m_stopwords_count = count($this->m_stopwords);
    file_put_contents($fname, $data);
    }

  public function ReplaceLinks(&$content)
    {
    $this->m_max_comments = get_option('wp_comment_replace_count');
    $this->m_max_links    = get_option('wp_comment_replace_links');
//---
    $comments = $links = 0;
//---
    $this->GetLastData($comments, $links);
    if($comments >= $this->m_max_comments) return false;
    //---
    if($links >= $this->m_max_links) $this->m_clear_links=true;
    //---
    $content = str_replace(array('\\\'',
                                 '\\"'), array('\'',
                                               '"'), $content);
    $content = preg_replace_callback('%<a.*href=[\'"](.*)[\'"][^>]*>(.*)</a>%isU', array($this,
                                                                                         'Replace'), $content);
    //var_dump(preg_replace_callback('%<a.*href=[\'"](.*)[\'"][^>]*>(.*)</a>%', array($this,'Replace'), $content));
    return true;
    }

  private function IsUrl($url)
    {
    $w           = "a-z0-9";
    $url_pattern = "#(
    (?:f|ht)tps?://(?:www.)?
    (?:[$w\\-.]+/?\\.[a-z]{2,4})/?
    (?:[$w\\-./\\#]+)?
    (?:\\?[$w\\-&=;\\#]+)?
    )#xi";
    $a           = preg_match($url_pattern, $url);
    return $a;
    }

  private function GetRandAnchor($default = '')
    {
    if($this->m_anchors_count <= 0) return $default;
    return $this->m_anchors[rand(0, $this->m_anchors_count - 1)];
    }

  private function GetLastData(&$comments, &$links)
    {
    global $wpdb;
    $comms = $wpdb->get_results("SELECT * FROM {$wpdb->comments} WHERE comment_date > NOW() - INTERVAL 1 DAY ", ARRAY_A);
    if(empty($comms)) return;
    foreach((array)$comms as $c)
      {
      $links += substr_count(strtolower($c['comment_content']), '<a ');
      $comments++;
      }
    }

  public function Replace($matches)
    {
    if($this->m_clear_links)
      {
      return $this->GetRandAnchor($matches[2]);
      }
    $result = str_replace($matches[1], $this->GetRandUrl(), $matches[0]);
    $result = str_replace($matches[2], $this->GetRandAnchor($matches[2]), $result);
    return $result;
    }

  public function CheckStopWords($content)
    {
    if($this->m_stopwords_count > 0) return true;
    foreach($this->m_stopwords as $word)
      {
      if(empty($word)) continue;
      if(stristr($content, trim($word)) !== false) return false;
      }
    return true;
    }
  }
function load_wp_comment_replace_lang()
  {
  $currentLocale = get_locale();
  if(!empty($currentLocale))
    {
    $moFile = dirname(__FILE__) . "/lang/wp-comment-replace-" . $currentLocale . ".mo";
    if(@file_exists($moFile) && is_readable($moFile)) load_textdomain('WP-Comment-Replace', $moFile);
    }
  }

add_filter('init', 'load_wp_comment_replace_lang');
$wp_comment_replace_urls = array();
if(!defined('WASINFO'))
  {
  define('WASINFO', '(Protected by <a href="http://prodvigenie-kazan.ru/" target="_blank">WP Comment Replace</a>)');
  }
function wp_comment_replace($comment_data)
  {
  $replace = new WPCommentReplace();
  $replace->Init();
//--- у пользователя подставляем свой урл
  $comment_data['comment_author_url'] = $replace->GetRandUrl();
//---
  $content = $comment_data['comment_content'];
  if(!$replace->CheckStopWords($content))
    {
    $comment_data['comment_content'] = '';
    return null;
    };
//--- находим все <a href=
//
//preg_match_all("/(<a[^>]+href=[\"'])([^'\"]*)(['\"][^>]*>)([.])*<\/a>/is",$content, $matches, PREG_OFFSET_CAPTURE | PREG_PATTERN_ORDER  );
//preg_match_all('~<a [^<>]*href=[\'"]([^\'"]+)[\'"][^<>]*>(((?!~si',$content, $matches);
//preg_match_all('#<a[^>]+href=[\"\']([^\'\"]+)[\'\"][^>?]*>(.*?)<\/a>#is',$content, $matches);
//--- /http:\/\/([^\s]+)/
//preg_replace_callback('/<a\s[^>]*href=([\"\']??)([^\"\' >]*?)\\1[^>]*>(.*)<\/a>/siU',wp_comment_replace_updateurl, $content);
//preg_replace_callback("/\<a[^>]*href=[\'|\"](.*?)[^\'|^\"]\>([^<]*)\<\/a\>/isU",wp_comment_replace_updateurl, $content);
  if(!$replace->ReplaceLinks($content))
    {
    return null;
    }
  if(empty($content)) return null;
//--- находим все урлы вида http:// или www.
  $comment_data['comment_content'] = $content;
  return ($comment_data);
  }

add_filter('preprocess_comment', 'wp_comment_replace', 1);
function was_commenter_replace_check($incoming_comment)
  {
  $isposing = 0;
  if(get_option("wp_anti_spam_nicknames") != '')
    {
    $wp_anti_spam_nicknames = explode(",", get_option("wp_anti_spam_nicknames"));
    foreach($wp_anti_spam_nicknames as $wp_anti_spam_nickname)
      {
      if($incoming_comment['comment_author'] == trim($wp_anti_spam_nickname))
        {
        $isposing = 1;
        }
      }
    }
  if(get_option("wp_anti_spam_emails") != '')
    {
    $wp_anti_spam_emails = explode(",", get_option("wp_anti_spam_emails"));
    foreach($wp_anti_spam_emails as $wp_anti_spam_email)
      {
      if($incoming_comment['comment_author_email'] == trim($wp_anti_spam_email))
        {
        $isposing = 1;
        }
      }
    }
  if(!$isposing || is_user_logged_in())
    {
    return $incoming_comment;
    }
  wp_die(__('Error: Only the logged user can use this nickname or email.', 'WP-Comment-Replace') . WASINFO);
  }

add_filter('preprocess_comment', 'was_commenter_replace_check', 1);
function wp_comment_replace_activate()
  {
  add_option('wp_comment_replace_count', '10');
  add_option('wp_comment_replace_links', '100');
  add_option('wp_comment_replace_deactivate', '');
  }

register_activation_hook(__FILE__, 'wp_comment_replace_activate');
if(get_option("wp_comment_replace_deactivate") == 'yes')
  {
  function wp_comment_replace_deactivate()
    {
    global $wpdb;
    $remove_options_sql = "DELETE FROM $wpdb->options WHERE $wpdb->options.option_name like 'wp_comment_replace_%'";
    $wpdb->query($remove_options_sql);
    }

  register_deactivation_hook(__FILE__, 'wp_comment_replace_deactivate');
  }
function wp_comment_replace_settings_link($action_links, $plugin_file)
  {
  if($plugin_file == plugin_basename(__FILE__))
    {
    $was_settings_link = '<a href="options-general.php?page=' . dirname(plugin_basename(__FILE__)) . '/wp_comment_replace_admin.php">' . __("Settings") . '</a>';
    array_unshift($action_links, $was_settings_link);
    }
  return $action_links;
  }

add_filter('plugin_action_links', 'wp_comment_replace_settings_link', 10, 2);
if(is_admin())
  {
  require_once('wp_comment_replace_admin.php');
  }
?>