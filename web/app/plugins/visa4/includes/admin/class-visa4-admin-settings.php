<?php
/**
 * Visa4 Admin Settings Class
 */

defined( 'ABSPATH' ) || exit;

class VISA4_Admin_Settings {

    /**
    * Setting pages.
    *
    * @var array
    */
    private static $settings = array();

	/**
	 * Get a setting from the settings API.
	 *
	 * @param string $option_name Option name.
	 * @param mixed  $default     Default value.
	 *
	 * @return mixed
	 */
	public static function get_option( $option_name, $default = '' ) {
	    // Array value.
		if ( strstr( $option_name, '[' ) ) {
		    parse_str( $option_name, $option_array );
		    // Option name is first key.
			$option_name = current( array_keys( $option_array ) );
			// Get value.
			$option_values = get_option( $option_name, '' );
			$key = key( $option_array[ $option_name ] );
			if ( isset( $option_values[ $key ] ) ) {
			    $option_value = $option_values[ $key ];
			} else {
			    $option_value = null;
			}
		} else {
		    // Single value.
			$option_value = get_option( $option_name, null );
		}
		if ( is_array( $option_value ) ) {
		    $option_value = array_map( 'stripslashes', $option_value );
		} elseif ( ! is_null( $option_value ) ) {
		    $option_value = stripslashes( $option_value );
		}

		return ( null === $option_value ) ? $default : $option_value;
	}

	/**
	* Include the settings page classes.
    *
    * @return VISA4_Settings_Tab[]
	*/
	public static function get_settings_pages() {
	    if ( ! empty( self::$settings ) ) {
	        return self::$settings;
	    }

	    include_once dirname( __FILE__ ) . '/settings/class-visa4-settings-tab.php';

	    self::$settings[] = include 'settings/class-visa4-settings-countries.php';
	    self::$settings[] = include 'settings/class-visa4-settings-visual.php';

	    return self::$settings;
	}

	/**
    * Save the settings.
	*/
	public static function save() {
		check_admin_referer( 'visa4_settings' );
        $options_to_update = array();

		foreach (self::get_settings_pages() as $page) {
		    $options_to_update = array_merge( $options_to_update, $page->get_options_to_update( $_POST ) );
		}

		// Save all options in our array.
		foreach ( $options_to_update as $name => $value ) {
			update_option( $name, $value, 'yes');
		}
	}

	/**
	 * Output admin fields.
	 *
	 * Loops though the options array and outputs each field.
	 *
	 * @param array[] $options options array to output.
	 */
	public static function output_fields( $options ) {
			foreach ( $options as $option ) {
				if ( ! isset( $option['type'] ) ) {
					continue;
				}
				if ( ! isset( $option['id'] ) ) {
					$option['id'] = '';
				}
				if ( ! isset( $option['title'] ) ) {
					$option['title'] = isset( $option['name'] ) ? $option['name'] : '';
				}
				if ( ! isset( $option['class'] ) ) {
					$option['class'] = '';
				}
				if ( ! isset( $option['css'] ) ) {
					$option['css'] = '';
				}
				if ( ! isset( $option['default'] ) ) {
					$option['default'] = '';
				}
				if ( ! isset( $option['desc'] ) ) {
					$option['desc'] = '';
				}
				if ( ! isset( $option['desc_tip'] ) ) {
					$option['desc_tip'] = false;
				}
				if ( ! isset( $option['placeholder'] ) ) {
					$option['placeholder'] = '';
				}
				if ( ! isset( $option['suffix'] ) ) {
					$option['suffix'] = '';
				}

				// Custom attribute handling.
				$custom_attributes = array();

				if ( ! empty( $option['custom_attributes'] ) && is_array( $option['custom_attributes'] ) ) {
					foreach ( $option['custom_attributes'] as $attribute => $attribute_value ) {
						$custom_attributes[] = esc_attr( $attribute ) . '="' . esc_attr( $attribute_value ) . '"';
					}
				}

				// Description handling.
				$field_description = self::get_field_description( $option );
				$description       = $field_description['description'];
				$tooltip_html      = $field_description['tooltip_html'];

				// Switch based on type.
				switch ( $option['type'] ) {

					// Section Titles.
					case 'title':
						if ( ! empty( $option['title'] ) ) {
							echo '<h2>' . esc_html( $option['title'] ) . '</h2>';
						}
						if ( ! empty( $option['desc'] ) ) {
							echo '<div id="' . esc_attr( sanitize_title( $option['id'] ) ) . '-description">';
							echo wp_kses_post( wpautop( wptexturize( $option['desc'] ) ) );
							echo '</div>';
						}
						echo '<table class="form-table">' . "\n\n";

						break;

					// Section Ends.
					case 'sectionend':
						echo '</table>';
						break;

					// Standard text inputs and subtypes like 'number'.
					case 'text':
					case 'password':
					case 'datetime':
					case 'datetime-local':
					case 'date':
					case 'month':
					case 'time':
					case 'week':
					case 'number':
					case 'email':
					case 'url':
					case 'tel':
						$option_value = self::get_option( $option['id'], $option['default'] );

						?><tr valign="top">
							<th scope="row" class="titledesc">
								<label for="<?php echo esc_attr( $option['id'] ); ?>"><?php echo esc_html( $option['title'] ); ?> <?php echo $tooltip_html; // WPCS: XSS ok. ?></label>
							</th>
							<td class="forminp forminp-<?php echo esc_attr( sanitize_title( $option['type'] ) ); ?>">
								<input
									name="<?php echo esc_attr( $option['id'] ); ?>"
									id="<?php echo esc_attr( $option['id'] ); ?>"
									type="<?php echo esc_attr( $option['type'] ); ?>"
									style="<?php echo esc_attr( $option['css'] ); ?>"
									value="<?php echo esc_attr( $option_value ); ?>"
									class="<?php echo esc_attr( $option['class'] ); ?>"
									placeholder="<?php echo esc_attr( $option['placeholder'] ); ?>"
									<?php echo implode( ' ', $custom_attributes ); // WPCS: XSS ok. ?>
									/><?php echo esc_html( $option['suffix'] ); ?> <?php echo $description; // WPCS: XSS ok. ?>
							</td>
						</tr>
						<?php
						break;

					// Textarea.
					case 'textarea':
						$option_value = self::get_option( $option['id'], $option['default'] );

						?>
						<tr valign="top">
							<th scope="row" class="titledesc">
								<label for="<?php echo esc_attr( $option['id'] ); ?>"><?php echo esc_html( $option['title'] ); ?> <?php echo $tooltip_html; // WPCS: XSS ok. ?></label>
							</th>
							<td class="forminp forminp-<?php echo esc_attr( sanitize_title( $option['type'] ) ); ?>">
								<?php echo $description; // WPCS: XSS ok. ?>

								<textarea
									name="<?php echo esc_attr( $option['id'] ); ?>"
									id="<?php echo esc_attr( $option['id'] ); ?>"
									style="<?php echo esc_attr( $option['css'] ); ?>"
									class="<?php echo esc_attr( $option['class'] ); ?>"
									placeholder="<?php echo esc_attr( $option['placeholder'] ); ?>"
									<?php echo implode( ' ', $custom_attributes ); // WPCS: XSS ok. ?>
									><?php echo esc_textarea( $option_value ); // WPCS: XSS ok. ?></textarea>
							</td>
						</tr>
						<?php
						break;

					// Select boxes.
					case 'select':
					case 'multiselect':
						$option_value = self::get_option( $option['id'], $option['default'] );

						?>
						<tr valign="top">
							<th scope="row" class="titledesc">
								<label for="<?php echo esc_attr( $option['id'] ); ?>"><?php echo esc_html( $option['title'] ); ?> <?php echo $tooltip_html; // WPCS: XSS ok. ?></label>
							</th>
							<td class="forminp forminp-<?php echo esc_attr( sanitize_title( $option['type'] ) ); ?>">
								<select
									name="<?php echo esc_attr( $option['id'] ); ?><?php echo ( 'multiselect' === $option['type'] ) ? '[]' : ''; ?>"
									id="<?php echo esc_attr( $option['id'] ); ?>"
									style="<?php echo esc_attr( $option['css'] ); ?>"
									class="<?php echo esc_attr( $option['class'] ); ?>"
									<?php echo implode( ' ', $custom_attributes ); // WPCS: XSS ok. ?>
									<?php echo 'multiselect' === $option['type'] ? 'multiple="multiple"' : ''; ?>
									>
									<?php
									foreach ( $option['options'] as $key => $val ) {
										?>
										<option value="<?php echo esc_attr( $key ); ?>"
											<?php

											if ( is_array( $option_value ) ) {
												selected( in_array( (string) $key, $option_value, true ), true );
											} else {
												selected( $option_value, (string) $key );
											}

										?>
										>
										<?php echo esc_html( $val ); ?></option>
										<?php
									}
									?>
								</select> <?php echo $description; // WPCS: XSS ok. ?>
							</td>
						</tr>
						<?php
						break;

					// Radio inputs.
					case 'radio':
						$option_value = self::get_option( $option['id'], $option['default'] );

						?>
						<tr valign="top">
							<th scope="row" class="titledesc">
								<label for="<?php echo esc_attr( $option['id'] ); ?>"><?php echo esc_html( $option['title'] ); ?> <?php echo $tooltip_html; // WPCS: XSS ok. ?></label>
							</th>
							<td class="forminp forminp-<?php echo esc_attr( sanitize_title( $option['type'] ) ); ?>">
								<fieldset>
									<?php echo $description; // WPCS: XSS ok. ?>
									<ul>
									<?php
									foreach ( $option['options'] as $key => $val ) {
										?>
										<li>
											<label><input
												name="<?php echo esc_attr( $option['id'] ); ?>"
												value="<?php echo esc_attr( $key ); ?>"
												type="radio"
												style="<?php echo esc_attr( $option['css'] ); ?>"
												class="<?php echo esc_attr( $option['class'] ); ?>"
												<?php echo implode( ' ', $custom_attributes ); // WPCS: XSS ok. ?>
												<?php checked( $key, $option_value ); ?>
												/> <?php echo esc_html( $val ); ?></label>
										</li>
										<?php
									}
									?>
									</ul>
								</fieldset>
							</td>
						</tr>
						<?php
						break;

					// Checkbox input.
					case 'checkbox':
						$option_value     = self::get_option( $option['id'], $option['default'] );
						$visibility_class = array();

						if ( ! isset( $option['hide_if_checked'] ) ) {
							$option['hide_if_checked'] = false;
						}
						if ( ! isset( $option['show_if_checked'] ) ) {
							$option['show_if_checked'] = false;
						}
						if ( 'yes' === $option['hide_if_checked'] || 'yes' === $option['show_if_checked'] ) {
							$visibility_class[] = 'hidden_option';
						}
						if ( 'option' === $option['hide_if_checked'] ) {
							$visibility_class[] = 'hide_options_if_checked';
						}
						if ( 'option' === $option['show_if_checked'] ) {
							$visibility_class[] = 'show_options_if_checked';
						}

						if ( ! isset( $option['checkboxgroup'] ) || 'start' === $option['checkboxgroup'] ) {
							?>
								<tr valign="top" class="<?php echo esc_attr( implode( ' ', $visibility_class ) ); ?>">
									<th scope="row" class="titledesc"><?php echo esc_html( $option['title'] ); ?></th>
									<td class="forminp forminp-checkbox">
										<fieldset>
							<?php
						} else {
							?>
								<fieldset class="<?php echo esc_attr( implode( ' ', $visibility_class ) ); ?>">
							<?php
						}

						if ( ! empty( $option['title'] ) ) {
							?>
								<legend class="screen-reader-text"><span><?php echo esc_html( $option['title'] ); ?></span></legend>
							<?php
						}

						?>
							<label for="<?php echo esc_attr( $option['id'] ); ?>">
								<input
									name="<?php echo esc_attr( $option['id'] ); ?>"
									id="<?php echo esc_attr( $option['id'] ); ?>"
									type="checkbox"
									class="<?php echo esc_attr( isset( $option['class'] ) ? $option['class'] : '' ); ?>"
									value="1"
									<?php checked( $option_value, 'yes' ); ?>
									<?php echo implode( ' ', $custom_attributes ); // WPCS: XSS ok. ?>
								/> <?php echo $description; // WPCS: XSS ok. ?>
							</label> <?php echo $tooltip_html; // WPCS: XSS ok. ?>
						<?php

						if ( ! isset( $option['checkboxgroup'] ) || 'end' === $option['checkboxgroup'] ) {
										?>
										</fieldset>
									</td>
								</tr>
							<?php
						} else {
							?>
								</fieldset>
							<?php
						}
						break;

					// Single page selects.
					case 'single_select_page':
						$args = array(
							'name'             => $option['id'],
							'id'               => $option['id'],
							'sort_column'      => 'menu_order',
							'sort_order'       => 'ASC',
							'show_option_none' => ' ',
							'class'            => $option['class'],
							'echo'             => false,
							'selected'         => absint( self::get_option( $option['id'], $option['default'] ) ),
							'post_status'      => 'publish,private,draft',
							'attach_editlink'  => true
						);

						if ( isset( $option['args'] ) ) {
							$args = wp_parse_args( $option['args'], $args );
						}

						?>
						<tr valign="top" class="single_select_page visa4_select_page">
							<th scope="row" class="titledesc">
								<label><?php echo esc_html( $option['title'] ); ?> <?php echo $tooltip_html; // WPCS: XSS ok. ?></label>
							</th>
							<td class="forminp">
								<?php echo str_replace( ' id=', " data-placeholder='" . esc_attr__( 'Select a page'  ) . "' style='" . $option['css'] . "' class='" . $option['class'] . "' id=", self::visa4_dropdown_pages( $args ) ); // WPCS: XSS ok. ?>
                                <?php echo $description; // WPCS: XSS ok. ?>
                                <div class="visa4_edit_pgae">
							        <a href="#" target="_blank">Edit this page</a>
							    </div>
							</td>
						</tr>
						<?php
						break;

					// Single country selects.
					case 'single_select_country':
						$country_setting = (string) self::get_option( $option['id'], $option['default'] );

						if ( strstr( $country_setting, ':' ) ) {
							$country_setting = explode( ':', $country_setting );
							$country         = current( $country_setting );
							$state           = end( $country_setting );
						} else {
							$country = $country_setting;
							$state   = '*';
						}
						?>
						<tr valign="top">
							<th scope="row" class="titledesc">
								<label for="<?php echo esc_attr( $option['id'] ); ?>"><?php echo esc_html( $option['title'] ); ?> <?php echo $tooltip_html; // WPCS: XSS ok. ?></label>
							</th>
							<td class="forminp"><select name="<?php echo esc_attr( $option['id'] ); ?>" style="<?php echo esc_attr( $option['css'] ); ?>" data-placeholder="<?php esc_attr_e( 'Choose a country&hellip;' ); ?>" aria-label="<?php esc_attr_e( 'Country' ); ?>" class="wc-enhanced-select">
								<?php visa4()->countries->country_dropdown_options( $country, $state ); ?>
							</select> <?php echo $description; // WPCS: XSS ok. ?>
							</td>
						</tr>
						<?php
						break;

					// Country multiselects.
					case 'multi_select_countries':
						$selections = (array) self::get_option( $option['id'], $option['default'] );

						if ( ! empty( $option['options'] ) ) {
							$countries = $option['options'];
						} else {
							$countries = visa4()->countries->countries;
						}

						asort( $countries );
						?>
						<tr valign="top">
							<th scope="row" class="titledesc">
								<label for="<?php echo esc_attr( $option['id'] ); ?>"><?php echo esc_html( $option['title'] ); ?> <?php echo $tooltip_html; // WPCS: XSS ok. ?></label>
							</th>
							<td class="forminp">
								<select multiple="multiple" name="<?php echo esc_attr( $option['id'] ); ?>[]" style="width:350px" data-placeholder="<?php esc_attr_e( 'Choose countries&hellip;' ); ?>" aria-label="<?php esc_attr_e( 'Country' ); ?>" class="wc-enhanced-select">
									<?php
									if ( ! empty( $countries ) ) {
										foreach ( $countries as $key => $val ) {
											echo '<option option="' . esc_attr( $key ) . '"' . visa4_selected( $key, $selections ) . '>' . esc_html( $val ) . '</option>'; // WPCS: XSS ok.
										}
									}
									?>
								</select> <?php echo ( $description ) ? $description : ''; // WPCS: XSS ok. ?> <br /><a class="select_all button" href="#"><?php esc_html_e( 'Select all' ); ?></a> <a class="select_none button" href="#"><?php esc_html_e( 'Select none' ); ?></a>
							</td>
						</tr>
						<?php
						break;

					// Wordpress WYSIWYG
					case 'editor':
					    ?>
						<tr valign="top">
							<th scope="row" class="titledesc">
								<label for="<?php echo esc_attr( $option['id'] ); ?>"><?php echo esc_html( $option['title'] ); ?> <?php echo $tooltip_html; // WPCS: XSS ok. ?></label>
							</th>
							<td class="forminp forminp-<?php echo esc_attr( sanitize_title( $option['type'] ) ); ?>">
								<?php echo $description; // WPCS: XSS ok. ?>
								<?php wp_editor( self::get_option( $option['id'], $option['default'] ), $option['id'], array( 'editor_height' => 500 ) ); ?>
							</td>
						</tr>
						<?php
					    break;

					// Default
					default:
						break;
				}
			}
		}

	/**
    * Custom dropdown of pages
    *
    * @param $args
    * @return mixed
    */
	private static function visa4_dropdown_pages( $args ) {
	   $defaults = array(
            'depth' => 0, 'child_of' => 0,
            'selected' => 0, 'echo' => 1,
            'name' => 'page_id', 'id' => '',
            'class' => '',
            'show_option_none' => '', 'show_option_no_change' => '',
            'option_none_value' => '',
            'value_field' => 'ID',
        );

        $r = wp_parse_args( $args, $defaults );

        $pages = get_pages( $r );
        $output = '';
        // Back-compat with old system where both id and name were based on $name argument
        if ( empty( $r['id'] ) ) {
            $r['id'] = $r['name'];
        }

        if ( ! empty( $pages ) ) {
            $class = '';
            if ( ! empty( $r['class'] ) ) {
                $class = " class='" . esc_attr( $r['class'] ) . "'";
            }

            $output = "<select name='" . esc_attr( $r['name'] ) . "'" . $class . " id='" . esc_attr( $r['id'] ) . "'>\n";
            if ( $r['show_option_no_change'] ) {
                $output .= "\t<option value=\"-1\">" . $r['show_option_no_change'] . "</option>\n";
            }
            if ( $r['show_option_none'] ) {
                $output .= "\t<option value=\"" . esc_attr( $r['option_none_value'] ) . '">' . $r['show_option_none'] . "</option>\n";
            }

            $walker = new Visa4_Page_DropDown();
            $args = array( $pages, $r['depth'], $r );

            $output .= call_user_func_array( array( $walker, 'walk' ), $args );
            $output .= "</select>\n";
        }

        /**
         * Filters the HTML output of a list of pages as a drop down.
         *
         * @since 2.1.0
         * @since 4.4.0 `$r` and `$pages` added as arguments.
         *
         * @param string $output HTML output for drop down list of pages.
         * @param array  $r      The parsed arguments array.
         * @param array  $pages  List of WP_Post objects returned by `get_pages()`
         */
        $html = apply_filters( 'wp_dropdown_pages', $output, $r, $pages );

        if ( $r['echo'] ) {
            echo $html;
        }

        return $html;
	}
	/**
	 * Helper function to get the formatted description and tip HTML for a
	 * given form field. Plugins can call this when implementing their own custom
	 * settings types.
	 *
	 * @param  array $value The form field value array.
	 *
	 * @return array The description and tip as a 2 element array.
	 */
	public static function get_field_description( $value ) {
	    $description  = '';
	    $tooltip_html = '';

	    if ( true === $value['desc_tip'] ) {
	        $tooltip_html = $value['desc'];
	    } elseif ( ! empty( $value['desc_tip'] ) ) {
	        $description  = $value['desc'];
	        $tooltip_html = $value['desc_tip'];
	    } elseif ( ! empty( $value['desc'] ) ) {
	        $description = $value['desc'];
	    }

	    if ( $description && in_array( $value['type'], array( 'textarea', 'radio' ), true ) ) {
	        $description = '<p style="margin-top:0">' . wp_kses_post( $description ) . '</p>';
	    } elseif ( $description && in_array( $value['type'], array( 'checkbox' ), true ) ) {
	        $description = wp_kses_post( $description );
	    } elseif ( $description ) {
	        $description = '<span class="description">' . wp_kses_post( $description ) . '</span>';
	    }

	    if ( $tooltip_html ) {
	        $tooltip_html = '<p class="description">' . $tooltip_html . '</p>';
	    }

	    return array(
	        'description'  => $description,
			'tooltip_html' => $tooltip_html,
		);
	}

	/**
	 * Get WordPress options to update
	 *
	 * Loops though the visa4 options array and preparing each field to save.
	 *
	 * @param array $options Options array to output.
	 * @param array $data    Data to use for saving
	 *
	 * @return bool|array
	 */
	public static function get_options_to_update( $options, $data ) {
		if ( empty( $data ) ) {
			return false;
		}

		// Options to update will be stored here and saved later.
		$update_options   = array();

		// Loop options and get values to save.
		foreach ( $options as $option ) {
			if ( ! isset( $option['id'] ) || ! isset( $option['type'] ) ) {
				continue;
			}

			// Get posted value.
			if ( strstr( $option['id'], '[' ) ) {
				parse_str( $option['id'], $option_name_array );
				$option_name  = current( array_keys( $option_name_array ) );
				$setting_name = key( $option_name_array[ $option_name ] );
				$raw_value    = isset( $data[ $option_name ][ $setting_name ] ) ? wp_unslash( $data[ $option_name ][ $setting_name ] ) : null;
			} else {
				$option_name  = $option['id'];
				$setting_name = '';
				$raw_value    = isset( $data[ $option['id'] ] ) ? wp_unslash( $data[ $option['id'] ] ) : null;
			}

			// Format the value based on option type.
			switch ( $option['type'] ) {
				case 'checkbox':
					$value = '1' === $raw_value || 'yes' === $raw_value ? 'yes' : 'no';
					break;
				case 'textarea':
				case 'editor':
					$value = wp_kses_post( trim( $raw_value ) );
					break;
				case 'multiselect':
				case 'multi_select_countries':
					$value = array_filter( self::clean_var( (array) $raw_value ) );
					break;
				case 'image_width':
					$value = array();
					if ( isset( $raw_value['width'] ) ) {
						$value['width']  = self::clean_var( $raw_value['width'] );
						$value['height'] = self::clean_var( $raw_value['height'] );
						$value['crop']   = isset( $raw_value['crop'] ) ? 1 : 0;
					} else {
						$value['width']  = $option['default']['width'];
						$value['height'] = $option['default']['height'];
						$value['crop']   = $option['default']['crop'];
					}
					break;
				case 'select':
					$allowed_values = empty( $option['options'] ) ? array() : array_map( 'strval', array_keys( $option['options'] ) );
					if ( empty( $option['default'] ) && empty( $allowed_values ) ) {
						$value = null;
						break;
					}
					$default = ( empty( $option['default'] ) ? $allowed_values[0] : $option['default'] );
					$value   = in_array( $raw_value, $allowed_values, true ) ? $raw_value : $default;
					break;
				/*case 'relative_date_selector':
					$value = wc_parse_relative_date_option( $raw_value );
					break;*/
				default:
					$value = self::clean_var( $raw_value );
					break;
			}

			if ( is_null( $value ) ) {
				continue;
			}

			// Check if option is an array and handle that differently to single values.
			if ( $option_name && $setting_name ) {
				if ( ! isset( $update_options[ $option_name ] ) ) {
					$update_options[ $option_name ] = get_option( $option_name, array() );
				}
				if ( ! is_array( $update_options[ $option_name ] ) ) {
					$update_options[ $option_name ] = array();
				}
				$update_options[ $option_name ][ $setting_name ] = $value;
			} else {
				$update_options[ $option_name ] = $value;
			}
		}

		return $update_options;
	}

	/**
     * Clean variables using sanitize_text_field. Arrays are cleaned recursively.
     * Non-scalar values are ignored.
     *
     * @param string|array $var Data to sanitize.
     *
     * @return string|array
     */
    public static function clean_var( $var ) {
        if ( is_array( $var ) ) {
            return array_map( array(__CLASS__, 'clean_var'), $var );
        } else {
            return is_scalar( $var ) ? sanitize_text_field( $var ) : $var;
        }
    }

	/**
	 * Settings page.
	 *
	 * Handles the display of the main visa4 settings page in admin.
	 */
	public static function output() {
	    wp_enqueue_script( 'visa4_settings', Visa4()->plugin_url() . '/assets/js/admin/settings.js', array(), Visa4()->version, true );

	    // Get tabs for the settings page.
		$tabs = self::get_settings_pages();

		include dirname( __FILE__ ) . '/views/html-admin-settings.php';
	}
}