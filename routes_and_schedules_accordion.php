<?php
/*
Plugin Name: Routes and Schedules Accordion
Version: 1.1
Author: Drew Carey Buglione
License: GPLv2 or later
*/

class Routes_and_Schedules_Accordion
{
	public function Routes_and_Schedules_Accordion()
	{
		$this->options = $this->get_options_with_defaults();
		add_action('admin_menu', array($this, 'menu_entry'));
		add_action('admin_init', array($this, 'settings_init'));
		add_action('wp_enqueue_scripts', array($this, 'stylesheet'));
		add_action('wp_enqueue_scripts', array($this, 'javascript'));
		add_action('wp_enqueue_scripts', array($this, 'google_opensans'));
		add_action('admin_enqueue_scripts', array($this, 'jquery_ui_admin'));
		add_action('admin_footer', array($this, 'jquery_ui_admin_load'));
		add_shortcode('routes_and_schedules_accordion', array($this, 'shortcode_display'));
	}
		
	var $options;

	function get_options_with_defaults()
	{
		$defaults = array(
			'google_maps_api_key' => '',
			'number_of_routes' => '1',
			'1_route_name' => 'Route 1 - Hill Valley (Example)',
			'1_route_service_area' => 'Hill Street, Courthouse Square, Main Street',
			'1_route_current_schedule_pdf_url' => 'http://example.com/route_1_hill_valley_current.pdf',
			'1_route_current_schedule_effective_date' => '11/12/55',
			'1_route_upcoming_schedule_pdf_url' => 'http://example.com/route_1_hill_valley_upcoming.pdf',
			'1_route_upcoming_schedule_effective_date' => '10/21/15',
			'1_route_google_map_msid' => '216327600948105941037.0004ac8d8adc2a29b8e94',
			'1_route_google_map_dimensions' => '640x320',
			'1_route_google_map_api_string' => '&markers=33.991203%2C-117.926613&markers=34.377377%2C-118.477272&markers=34.374142%2C-118.477325&markers=34.238983%2C-118.433464&markers=34.179119%2C-118.319740&markers=34.151665%2C-118.160843&markers=34.126968%2C-118.133453&markers=34.105701%2C-118.141502&markers=34.106178%2C-118.141510&markers=34.105289%2C-118.141510&markers=34.105556%2C-118.141518&markers=34.153454%2C-118.005699&markers=34.141361%2C-118.349731&markers=34.242153%2C-118.433128&markers=33.935509%2C-117.633301&markers=34.208431%2C-119.197166&markers=34.068008%2C-118.005440&markers=34.068638%2C-118.005409&markers=34.193127%2C-118.320786&markers=33.979790%2C-118.044075&markers=34.104809%2C-118.338829&markers=33.981030%2C-118.042717&markers=34.137630%2C-118.358482&markers=33.780174%2C-118.237343&markers=34.121414%2C-118.296516&markers=34.123680%2C-118.300995&markers=34.209316%2C-118.769333&markers=37.865788%2C-120.503922&markers=37.950478%2C-120.417290&markers=37.922787%2C-120.433617&markers=36.973141%2C-110.091011&markers=34.145008%2C-119.195290&markers=34.139927%2C-118.358940',
		);
		
		$current_options = get_option('routes_and_schedules_accordion_settings');
		if (isset($current_options['number_of_routes'])){$number_of_routes = $current_options['number_of_routes'];}
		elseif (!isset($current_options['number_of_routes'])){$number_of_routes = 1;}
		
		for ($i=2; $i<=$number_of_routes; $i++)
		{
			$defaults["{$i}_route_name"] = $defaults["{$i}_route_service_area"] = $defaults["{$i}_route_current_schedule_pdf_url"] = $defaults["{$i}_route_current_schedule_effective_date"] = $defaults["{$i}_route_upcoming_schedule_pdf_url"] = $defaults["{$i}_route_upcoming_schedule_effective_date"] = $defaults["{$i}_route_google_map_msid"] = $defaults["{$i}_route_google_map_api_string"] = '';
			$defaults["{$i}_route_google_map_dimensions"] = '640x320';
		}
			
		return wp_parse_args($current_options, $defaults);
	}

	function menu_entry() 
	{
		add_options_page('Routes and Schedules Accordion', 'Routes and Schedules Accordion', 'manage_options', 'routes_and_schedules_accordion', array($this, 'submenu_settings_content'));
	}

	function submenu_settings_content() 
	{
	?>
		<div class="wrap">
			<h2>Routes and Schedules Accordion</h2>
			<form action="options.php" method="post">
			<?php settings_fields('routes_and_schedules_accordion_settings'); ?>
			<?php do_settings_sections('routes_and_schedules_accordion'); ?>
				<input name="Submit" type="submit" class="button-primary" value="<?php esc_attr_e('Save Changes'); ?>" />
			</form>
	<?php
	}
	
	function settings_init()
	{		
		register_setting('routes_and_schedules_accordion_settings', 'routes_and_schedules_accordion_settings', array($this, 'settings_validate' ));
		add_settings_section('routes_and_schedules_accordion_general', 'General Settings', array($this, 'settings_text'), 'routes_and_schedules_accordion');
		
		add_settings_field('routes_and_schedules_accordion_settings_google_maps_api_key', 'Google Maps API Key', array($this, 'setting_google_maps_api_key'), 'routes_and_schedules_accordion', 'routes_and_schedules_accordion_general');
		
		add_settings_field('routes_and_schedules_accordion_settings_number_of_routes', 'Number of Routes', array($this, 'setting_number_of_routes'), 'routes_and_schedules_accordion', 'routes_and_schedules_accordion_general');
		
		add_settings_section("routes_and_schedules_accordion_admincordion_start", 'Individual Route Settings', array($this, 'admincordion_start'), 'routes_and_schedules_accordion');
		
		for ($i=1; $i<=$this->options['number_of_routes']; $i++)
		{
			add_settings_section("routes_and_schedules_accordion_{$i}", "#{$i}", array($this, 'admincordion_unit_start'), 'routes_and_schedules_accordion');
			add_settings_field("routes_and_schedules_accordion_settings_{$i}_route_name", 'Route Name', array($this, 'setting_route_name'), 'routes_and_schedules_accordion', "routes_and_schedules_accordion_{$i}", $i);
			add_settings_field("routes_and_schedules_accordion_settings_{$i}_route_service_area", 'Route Service Area', array($this, 'setting_route_service_area'), 'routes_and_schedules_accordion', "routes_and_schedules_accordion_{$i}", $i);
			add_settings_field("routes_and_schedules_accordion_settings_{$i}_route_current_schedule_pdf_url", 'Route Current Schedule PDF URL', array($this, 'setting_route_current_schedule_pdf_url'), 'routes_and_schedules_accordion', "routes_and_schedules_accordion_{$i}", $i);
			add_settings_field("routes_and_schedules_accordion_settings_{$i}_route_current_schedule_effective_date", 'Route Current Schedule Effective Date', array($this, 'setting_route_current_schedule_effective_date'), 'routes_and_schedules_accordion', "routes_and_schedules_accordion_{$i}", $i);
			add_settings_field("routes_and_schedules_accordion_settings_{$i}_route_upcoming_schedule_pdf_url", 'Route Upcoming Schedule PDF URL', array($this, 'setting_route_upcoming_schedule_pdf_url'), 'routes_and_schedules_accordion', "routes_and_schedules_accordion_{$i}", $i);
			add_settings_field("routes_and_schedules_accordion_settings_{$i}_route_upcoming_schedule_effective_date", 'Route Upcoming Schedule Effective Date', array($this, 'setting_route_upcoming_schedule_effective_date'), 'routes_and_schedules_accordion', "routes_and_schedules_accordion_{$i}", $i);
			add_settings_field("routes_and_schedules_accordion_settings_{$i}_route_google_map_dimensions", 'Route Google Map Dimensions<br/>(in pixels)', array($this, 'setting_route_google_map_dimensions'), 'routes_and_schedules_accordion', "routes_and_schedules_accordion_{$i}", $i);
			add_settings_field("routes_and_schedules_accordion_settings_{$i}_route_google_map_msid", 'Route Google Map MSID', array($this, 'setting_route_google_map_msid'), 'routes_and_schedules_accordion', "routes_and_schedules_accordion_{$i}", $i);
			add_settings_field("routes_and_schedules_accordion_settings_{$i}_route_google_map_api_string", "Route Google Map API (<b>&path</b>, etc.) String<br/><a href='http://static-maps-generator.appspot.com/url?msid={$this->options[$i.'_route_google_map_msid']}' target='_blank' style='background: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAoAAAAKCAYAAACNMs+9AAAAVklEQVR4Xn3PgQkAMQhDUXfqTu7kTtkpd5RA8AInfArtQ2iRXFWT2QedAfttj2FsPIOE1eCOlEuoWWjgzYaB/IkeGOrxXhqB+uA9Bfcm0lAZuh+YIeAD+cAqSz4kCMUAAAAASUVORK5CYII=) center right no-repeat; padding-right: 13px;' >Third-Party Generator</a>", array($this, 'setting_route_google_map_api_string'), 'routes_and_schedules_accordion', "routes_and_schedules_accordion_{$i}", $i);
			add_settings_section("routes_and_schedules_accordion_{$i}_end", '', array($this, 'admincordion_unit_end'), 'routes_and_schedules_accordion');
		}
		
		add_settings_section("routes_and_schedules_accordion_admincordion_end", '', array($this, 'admincordion_end'), 'routes_and_schedules_accordion');
	}

	function settings_text()
	{
		//Placeholder
	}

	function settings_validate($input)
	{
		// Create our array for storing the validated options
		$output = array();
		
		// Loop through each of the incoming options
		foreach( $input as $key => $value ) {
			
			// Check to see if the current option has a value. If so, process it.
			if( isset( $input[$key] ) ) {
			
				// Strip all HTML and PHP tags and properly handle quoted strings
				$output[$key] = esc_attr(strip_tags(stripslashes($input[$key])));
				
			} // end if
			
		} // end foreach
		
		// Return the array
		return $output;
	}
	
	function setting_google_maps_api_key()
	{
		echo "<input type='text' name='routes_and_schedules_accordion_settings[google_maps_api_key]' size='80' value='{$this->options['google_maps_api_key']}' />";
	}
	
	function setting_number_of_routes()
	{
		echo "<input type='text' id='admin-route-number-spinner' name='routes_and_schedules_accordion_settings[number_of_routes]' size='3' value='{$this->options['number_of_routes']}' />&nbsp;&nbsp;<input name='Update' type='submit' class='button-primary' value='Update' />";
	}
	
	function admincordion_start()
	{
		echo '<div style="width: 900px;" id="admincordion">';
	}
	
	function admincordion_unit_start()
	{
		echo '<div>';
	}

	function setting_route_name($i)
	{
		echo "<input type='text' name='routes_and_schedules_accordion_settings[{$i}_route_name]' size='80' value='{$this->options[$i.'_route_name']}' />";
	}

	function setting_route_service_area($i)
	{
		echo "<input type='text' name='routes_and_schedules_accordion_settings[{$i}_route_service_area]' size='80' value='{$this->options[$i.'_route_service_area']}' />";
	}

	function setting_route_current_schedule_pdf_url($i)
	{
		echo "<input type='text' name='routes_and_schedules_accordion_settings[{$i}_route_current_schedule_pdf_url]' size='80' value='{$this->options[$i.'_route_current_schedule_pdf_url']}' />";
	}

	function setting_route_current_schedule_effective_date($i)
	{
		echo "<input type='text' class='admin-schedule-datepicker' name='routes_and_schedules_accordion_settings[{$i}_route_current_schedule_effective_date]' size='80' value='{$this->options[$i.'_route_current_schedule_effective_date']}' />";
	}

	function setting_route_upcoming_schedule_pdf_url($i)
	{
		echo "<input type='text' name='routes_and_schedules_accordion_settings[{$i}_route_upcoming_schedule_pdf_url]' size='80' value='{$this->options[$i.'_route_upcoming_schedule_pdf_url']}' />";
	}

	function setting_route_upcoming_schedule_effective_date($i)
	{
		echo "<input type='text' class='admin-schedule-datepicker' name='routes_and_schedules_accordion_settings[{$i}_route_upcoming_schedule_effective_date]' size='80' value='{$this->options[$i.'_route_upcoming_schedule_effective_date']}' />";
	}

	function setting_route_google_map_dimensions($i)
	{
		echo "<input type='text' name='routes_and_schedules_accordion_settings[{$i}_route_google_map_dimensions]' size='7' value='{$this->options[$i.'_route_google_map_dimensions']}' />";
	}
	
	function setting_route_google_map_msid($i)
	{
		echo "<input type='text' name='routes_and_schedules_accordion_settings[{$i}_route_google_map_msid]' size='80' value='{$this->options[$i.'_route_google_map_msid']}' />";
	}
	
	function setting_route_google_map_api_string($i)
	{
		echo "<textarea name='routes_and_schedules_accordion_settings[{$i}_route_google_map_api_string]' rows='5' cols='90'>{$this->options[$i.'_route_google_map_api_string']}</textarea>";
	}
	
	function admincordion_unit_end()
	{
		echo '</div>';
	}
	
	function admincordion_end()
	{
		echo '</div><br/>';
	}

	function stylesheet()
	{
		//Stylesheet
		wp_register_style('routes_and_schedules_accordion_style', plugins_url('css/frontend.css', __FILE__));
		wp_enqueue_style('routes_and_schedules_accordion_style');
	}
	
	function google_opensans()
	{
		//Google OpenSans Font
		wp_register_style('routes_and_schedules_accordion_google_opensans', 'http://fonts.googleapis.com/css?family=Open+Sans');
		wp_enqueue_style('routes_and_schedules_accordion_google_opensans');
	}

	function javascript()
	{
		//Frontend Javascript
		wp_register_script('routes_and_schedules_accordion_js', plugins_url('js/frontend.js', __FILE__), array('jquery'));
		wp_enqueue_script('routes_and_schedules_accordion_js');
	}

	function jquery_ui_admin()
	{
		//jQuery UI Datepicker (".admin-schedule-datepicker")
		wp_register_script('jquery-ui-datepicker', 'jquery-ui-datepicker');
		wp_enqueue_script('jquery-ui-datepicker');
		//jQuery UI Accordion ("#admin-route-number-spinner")
		wp_register_script('jquery-ui-accordion', 'jquery-ui-accordion');
		wp_enqueue_script('jquery-ui-accordion');
		//jQuery UI Spinner("#admincordion")
		wp_register_script('jquery-ui-spinner', 'jquery-ui-spinner');
		wp_enqueue_script('jquery-ui-spinner');
		//Stylesheet
		wp_register_style('jquery-ui-admin-css', plugins_url('css/admin.css', __FILE__));
		wp_enqueue_style('jquery-ui-admin-css');
	}
	
	function jquery_ui_admin_load()
	{
		?>
		<script type="text/javascript">
		jQuery(document).ready(function(){
			jQuery('.admin-schedule-datepicker').datepicker({
				dateFormat : 'mm/dd/y'
			});
			jQuery('#admincordion').accordion({
				collapsible: true,
				active: false
			});
			jQuery('#admin-route-number-spinner').spinner({
				culture: 'en-US',
				min: 1,
				max: 99
			});
		});
		</script>
		<?php
	}

	function shortcode_display()
	{
		ob_start(); //Buffer output so we can return it as a shortcode
				
		?>
		<ul class="routes-and-schedules-accordion">
		<?php for ($i=1; $i<=$this->options['number_of_routes']; $i++)
		{if (($this->options[$i.'_route_name'] != '') and ($this->options[$i.'_route_service_area'] != '') and ($this->options[$i.'_route_current_schedule_pdf_url'] != '') and ($this->options[$i.'_route_current_schedule_effective_date'] != '')){?>
			<li class="routes-and-schedules-accordion-li">
				<div class="routes-and-schedules-accordion-title">
					<h5 class="routes-and-schedules-accordion-route-name"><?php echo $this->options[$i.'_route_name'] ?></h5>
					<span class="routes-and-schedules-accordion-route-service-area"><b>Serving:</b> <?php echo $this->options[$i.'_route_service_area'] ?></span>
				</div>
				<div class="routes-and-schedules-accordion-content">
					<a class="routes-and-schedules-accordion-pdf-link" href="<?php echo $this->options[$i.'_route_current_schedule_pdf_url'] ?>">Current Schedule PDF<span>(Effective: <?php echo $this->options[$i.'_route_current_schedule_effective_date'] ?>)</span></a><br><br>
					<?php if (($this->options[$i.'_route_upcoming_schedule_pdf_url'] != '') and ($this->options[$i.'_route_upcoming_schedule_effective_date'] != ''))
					{?>
					<a class="routes-and-schedules-accordion-pdf-link" href="<?php echo $this->options[$i.'_route_upcoming_schedule_pdf_url'] ?>">Upcoming Schedule <span>(Effective: <?php echo $this->options[$i.'_route_upcoming_schedule_effective_date'] ?>)</span></a><br><br>
					<?php }?>
					<?php if (($this->options[$i.'_route_google_map_msid'] != '') and ($this->options[$i.'_route_google_map_dimensions'] != '') and ($this->options[$i.'_route_google_map_dimensions'] != '_route_google_map_api_string'))
					{?>
					<a href="http://maps.google.com/maps/ms?ie=UTF8&hl=en&msa=0&msid=<?php echo $this->options[$i.'_route_google_map_msid'] ?>"><img class="routes-and-schedules-accordion-map-img" src="http://maps.google.com/maps/api/staticmap?size=<?php echo $this->options[$i.'_route_google_map_dimensions'] ?><?php echo $this->options[$i.'_route_google_map_api_string'] ?>&sensor=false<?php if ($this->options['google_maps_api_key'] != ''){echo '&key='.$this->options['google_maps_api_key'];} ?>"></a>
					<?php }?>
				</div>
			</li>
		<?php }} ?>
		</ul>
		<?php
		
		$output = ob_get_contents();
		ob_end_clean();
		return $output;
	}
}

$Routes_and_Schedules_Accordion = new Routes_and_Schedules_Accordion();
?>