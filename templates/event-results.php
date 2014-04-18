<?php
/**
 * Event Results
 *
 * @author 		ThemeBoy
 * @package 	SportsPress/Templates
 * @version     0.8
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! isset( $id ) )
	$id = get_the_ID();

$teams = (array)get_post_meta( $id, 'sp_team', false );
$results = array_filter( sp_array_combine( $teams, (array)get_post_meta( $id, 'sp_results', true ) ), 'array_filter' );
$result_labels = sp_get_var_labels( 'sp_result' );

$output = '';

// Initialize and check
$table_rows = '';

$i = 0;

if ( empty( $results ) )
	return false;

foreach( $results as $team_id => $result ):
	if ( sp_array_value( $result, 'outcome', '-1' ) != '-1' ):

		unset( $result['outcome'] );

		$table_rows .= '<tr class="' . ( $i % 2 == 0 ? 'odd' : 'even' ) . '">';

		$table_rows .= '<td class="data-name">' . get_the_title( $team_id ) . '</td>';

		foreach( $result_labels as $key => $label ):
			if ( $key == 'name' )
				continue;
			if ( array_key_exists( $key, $result ) && $result[ $key ] != '' ):
				$value = $result[ $key ];
			else:
				$value = '&mdash;';
			endif;
			$table_rows .= '<td class="data-' . $key . '">' . $value . '</td>';
		endforeach;

		$table_rows .= '</tr>';

		$i++;

	endif;
endforeach;

if ( empty( $table_rows ) ):

	return false;

else:

	$output .= '<h3>' . SP()->text->string('Results', 'event') . '</h3>';

	$output .= '<div class="sp-table-wrapper">' .
		'<table class="sp-event-results sp-data-table sp-responsive-table"><thead>' .
		'<th class="data-name">' . SP()->text->string('Team', 'event') . '</th>';
	foreach( $result_labels as $key => $label ):
		$output .= '<th class="data-' . $key . '">' . $label . '</th>';
	endforeach;
	$output .= '</tr>' . '</thead>' . '<tbody>';
	$output .= $table_rows;
	$output .= '</tbody>' . '</table>' . '</div>';

endif;

echo $output;
