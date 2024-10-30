<?php 
/*
Plugin Name: CF7 Google Captcha Load After Page
Description: Get google captcha code after the page is completed loaded. This is best for your website speed improvement score.
Author: Amit Bhalani
Author URI: http://amitbhalani.me
Version: 3.0.1
*/

/* Define ABCF7 captcha */
class ABCF7_captcha {
	
	/* set the default constuctor of ABCL comment */
	function __construct() {
		
		add_action('init',array($this, 'ABCF7_check_captcha_active'));
		
	}
	
	/* check google captcha_active */
	public function ABCF7_check_captcha_active()
	{
		$wpcf7 = get_option('wpcf7');
		
		if(isset($wpcf7['recaptcha']) && !empty($wpcf7['recaptcha'])){
			
			$sitekey = $this->ABCF7_get_sitekey( $wpcf7['recaptcha']);
			$secret = $this->ABCF7_get_secret( $sitekey , $wpcf7['recaptcha']);
			
			if( !empty( $sitekey ) && !empty( $secret ) ){
				/* store sitekey value SITEKEY variable */ 
				define( 'SITEKEY', $sitekey ); 
				add_action( 'wp_enqueue_scripts', array($this, 'ABCF7_remove_google_captcha_js'), 21, 0);
				add_action( 'wp_footer', array($this,'ABCF7_footer_script'), 100 );
				
			}
			
		} 
	}
	
	/* get sitekey from  wpcf7 option */
	public function ABCF7_get_sitekey($sitekeys) {
		if ( empty( $sitekeys )
		or ! is_array( $sitekeys ) ) {
			return false;
		}
		$sitekeys = array_keys( $sitekeys );
		return $sitekeys[0];
	}
	
	/* get sitesecret from  wpcf7 option */
	public function ABCF7_get_secret( $sitekey , $sitekeys) {
		if ( isset( $sitekeys[$sitekey] ) ) {
			return $sitekeys[$sitekey];
		} else {
			return false;
		}
	}
	
	/* remove default google-recaptcha js file */
	public function ABCF7_remove_google_captcha_js(){
		wp_dequeue_script('google-recaptcha');
		wp_dequeue_script('wpcf7-recaptcha');
	}
	
	/* add new google-recaptcha in footer */
	public function ABCF7_footer_script(){
		?>
		<script>
		var fired = false;
		window.addEventListener("scroll", function(){
		if ((document.documentElement.scrollTop != 0 && fired === false) || (document.body.scrollTop != 0 && fired === false)) {

			var abcf7head = document.getElementsByTagName('head')[0];
			var script= document.createElement('script');
			script.type= 'text/javascript';
			script.src= 'https://www.google.com/recaptcha/api.js?render=<?php echo SITEKEY; ?>&ver=3.0';
			abcf7head.appendChild(script);
				
			
			var wpcf7_recaptcha = {"sitekey":"<?php echo SITEKEY; ?>","actions":{"homepage":"homepage","contactform":"contactform"}};
	 
			setTimeout(function(){ 
				wpcf7_recaptcha.execute = function( action ) {
					grecaptcha.execute(
						wpcf7_recaptcha.sitekey,
						{ action: action }
					).then( function( token ) {
						var event = new CustomEvent( 'wpcf7grecaptchaexecuted', {
							detail: {
								action: action,
								token: token,
							},
						} );

						document.dispatchEvent( event );
					} );
				};

				wpcf7_recaptcha.execute_on_homepage = function() {
					wpcf7_recaptcha.execute( wpcf7_recaptcha.actions[ 'homepage' ] );
				};

				wpcf7_recaptcha.execute_on_contactform = function() {
					wpcf7_recaptcha.execute( wpcf7_recaptcha.actions[ 'contactform' ] );
				};

				grecaptcha.ready(
					wpcf7_recaptcha.execute_on_homepage
				);

				document.addEventListener( 'change',
					wpcf7_recaptcha.execute_on_contactform
				);

				document.addEventListener( 'wpcf7submit',
					wpcf7_recaptcha.execute_on_homepage
				);
				
				
				document.addEventListener( 'wpcf7grecaptchaexecuted', function( event ) {
					var fields = document.querySelectorAll(
						"form.wpcf7-form input[name='_wpcf7_recaptcha_response']"
					);

					for ( var i = 0; i < fields.length; i++ ) {
						var field = fields[ i ];
						field.setAttribute( 'value', event.detail.token );
					}
				} );
				
			},4000);
			
			fired = true;
		  }
		}, true)
		</script>
	<?php }
}

/*
 * Instantiate the class.
 */
$abcf7 = new ABCF7_captcha(); // go