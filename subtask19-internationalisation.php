 FUNCTION           RETURNS/ECHOES   ESCAPING          USE WHEN
  ────────           ──────────────   ────────          ────────                                                                              
  __()               returns          none              PHP logic, sprintf, arrays                                                            
  _e()               echoes           none              direct HTML output                                                                    
  esc_html__()       returns          HTML entities      inside <tags>                                                                        
  esc_html_e()       echoes           HTML entities      inside <tags>                                                                        
  esc_attr__()       returns          attribute-safe     inside attributes                                                                    
  esc_attr_e()       echoes           attribute-safe     inside attributes                                                                    
  _n()               returns          none              singular/plural
  _x()               returns          none              ambiguous words                                                                       
  _nx()              returns          none              plural + context                                                                      
  
  RULES                                                                                                                                       
  ───────────────────────────────────────────────────────────────────────
  Never concatenate          → use printf/sprintf with placeholders                                                                           
  Never translate variables  → translate the template, insert values                                                                          
  Always include text domain → __('text', 'myplugin'), not __('text')                                                                         
  Always escape output       → esc_html__() in HTML, esc_attr__() in attributes                                                               
  Add translator comments    → /* translators: %s is ... */ before every placeholder                                                          
  One complete sentence      → never split a sentence across multiple __() calls 
<?php 
// In PHP logic → __()
   $label = __( 'Save Changes', 'myplugin' );                                                                                                  
  add_menu_page( $label, $label, 'manage_options', 'my-settings' );
                                                                                                                                              
  // In HTML content → esc_html__() or esc_html_e()                                                                                           
  echo '<button>' . esc_html__( 'Save Changes', 'myplugin' ) . '</button>';                                                                   
                                                                                                                                              
  // In HTML attributes → esc_attr__() or esc_attr_e()
  echo '<input title="' . esc_attr__( 'Your full name', 'myplugin' ) . '">';                                                                  
                                                                                                                                              
  // In JavaScript localization → __()
  wp_localize_script( 'my-app', 'myAppI18n', array(                                                                                           
      'confirmDelete' => __( 'Are you sure you want to delete this?', 'myplugin' ),                                                           
      'saving'        => __( 'Saving...', 'myplugin' ),                                                                                       
      'saved'         => __( 'Saved!', 'myplugin' ),                                                                                          
  )); 
  // ── WRONG — translator gets 3 fragments, can't reorder ──                                                                                 
  echo __( 'Order #', 'myplugin' ) . $id . __( ' placed by ', 'myplugin' ) . $name;
 // correct way of doing it 
printf(                                                                                                                                     
      /* translators: %1$d is order number, %2$s is customer name */
      esc_html__( 'Order #%1$d placed by %2$s', 'myplugin' ),                                                                                 
      $id,                                                                                                                                    
      esc_html( $name )                                                                                                                       
  ); 

 printf(
      /* translators: %s is the product name */                                                                                               
      esc_html__( 'Added "%s" to your cart.', 'myplugin' ),
      esc_html( $product_name )                                                                                                               
  );              
                                                                                                                                              
  // Multiple: numbered placeholders
  printf(
      /* translators: %1$s is product name, %2$s is formatted price */
      esc_html__( '%1$s — %2$s each', 'myplugin' ),                                                                                           
      esc_html( $product_name ),                                                                                                              
      wc_price( $price )                                                                                                                      
  );                                                                                                                                          
                  
  // With HTML (use sprintf, not printf, to return)                                                                                           
  $message = sprintf(
      /* translators: %s is a URL */                                                                                                          
      __( 'View your <a href="%s">order history</a>.', 'myplugin' ),                                                                          
      esc_url( $orders_url )                                                                                                                  
  );                                                                                                                                          
  echo wp_kses( $message, array( 'a' => array( 'href' => array() ) ) ); 

// setup the file transaltion 
// Tell WordPress where to find .mo files for your plugin
add_action( 'init', function() {                                                                                                            
    load_plugin_textdomain(
        'myplugin',    // text domain — matches your __() calls                                                                             
        false,                                                                                                                              
        dirname( plugin_basename( __FILE__ ) ) . '/languages'
        // looks in: wp-content/plugins/myplugin/languages/                                                                                 
    );                                                                                                                                      
});



// 1. Mark translatable strings in code                                                                                                   

// Use WP's gettext wrappers — each takes your text domain (must match your plugin slug) as the second argument.                          
                
// Plain translation                                                                                                                   
$msg = __( 'Hello world', 'myplugin' );                                                                                                

// Echo directly                                                                                                                       
_e( 'Save changes', 'myplugin' );
                                                                                                                                        
// Escape for HTML context
echo esc_html__( 'Settings', 'myplugin' );                                                                                             
echo esc_attr__( 'Click here', 'myplugin' );                                                                                           
                                                                                                                                        
// With placeholders                                                                                                                   
printf(                                                                                                                                
    /* translators: %s: user name */
    esc_html__( 'Welcome, %s', 'myplugin' ),                                                                                           
    esc_html( $name )
);                                                                                                                                     
                
// Plurals                                                                                                                             
_n( '%d item', '%d items', $count, 'myplugin' );
                                                                                                                                        
// Context disambiguation (same word, different meaning)                                                                               
_x( 'Post', 'noun', 'myplugin' );                                                                                                      
                                                                                                                                        
//Rule: strings must be literal — __( $var, 'myplugin' ) won't be extracted.                                                             

// 2. Declare the text domain                                                                                                             
                
// In your plugin header:                                                                                                                 
                
/**
 * Plugin Name: My Plugin
 * Text Domain: myplugin                                                                                                               
 * Domain Path: /languages
 */                                                                                                                                    
                
//Then load it on init (or plugins_loaded for older WP):                                                                                 

add_action( 'init', function () {                                                                                                      
    load_plugin_textdomain(                                                                                                            
        'myplugin',
        false,                                                                                                                         
        dirname( plugin_basename( __FILE__ ) ) . '/languages'
    );                                                                                                                                 
} );
                                                                                                                                        
                                                                                                                                        
// 3. Extract strings into a .pot template

// Install WP-CLI and its i18n command package:                                                                                           

// wp package install wp-cli/i18n-command                                                                                                 
                                                                                                                                        
// From your plugin root, scan all PHP/JS files and write the template:                                                                   
                                                                                                                                        
//wp i18n make-pot . languages/myplugin.pot \                                                                                            
//     --domain=myplugin \                                                                                                                
//     --slug=myplugin
                                                                                                                                        
// This produces languages/myplugin.pot — a template listing every translatable string with file:line references. Regenerate it whenever  
// strings change.
                                                                                                                                        
// 4. Translators create .po files per locale                                                                                             

// A translator copies the .pot and fills in translations. Filenames must follow {textdomain}-{locale}.po:                                
                
// languages/myplugin-fr_FR.po                                                                                                            
// languages/myplugin-es_ES.po                                                                                                            
// languages/myplugin-de_DE.po
                                                                                                                                        
// Tools translators typically use:                                                                                                       
// - Poedit (desktop GUI) — opens the .pot, lets you save as .po, compiles .mo automatically on save
// - GlotPress (web, what WordPress.org uses)                                                                                             
// - Loco Translate (WordPress plugin that edits files in-place)
                                                                                                                                        
// 5. Compile .po → .mo                                                                                                                   
                                                                                                                                        
// WordPress loads the binary .mo at runtime, not the .po. Poedit compiles on save; from CLI:                                             
                
// # Using msgfmt (from gettext package)                                                                                                  
// msgfmt languages/myplugin-fr_FR.po -o languages/myplugin-fr_FR.mo                                                                      
                                                                                                                                        
// # Or WP-CLI (compiles every .po in a directory)                                                                                        
// wp i18n make-mo languages/                                                                                                             
                                                                                                                                        
// 6. For JavaScript translations (block editor, React)                                                                                   

// JS uses wp.i18n — same function names:                                                                                                 
                
// import { __, _n, sprintf } from '@wordpress/i18n';                                                                                     
// const label = __( 'Save', 'myplugin' );
                                                                                                                                        
// JS needs a JSON file per script handle, not .mo. The flow:                                                                             
                                                                                                                                        
// # After .po files exist, generate .json files                                                                                          
// wp i18n make-json languages/ --no-purge                                                                                                

// Then enqueue with translations:                                                                                                        
                
// wp_enqueue_script( 'myplugin-block', $url, $deps, $ver );                                                                              
// wp_set_script_translations( 'myplugin-block', 'myplugin', plugin_dir_path( __FILE__ ) . 'languages' );                                 
                                                                                                                                        
// 7. Ship the files                                                                                                                      
                                                                                                                                        
// Your final languages/ directory looks like:                                                                                            

// languages/                                                                                                                             
// ├── myplugin.pot                      ← template (optional to ship, but convention)
// ├── myplugin-fr_FR.po                 ← source for translators                                                                         
// ├── myplugin-fr_FR.mo                 ← what WP actually reads                                                                         
// ├── myplugin-fr_FR-myplugin-block.json  ← JS translations                                                                              
// └── ...                                                                                                                                
                                                                                                                                        
// Only .mo and .json files are strictly required at runtime; shipping .po/.pot is good practice so translators can contribute.           
                                                                                                                                        
// Typical dev loop                                                                                                                       
                
// # 1. Edit PHP/JS, add new __() strings
// # 2. Regenerate template                                                                                                               
// wp i18n make-pot . languages/myplugin.pot --domain=myplugin
                                                                                                                                        
// # 3. Merge new strings into existing .po files
// wp i18n update-po languages/myplugin.pot languages/                                                                                    
                                                                                                                                        
// # 4. Translators fill in new entries (or you do)
// # 5. Recompile                                                                                                                         
// wp i18n make-mo languages/                                                                                                             
// wp i18n make-json languages/ --no-purge
                                                                                                                                        
// # 6. Test by switching site language in Settings → General                                                                             

// How WordPress picks the translation at runtime                                                                                         
                
// 1. User's site locale is set (e.g. fr_FR in wp-config.php or Settings → General).                                                      
// 2. WP looks in wp-content/languages/plugins/myplugin-fr_FR.mo first (community translations from translate.wordpress.org).
// 3. Falls back to your plugin's languages/myplugin-fr_FR.mo.                                                                            
// 4. If neither exists, strings show in the original (English) source.                                                                   
                                                                                                                                        
// That's the complete loop — write with gettext wrappers → extract .pot → translate to .po → compile .mo/.json → ship in /languages.   