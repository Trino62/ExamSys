<?php
/**
 * Carga la configuración del sitio desde la BD.
 * Incluir con: require_once 'server/site_config_helper.php';
 * Usar con:    $cfg = getSiteConfig($pdo);
 */
function getSiteConfig(PDO $pdo): array {
    $defaults = [
        'titulo'       => 'Evaluación de Conocimientos',
        'descripcion'  => '',
        'badges'       => '',
        'texto_cta'    => '',
        'card1_icono'  => '🎯', 'card1_titulo' => 'Tarjeta 1', 'card1_desc' => '',
        'card2_icono'  => '🏢', 'card2_titulo' => 'Tarjeta 2', 'card2_desc' => '',
        'card3_icono'  => '📋', 'card3_titulo' => 'Tarjeta 3', 'card3_desc' => '',
    ];
    try {
        $rows = $pdo->query("SELECT clave, valor FROM site_config")->fetchAll(PDO::FETCH_KEY_PAIR);
        return array_merge($defaults, $rows);
    } catch (Exception $e) {
        return $defaults;
    }
}
