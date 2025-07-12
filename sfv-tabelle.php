<?php

// ToDo: header in dateien setzen, z. b. sowas wie 
// https://github.com/licenses/license-templates/blob/master/templates/agpl3-header.txt

/**
 * Plugin Name
 *
 * @package           SFV-Tabelle
 * @author            Patrik Kittl
 * @copyright         2025 Patrik Kittl
 * @license           GNU AFFERO GENERAL PUBLIC LICENSE
 *
 * @wordpress-plugin
 * Plugin Name:       SFV-Tabelle
 * Plugin URI:        
 * Description:       Extract standings from
 * Version:           0.0.1
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            Patrik Kittl
 * Author URI:        https://github.com/Patrik-SE
 * License:           GNU AFFERO GENERAL PUBLIC LICENSE
 * License URI:       https://github.com/Patrik-SE/sfv-tabelle/blob/main/LICENSE
 * Update URI:        
 * Requires Plugins:
 */

function sfv_tabelle_shortcode($atts, $content){
    // ToDo: Add Custom scaling
    extract(shortcode_atts(array(
        'url' => '',
        'team' => '',
        'rgb_color' =>  ''
    ), $atts));
    wp_register_style('custom_css', plugins_url('custom.css',__FILE__ ));
    wp_enqueue_style('custom_css');
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_HEADER, 0);
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true); 
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

    $htmlContent = curl_exec($curl);
    if(curl_error($curl)) {
        echo "<div> Error: " . curl_error($curl) . "</div>";

    }
    curl_close($curl);
    $rgb_parts = explode(',', $rgb_color);


    $htmlContent = explode('{"bewerbName"', $htmlContent);
    if (count($htmlContent) > 1) {
        $i = 1;
        $eintrag = [];
        while (empty($einträge) && $i < count($htmlContent)) {
            $tabelle_data = explode(',"hintRueckreihungPunktgleichheit"', '{"bewerbName"' . 
                $htmlContent[$i])[0] . "}";
            $tabelle = json_decode($tabelle_data, false);
            $i++;
            if (!empty($tabelle->eintraege)) {
                $eintrag = $tabelle->eintraege;
                ob_start();
                $content .= "<h2>" . $tabelle->bewerbName . "</h2>";
                $content .= "<table style='border-collapse: collapse;' width='100%'>";
                $content .= "<tr>";
                    $content .= "<th style='text-align:end; padding-right: 10px;'>" . "#" . "</th>";
                    $content .= "<th style='text-align:start;'>" . "Mannschaft" . "</th>";
                    $content .= "<th class= 'header' style='padding-left: 10px; padding-right: 10px;'>" . "SP" . "</th>";
                    $content .= "<th class='additional_information' style='text-align:end; padding-right: 10px;'>" . "S" . "</th>";
                    $content .= "<th class='additional_information' style='text-align:end; padding-right: 10px;'>" . "U" . "</th>";
                    $content .= "<th class='additional_information' style='text-align:end; padding-right: 10px;'>" . "N" . "</th>";
                    $content .= "<th class='additional_information' style='text-align:end; padding-right: 10px;'>" . "Tore" . "</th>";
                    $content .= "<th class='header' style='padding-right: 10px;'>" . "+/-" . "</th>";
                    $content .= "<th class='header' style='text-align:end; padding-right: 10px;'>" . "Pkt" . "</th>";
                    $content .= "</tr>";
                foreach ($tabelle->eintraege as $eintrag) {
                    // ToDo: check if $rgb_color is a valid color
                    if ($eintrag->mannschaft == $team && !empty($rgb_color)) {
                        $content .= "<tr style='background-color:rgba(" . $rgb_color . ")'>";
                    } else {
                        $content .= "<tr>";  
                    }
                    $content .= "<td style='text-align:end; padding-right: 10px;'>" . $eintrag->rang . "</td>";
                    $content .= "<td> <a href='" . $eintrag->mannschaftLink . "' target='_blank'>" . $eintrag->mannschaft . "</a></td>";
                    $content .= "<td style='text-align:end; padding-left: 10px; padding-right: 10px;'>" . $eintrag->spiele . "</td>";
                    $content .= "<td class='additional_information' style='text-align:end; padding-right: 10px;'>" . $eintrag->siege . "</td>";
                    $content .= "<td class='additional_information' style='text-align:end; padding-right: 10px;'>" . $eintrag->unentschieden . "</td>";
                    $content .= "<td class='additional_information' style='text-align:end; padding-right: 10px;'>" . $eintrag->niederlagen . "</td>";
                    $content .= "<td class='additional_information' style='text-align:end; padding-right: 10px;'>" . $eintrag->toreErzielt . ":" . $eintrag->toreErhalten . "</td>";
                    $content .= "<td style='text-align:end; padding-right: 10px;'>" . $eintrag->tordifferenz . "</td>";
                    $content .= "<td style='text-align:end; padding-right: 10px;'>" . $eintrag->punkte . "</td>";
                    $content .= "</tr>";
                }
                $content .= "</table>";
                ob_end_clean();
            }
            if (empty($tabelle->eintraege) && $i == count($htmlContent)) {
                $content .= "<div>Keine Einträge gefunden</div>";
            }
        }
    } else {
        $content .= "<div>Keine Einträge gefunden</div>";
    }
    return $content;
}
add_shortcode( 'sfv_tabelle', 'sfv_tabelle_shortcode' );

function sfv_spielplan_shortcode($atts, $content){
    extract(shortcode_atts(array(
        'url' => '',
        'team' => '',
    ), $atts));
    $id = explode('/', $url);
    $id = end($id);
    $id = explode('?', $id)[0];

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_HEADER, 0);
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true); 
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

    $htmlContent = curl_exec($curl);
    if(curl_error($curl)) {
        echo "<div> Error: " . curl_error($curl) . "</div>";

    }
    curl_close($curl);
    //"tournament":false
    
    $htmlContent = explode(',"ergebnisse":[', $htmlContent);
    if (count($htmlContent) > 1) {
        $dateTimeFormat = get_option( 'date_format' ) . ' ' . get_option( 'time_format' );
        // ,"ergebnisse":[ - beginnt die vergangen spiele
        $completed_games = explode(',"spiele":[', $htmlContent[1])[0];
        $completed_games = '{"ergebnisse":[' . $completed_games . "}";
        $completed_games_decoded = json_decode($completed_games, true);
        if (count($completed_games_decoded['ergebnisse'])>0) {
            $content .= "<h2>Ergebnisse</h2>";
            $content .= "<table style='border-collapse: collapse;' width='100%'>";
            foreach($completed_games_decoded['ergebnisse'] as $game) {
                if ($game['heimMannschaft'] == $team || $game['gastMannschaft'] == $team) {
                    $content .= "<tr>";
                    $content .= "<td> <small>" . wp_date( $dateTimeFormat, $game['anstoss']/1000 ) . "</small><br>";
                    $content .= $game['heimMannschaft'] . " : " . $game['gastMannschaft'] . "</td>";
                    $ergebnis = explode('(', $game['ergebnis']);
                    $content .= "<td style='text-align: center; vertical-align: bottom;'><b>" . $ergebnis[0] . "</b></td>";
                    $content .= "<td style='text-align: center; vertical-align: bottom;'><small>(" . $ergebnis[1] . "</small></td><tr>";
                } 
            }
            $content .= "</table>";
        }
        // "spiele":[ - beginnt die kommenden spiele
        $upcoming_games = explode(',"spiele":[', $htmlContent[1])[1];
        $upcoming_games = '{"spiele":[' . explode(',"id":"' . $id . '"', $upcoming_games)[0] . "}";
        $upcoming_games_decoded = json_decode($upcoming_games, true);
        if (count($upcoming_games_decoded['spiele']) > 0) {
            $content .= "<h2>Kommende Spiele</h2>";
            $content .= "<table style='border-collapse: collapse;' width='100%'>";
            foreach ($upcoming_games_decoded['spiele'] as $game) {
                if ($game['heimMannschaft'] == $team || $game['gastMannschaft'] == $team) {
                    $content .= "<tr>";
                    $content .= "<td> <small>" . wp_date( $dateTimeFormat, $game['anstoss']/1000 ) . "</small><br>";
                    $content .= $game['heimMannschaft'] . " : " . $game['gastMannschaft'] . "</td></tr>";
                }
            }
            $content .= "</table>";
        }

        // ,"id":"221323", - beendet den Block und entspricht der ID im Link
        
        
    } else {
        $content .= "<div>Keine Einträge gefunden</div>";
    }
    return $content;
}
add_shortcode( 'sfv_spielplan', 'sfv_spielplan_shortcode' );