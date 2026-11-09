<?php
// Loaded only by the fresh local Playground browser runner.
if ('cli' !== PHP_SAPI || !defined('ABSPATH')) { exit(1); }
if ('local' !== wp_get_environment_type() || !defined('PROOF_DISPOSABLE_TEST_SITE') || true !== PROOF_DISPOSABLE_TEST_SITE) { throw new RuntimeException('Disposable local test site required'); }
wp_mkdir_p(WPMU_PLUGIN_DIR);
file_put_contents(WPMU_PLUGIN_DIR.'/proof-disposable-header.php', '<?php add_action("send_headers", function(){ header("X-Proof-Disposable-Test-Site: 1"); });');
if (!in_array(wp_parse_url(home_url(), PHP_URL_HOST), ['127.0.0.1', 'localhost'], true)) { throw new RuntimeException('Loopback fixture required'); }
if (get_posts(['post_type'=>'any', 'post_status'=>array_keys(get_post_stati()), 'numberposts'=>1])) { throw new RuntimeException('Empty disposable database required'); }
if ('proof' !== get_stylesheet() || '7.1.3' !== $GLOBALS['wp_version']) { throw new RuntimeException('Pinned WordPress and active Proof required'); }
// switch_theme() changes the active theme but does not load its functions in
// this same bootstrap request. Subsequent HTTP requests load them normally.
require_once get_template_directory().'/functions.php';
// Match the established site-title configuration; all article content stays synthetic.
update_option('blogname','Jim Lunsford');
$GLOBALS['wp_rewrite']->set_permalink_structure('/%postname%/');
update_option('blog_public',1);
$projects = wp_insert_term('Projects','category',['slug'=>'projects']);
$law = wp_insert_term('Law Enforcement & Corrections','category',['slug'=>'law-enforcement-corrections']);
$pages=[];
foreach (['Projects'=>'projects','Books'=>'books','The PERIOD Code'=>'period-code','How to Rebuild Yourself'=>'how-to-rebuild-yourself','The Discipline Loop'=>'discipline-loop','The Disciplined Operator'=>'disciplined-operator'] as $title=>$slug) {
 $pages[$slug]=wp_insert_post(['post_type'=>'page','post_title'=>$title,'post_name'=>$slug,'post_status'=>'publish','post_content'=>'<p>Disposable acceptance fixture.</p>']);
}
update_post_meta($pages['projects'],'_wp_page_template','page-projects.php');
for($i=0;$i<14;$i++) {
 wp_insert_post(['post_title'=>'Project fixture '.($i+1),'post_name'=>'project-fixture-'.($i+1),'post_status'=>'publish','post_category'=>[$projects['term_id']],'post_date'=>gmdate('Y-m-d H:i:s',time()-($i+1)*DAY_IN_SECONDS),'post_content'=>'<!-- wp:paragraph --><p>Useful project narrative for the disposable responsive acceptance site. This writing demonstrates the original public summary presentation.</p><!-- /wp:paragraph -->']);
}
wp_insert_post(['post_title'=>'Why I Loved the Work','post_name'=>'why-i-loved-the-work','post_status'=>'publish','post_category'=>[$law['term_id']],'post_content'=>'<p>A synthetic law story used only in disposable acceptance.</p>']);
$article='<!-- wp:paragraph --><p>'.str_repeat('A useful article paragraph about building reliable publishing tools. ',12).'</p><!-- /wp:paragraph --><!-- wp:heading --><h2 class="wp-block-heading">Code and narrow screens</h2><!-- /wp:heading --><!-- wp:code --><pre class="wp-block-code"><code>'.str_repeat('A-long-code-line-with-breaks-available-',12).'</code></pre><!-- /wp:code --><!-- wp:paragraph --><p>'.str_repeat('The next practical lesson remains readable across desktop and mobile layouts. ',15).'</p><!-- /wp:paragraph -->';
$browser_article=wp_insert_post(['post_title'=>'Browser Acceptance Article','post_name'=>'browser-article','post_status'=>'publish','post_category'=>[$projects['term_id']],'post_content'=>$article]);
$book_paths = ['one'=>'period-code', 'three'=>'how-to-rebuild-yourself', 'four'=>'discipline-loop', 'two'=>'disciplined-operator'];
foreach (proof_get_identity_published_books_defaults() as $key=>$book) {
 // Synthetic covers keep the fixture offline. Optional local copies of the
 // published covers let visual review use real artwork without any live writes.
 $source = '/proof-covers/'.$book['image_id'];
 $files = glob($source.'.*');
 if ($files) {
  $bytes=file_get_contents($files[0]);
  $filename=basename($files[0]);
  $dimensions=getimagesize($files[0]);
  if (!$dimensions) throw new RuntimeException('Invalid review cover');
 } else {
  $cover=imagecreatetruecolor(400,600);
  imagefill($cover,0,0,imagecolorallocate($cover,29,38,51));
  imagestring($cover,5,25,200,$book['title'],imagecolorallocate($cover,255,255,255));
  ob_start(); imagepng($cover); $bytes=ob_get_clean(); imagedestroy($cover);
  $filename='fixture-cover-'.$key.'.png';
  $dimensions=[400,600,'mime'=>'image/png'];
 }
 $upload=wp_upload_bits($filename,null,$bytes);
 if ($upload['error']) throw new RuntimeException($upload['error']);
 $id=wp_insert_attachment(['post_title'=>$book['title'].' test cover','post_mime_type'=>$dimensions['mime'],'post_status'=>'inherit'],$upload['file']);
 wp_update_attachment_metadata($id,['width'=>$dimensions[0],'height'=>$dimensions[1],'file'=>_wp_relative_upload_path($upload['file'])]);
 set_theme_mod('proof_identity_published_books_'.$key.'_image_id',$id);
 if ('one' === $key) { set_theme_mod('proof_author_block_image_id',$id); }
 set_theme_mod('proof_identity_published_books_'.$key.'_url',home_url('/'.$book_paths[$key].'/'));
}
// Exercise actual Customizer registration, not just text matching its source.
require_once ABSPATH.WPINC.'/class-wp-customize-manager.php';
$customizer = new WP_Customize_Manager();
proof_customize_register($customizer);
$position=0;
foreach (proof_get_identity_published_books_defaults() as $key=>$book) {
 $position++;
 foreach (['title','description','url','image_id'] as $field) {
  $setting='proof_identity_published_books_'.$key.'_'.$field;
  if (!$customizer->get_setting($setting) || $customizer->get_setting($setting)->default !== $book[$field]) throw new RuntimeException('Missing or reassigned Customizer setting '.$setting);
  $control=$customizer->get_control($setting);
  if (!$control || strpos($control->label,'Book '.$position.' ') !== 0) throw new RuntimeException('Incorrect Customizer display position '.$setting);
 }
 if (!($customizer->get_control('proof_identity_published_books_'.$key.'_image_id') instanceof WP_Customize_Media_Control)) throw new RuntimeException('Media picker required');
}
if (4 !== count(proof_get_identity_published_books())) throw new RuntimeException('Four independent saved cover and destination overrides required');
$menu=wp_create_nav_menu('Acceptance primary menu');
foreach (['Home'=>'/','Projects'=>'/projects/','Books'=>'/books/'] as $label=>$path) {
 wp_update_nav_menu_item($menu,0,['menu-item-title'=>$label,'menu-item-url'=>home_url($path),'menu-item-status'=>'publish','menu-item-type'=>'custom']);
}
set_theme_mod('nav_menu_locations',['primary'=>$menu]);
flush_rewrite_rules();
if ((int) $browser_article !== (int) url_to_postid(get_permalink($browser_article))) { throw new RuntimeException('Article permalink must resolve to its fixture ID'); }
echo 'Browser fixture ready: WordPress '.$wp_version.', PHP '.PHP_VERSION.'\n';
